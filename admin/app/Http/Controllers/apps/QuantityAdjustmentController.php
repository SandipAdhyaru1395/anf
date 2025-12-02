<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\OrderRef;
use App\Models\QuantityAdjustment;
use App\Models\QuantityAdjustmentItem;
use App\Models\Product;
use App\Services\WarehouseProductSyncService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class QuantityAdjustmentController extends Controller
{
    public function index()
    {
        $data['total_adjustments_count'] = QuantityAdjustment::all()->count();
        $data['today_adjustments_count'] = QuantityAdjustment::whereDate('date', today())->count();
        $data['this_month_adjustments_count'] = QuantityAdjustment::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)->count();

        return view('content.quantity_adjustment.list', $data);
    }

    public function add()
    {
        $data['products'] = Product::where('is_active', 1)->orderBy('name')->get();
        return view('content.quantity_adjustment.add', $data);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'date' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return;
                    }
                    // Try to parse d/m/Y H:i format (e.g., "12/11/2025 12:06")
                    if (strpos($value, '/') !== false) {
                        try {
                            $parsed = Carbon::createFromFormat('d/m/Y H:i', $value);
                            if ($parsed === false) {
                                $fail('Date must be in dd/mm/yyyy hh:mm format');
                            }
                        } catch (\Exception $e) {
                            try {
                                $parsed = Carbon::createFromFormat('d/m/Y', $value);
                                if ($parsed === false) {
                                    $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
                                }
                            } catch (\Exception $e2) {
                                $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
                            }
                        }
                    } else {
                        // Try standard date formats
                        try {
                            $parsed = Carbon::parse($value);
                            if ($parsed === false) {
                                $fail('Date must be a valid date');
                            }
                        } catch (\Exception $e) {
                            $fail('Date must be a valid date');
                        }
                    }
                }
            ],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'note' => ['nullable', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.type' => ['required', 'in:addition,subtraction'],
            'products.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ], [
            'date.required' => 'Date is required',
            'products.required' => 'At least one product is required',
            'products.min' => 'At least one product is required',
            'products.*.product_id.required' => 'Product is required',
            'products.*.product_id.exists' => 'Selected product does not exist',
            'products.*.type.required' => 'Type is required',
            'products.*.type.in' => 'Type must be either addition or subtraction',
            'products.*.quantity.required' => 'Quantity is required',
            'products.*.quantity.numeric' => 'Quantity must be a number',
            'products.*.quantity.min' => 'Quantity must be greater than 0',
            'document.mimes' => 'Document must be a file of type: pdf, doc, docx, jpg, jpeg, png',
            'document.max' => 'Document must not be larger than 10MB',
        ]);

        // Handle document upload
        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('adjustments/documents', 'public');
        }

        // Parse date (support d/m/Y H:i and d/m/Y)
        $date = $validated['date'];
        if (strpos($date, '/') !== false) {
            try {
                $date = Carbon::createFromFormat('d/m/Y H:i', $date);
            } catch (\Exception $e) {
                $date = Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
            }
        } else {
            $date = Carbon::parse($date);
        }

        // Get reference_no from order_ref table (qa column) and increment it
        $orderRef = OrderRef::orderBy('id', 'desc')->first();
        if (!$orderRef) {
            // Create new OrderRef if it doesn't exist
            $orderRef = OrderRef::create(['qa' => 1]);
            $reference_no = 1;
        } else {
            $reference_no = $orderRef->qa ?? 1;
            $orderRef->update([
                'qa' => ($orderRef->qa ?? 0) + 1,
            ]);
        }

        // Normalize products array (in case keys are product IDs)
        $products = array_filter($validated['products'], function($productData) {
            return isset($productData['product_id']);
        });

        // Process everything in transaction for data integrity
        $userId = Auth::id();
        DB::transaction(function () use ($date, $reference_no, $documentPath, $request, $products, $userId) {
            // Create adjustment
            $adjustment = QuantityAdjustment::create([
                'date' => $date,
                'reference_no' => $reference_no ?? null,
                'document' => $documentPath,
                'note' => $request->note ?? null,
                'user_id' => $userId,
            ]);

            $adjustments = [];
            
            // Create adjustment items and collect adjustments
            foreach ($products as $productData) {
                QuantityAdjustmentItem::create([
                    'quantity_adjustment_id' => $adjustment->id,
                    'product_id' => $productData['product_id'],
                    'type' => $productData['type'],
                    'quantity' => $productData['quantity'],
                ]);

                $adjustments[] = [
                    'product_id' => $productData['product_id'],
                    'type' => $productData['type'],
                    'quantity' => $productData['quantity'],
                ];
            }

            // Process all adjustments at once
            WarehouseProductSyncService::processAdjustments($adjustments);
        });

        Toastr::success('Quantity adjustment created successfully!');
        return redirect()->route('quantity_adjustment.list');
    }

    public function edit($id)
    {
        $data['adjustment'] = QuantityAdjustment::with('items.product')->findOrFail($id);
        $data['products'] = Product::where('is_active', 1)->orderBy('name')->get();
        return view('content.quantity_adjustment.edit', $data);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'exists:quantity_adjustments,id'],
            'date' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return;
                    }
                    // Try to parse d/m/Y H:i format (e.g., "12/11/2025 12:06")
                    if (strpos($value, '/') !== false) {
                        try {
                            $parsed = Carbon::createFromFormat('d/m/Y H:i', $value);
                            if ($parsed === false) {
                                $fail('Date must be in dd/mm/yyyy hh:mm format');
                            }
                        } catch (\Exception $e) {
                            try {
                                $parsed = Carbon::createFromFormat('d/m/Y', $value);
                                if ($parsed === false) {
                                    $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
                                }
                            } catch (\Exception $e2) {
                                $fail('Date must be in dd/mm/yyyy hh:mm or dd/mm/yyyy format');
                            }
                        }
                    } else {
                        // Try standard date formats
                        try {
                            $parsed = Carbon::parse($value);
                            if ($parsed === false) {
                                $fail('Date must be a valid date');
                            }
                        } catch (\Exception $e) {
                            $fail('Date must be a valid date');
                        }
                    }
                }
            ],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'note' => ['nullable', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.type' => ['required', 'in:addition,subtraction'],
            'products.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ], [
            'id.required' => 'Adjustment ID is required',
            'id.exists' => 'Adjustment not found',
            'date.required' => 'Date is required',
            'products.required' => 'At least one product is required',
            'products.min' => 'At least one product is required',
            'products.*.product_id.required' => 'Product is required',
            'products.*.product_id.exists' => 'Selected product does not exist',
            'products.*.type.required' => 'Type is required',
            'products.*.type.in' => 'Type must be either addition or subtraction',
            'products.*.quantity.required' => 'Quantity is required',
            'products.*.quantity.numeric' => 'Quantity must be a number',
            'products.*.quantity.min' => 'Quantity must be greater than 0',
            'document.mimes' => 'Document must be a file of type: pdf, doc, docx, jpg, jpeg, png',
            'document.max' => 'Document must not be larger than 10MB',
        ]);

        $adjustment = QuantityAdjustment::with('items')->findOrFail($request->id);

        // Prepare old adjustments for reversal
        $oldAdjustments = $adjustment->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'type' => $item->type,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // Handle document upload
        $documentPath = $adjustment->document;
        if ($request->hasFile('document')) {
            // Delete old document
            if ($documentPath) {
                Storage::disk('public')->delete($documentPath);
            }
            $documentPath = $request->file('document')->store('adjustments/documents', 'public');
        }

        // Parse date (support d/m/Y H:i and d/m/Y)
        $date = $validated['date'];
        if (strpos($date, '/') !== false) {
            try {
                $date = Carbon::createFromFormat('d/m/Y H:i', $date);
            } catch (\Exception $e) {
                $date = Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
            }
        } else {
            $date = Carbon::parse($date);
        }

        // Update adjustment (reference_no is not updated, it remains as originally set from order_ref)
        $adjustment->update([
            'date' => $date,
            'document' => $documentPath,
            'note' => $request->note ?? null,
        ]);

        // Normalize products array (in case keys are product IDs)
        $products = array_filter($validated['products'], function($productData) {
            return isset($productData['product_id']);
        });

        // Process update in transaction
        DB::transaction(function () use ($adjustment, $oldAdjustments, $products) {
            // Revert previous stock changes
            WarehouseProductSyncService::revertAdjustments($oldAdjustments);

            // Delete old items
            $adjustment->items()->delete();

            // Prepare new adjustments
            $newAdjustments = [];
            foreach ($products as $productData) {
                QuantityAdjustmentItem::create([
                    'quantity_adjustment_id' => $adjustment->id,
                    'product_id' => $productData['product_id'],
                    'type' => $productData['type'],
                    'quantity' => $productData['quantity'],
                ]);

                $newAdjustments[] = [
                    'product_id' => $productData['product_id'],
                    'type' => $productData['type'],
                    'quantity' => $productData['quantity'],
                ];
            }

            // Apply new adjustments
            WarehouseProductSyncService::processAdjustments($newAdjustments);
        });

        Toastr::success('Quantity adjustment updated successfully!');
        return redirect()->route('quantity_adjustment.list');
    }

    public function delete($id)
    {
        $adjustment = QuantityAdjustment::with('items')->findOrFail($id);

        // Prepare adjustments for reversal
        $adjustments = $adjustment->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'type' => $item->type,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        DB::transaction(function () use ($adjustments, $adjustment) {
            // Revert stock changes
            WarehouseProductSyncService::revertAdjustments($adjustments);

            // Delete document if exists
            if ($adjustment->document) {
                Storage::disk('public')->delete($adjustment->document);
            }

            $adjustment->delete();
        });

        Toastr::success('Quantity adjustment deleted successfully!');
        return redirect()->back();
    }

    public function ajaxList(Request $request)
    {
        $query = QuantityAdjustment::with('user')
            ->select([
                'id',
                'date',
                'reference_no',
                'note',
                'user_id',
                'created_at',
            ])
            ->orderBy('id', 'desc');

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $searchValue = $request->get('search')['value'] ?? '';
                if (!empty($searchValue)) {
                    $query->where(function ($q) use ($searchValue) {
                        // Remove #QA prefix if present for searching reference_no
                        $refSearchValue = preg_replace('/^#?QA/i', '', $searchValue);
                        // Search in reference_no (with or without prefix)
                        $q->where(function ($refQuery) use ($searchValue, $refSearchValue) {
                            $refQuery->where('reference_no', 'like', "%{$refSearchValue}%")
                                ->orWhereRaw("CONCAT('#QA', reference_no) LIKE ?", ["%{$searchValue}%"]);
                        })
                            // Search in note
                            ->orWhere('note', 'like', "%{$searchValue}%")
                            // Search in date (try to parse date formats)
                            ->orWhere(function ($dateQuery) use ($searchValue) {
                                try {
                                    // Try d/m/Y format first
                                    if (preg_match('/\d{1,2}\/\d{1,2}\/\d{4}/', $searchValue, $matches)) {
                                        $date = Carbon::createFromFormat('d/m/Y', $matches[0]);
                                        $dateQuery->whereDate('date', $date->format('Y-m-d'));
                                    } else {
                                        // Try to parse as date and search
                                        $date = Carbon::parse($searchValue);
                                        $dateQuery->whereDate('date', $date->format('Y-m-d'));
                                    }
                                } catch (\Exception $e) {
                                    // If parsing fails, search in formatted date string
                                    $dateQuery->whereRaw("DATE_FORMAT(date, '%d/%m/%Y %H:%i') LIKE ?", ["%{$searchValue}%"]);
                                }
                            })
                            // Search in user name
                            ->orWhereHas('user', function ($userQuery) use ($searchValue) {
                                $userQuery->where('name', 'like', "%{$searchValue}%");
                            });
                    });
                }
            })
            ->addColumn('reference_no_display', function ($adjustment) {
                return $adjustment->reference_no ? '#QA' . $adjustment->reference_no : 'N/A';
            })
            ->addColumn('user_name', function ($adjustment) {
                return $adjustment->user ? $adjustment->user->name : 'N/A';
            })
            ->addColumn('date_formatted', function ($adjustment) {
                return optional($adjustment->date)->format('d/m/Y H:i');
            })
            ->addColumn('note_display', function ($adjustment) {
                return $adjustment->note ? strip_tags($adjustment->note) : '';
            })
            ->addColumn('actions', function ($adjustment) {
                $editUrl = route('quantity_adjustment.edit', $adjustment->id);
                return '<div class="d-inline-block text-nowrap">' .
                       '<a href="' . $editUrl . '" class="rounded-pill waves-effect btn-icon"><button class="btn btn-text-secondary "><i class="icon-base ti tabler-edit icon-22px"></i></button></a> ' .
                       '<a href="javascript:;" onclick="deleteAdjustment(' . $adjustment->id . ')" class="rounded-pill waves-effect btn-icon"><button class="btn"><i class="icon-base ti tabler-trash icon-22px"></i></button></a>' .
                       '</div>';
            })
            ->filterColumn('reference_no_display', function ($query, $keyword) {
                // Remove #QA prefix if present for searching reference_no
                $refSearchValue = preg_replace('/^#?QA/i', '', $keyword);
                $query->where(function ($q) use ($keyword, $refSearchValue) {
                    $q->where('reference_no', 'like', "%{$refSearchValue}%")
                        ->orWhereRaw("CONCAT('#QA', reference_no) LIKE ?", ["%{$keyword}%"]);
                });
            })
            ->filterColumn('date_formatted', function ($query, $keyword) {
                // Try to parse date in d/m/Y format and search
                try {
                    // Try d/m/Y format first
                    if (preg_match('/\d{1,2}\/\d{1,2}\/\d{4}/', $keyword, $matches)) {
                        $date = Carbon::createFromFormat('d/m/Y', $matches[0]);
                        $query->whereDate('date', $date->format('Y-m-d'));
                    } else {
                        // Try to parse as date and search
                        $date = Carbon::parse($keyword);
                        $query->whereDate('date', $date->format('Y-m-d'));
                    }
                } catch (\Exception $e) {
                    // If parsing fails, search in formatted date string
                    $query->whereRaw("DATE_FORMAT(date, '%d/%m/%Y %H:%i') LIKE ?", ["%{$keyword}%"]);
                }
            })
            ->filterColumn('user_name', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('note_display', function ($query, $keyword) {
                $query->where('note', 'like', "%{$keyword}%");
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function showAjax($id)
    {
        $adjustment = QuantityAdjustment::with(['items.product', 'user'])->findOrFail($id);
        $html = view('_partials._modals.modal-quantity-adjustment-show', compact('adjustment'))->render();
        return response()->json(['html' => $html]);
    }
    public function searchAjax(Request $request)
    {
        $q = trim($request->get('q', ''));
        $limit = (int) $request->get('limit', 10);

        $query = Product::select(['id', 'name', 'sku', 'price', 'image_url'])
            ->where('is_active', 1);

        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->limit($limit)->get();

        return response()->json([
            'results' => $products->map(function($p) {
                return [
                    'id' => $p->id,
                    'text' => $p->name . ' (' . $p->sku . ')',
                    'price' => $p->price,
                    'image_url' => $p->image_url,
                ];
            })
        ]);
    }
    
}
