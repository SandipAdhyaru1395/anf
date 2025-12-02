<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ReportController extends Controller
{
  public function index()
  {
    return view('content.report.index');
  }

  public function salesReport()
  {
    $customers = \App\Models\Customer::where('is_active', 1)
      ->orderBy('company_name')
      ->orderBy('email')
      ->get();
    return view('content.report.sales', ['customers' => $customers]);
  }

  public function salesReportAjax(Request $request)
  {
    // Only get SO (Sales Orders) for sales report, exclude EST and CN
    $query = Order::select([
      'orders.id',
      'orders.order_number',
      'orders.type',
      'orders.order_date',
      'orders.subtotal',
      'orders.vat_amount',
      'orders.total_amount',
      'orders.paid_amount',
      'orders.unpaid_amount',
      'orders.status as order_status',
      'orders.payment_status',
      'customers.email as customer_email',
      'customers.company_name as customer_name'
    ])->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
      ->where('orders.type', 'SO')
      ->orderBy('orders.id', 'desc');

    // Apply filters
    if ($request->has('customer') && !empty($request->customer)) {
      // If customer is numeric, treat it as customer_id, otherwise search by name/email
      if (is_numeric($request->customer)) {
        $query->where('orders.customer_id', '=', $request->customer);
      } else {
        $query->where(function($q) use ($request) {
          $q->where('customers.company_name', 'like', '%' . $request->customer . '%')
            ->orWhere('customers.email', 'like', '%' . $request->customer . '%');
        });
      }
    }

    if ($request->has('payment_status') && !empty($request->payment_status)) {
      $query->where('orders.payment_status', '=', $request->payment_status);
    }

    if ($request->has('start_date') && !empty($request->start_date)) {
      try {
        $startDateStr = trim($request->start_date);
        if (strpos($startDateStr, '/') !== false) {
          $startDate = Carbon::createFromFormat('d/m/Y', $startDateStr)->startOfDay();
        } else {
          $startDate = Carbon::parse($startDateStr)->startOfDay();
        }
        $query->where('orders.order_date', '>=', $startDate);
      } catch (\Exception $e) {
        // Invalid date format, skip filter
      }
    }

    if ($request->has('end_date') && !empty($request->end_date)) {
      try {
        $endDateStr = trim($request->end_date);
        if (strpos($endDateStr, '/') !== false) {
          $endDate = Carbon::createFromFormat('d/m/Y', $endDateStr)->endOfDay();
        } else {
          $endDate = Carbon::parse($endDateStr)->endOfDay();
        }
        $query->where('orders.order_date', '<=', $endDate);
      } catch (\Exception $e) {
        // Invalid date format, skip filter
      }
    }

    // Build filtered query for totals calculation (without individual columns)
    $totalsQuery = Order::leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
      ->where('orders.type', 'SO');
    
    // Apply the same filters as main query
    if ($request->has('customer') && !empty($request->customer)) {
      // If customer is numeric, treat it as customer_id, otherwise search by name/email
      if (is_numeric($request->customer)) {
        $totalsQuery->where('orders.customer_id', '=', $request->customer);
      } else {
        $totalsQuery->where(function($q) use ($request) {
          $q->where('customers.company_name', 'like', '%' . $request->customer . '%')
            ->orWhere('customers.email', 'like', '%' . $request->customer . '%');
        });
      }
    }

    if ($request->has('payment_status') && !empty($request->payment_status)) {
      $totalsQuery->where('orders.payment_status', '=', $request->payment_status);
    }

    if ($request->has('start_date') && !empty($request->start_date)) {
      try {
        $startDateStr = trim($request->start_date);
        if (strpos($startDateStr, '/') !== false) {
          $startDate = Carbon::createFromFormat('d/m/Y', $startDateStr)->startOfDay();
        } else {
          $startDate = Carbon::parse($startDateStr)->startOfDay();
        }
        $totalsQuery->where('orders.order_date', '>=', $startDate);
      } catch (\Exception $e) {
        // Invalid date format, skip filter
      }
    }

    if ($request->has('end_date') && !empty($request->end_date)) {
      try {
        $endDateStr = trim($request->end_date);
        if (strpos($endDateStr, '/') !== false) {
          $endDate = Carbon::createFromFormat('d/m/Y', $endDateStr)->endOfDay();
        } else {
          $endDate = Carbon::parse($endDateStr)->endOfDay();
        }
        $totalsQuery->where('orders.order_date', '<=', $endDate);
      } catch (\Exception $e) {
        // Invalid date format, skip filter
      }
    }
    
    // Apply search filter if exists for totals (same logic as main query)
    if ($request->has('search') && !empty($request->input('search.value'))) {
      $keyword = $request->input('search.value');
      $totalsQuery->where(function($q) use ($keyword) {
        // Search in order number
        $q->where('orders.order_number', 'like', "%{$keyword}%");
        
        // Search in order type + number combination (e.g., SO1, EST12, CN5)
        $orderTypes = ['SO', 'EST', 'CN'];
        foreach ($orderTypes as $type) {
          if (stripos($keyword, $type) === 0) {
            $numberPart = substr($keyword, strlen($type));
            if (!empty($numberPart)) {
              $q->orWhere(function($subQ) use ($type, $numberPart) {
                $subQ->where('orders.type', '=', $type)
                     ->where('orders.order_number', 'like', "%{$numberPart}%");
              });
            } else {
              $q->orWhere('orders.type', '=', $type);
            }
            break;
          }
        }
        
        // Search in customer name
        $q->orWhere('customers.company_name', 'like', "%{$keyword}%");
        // Search in customer email
        $q->orWhere('customers.email', 'like', "%{$keyword}%");
        // Search in order date
        $q->orWhere('orders.order_date', 'like', "%{$keyword}%");
        // Search in subtotal
        $q->orWhere('orders.subtotal', 'like', "%{$keyword}%");
        // Search in VAT amount
        $q->orWhere('orders.vat_amount', 'like', "%{$keyword}%");
        // Search in total amount
        $q->orWhere('orders.total_amount', 'like', "%{$keyword}%");
        // Search in paid amount
        $q->orWhere('orders.paid_amount', 'like', "%{$keyword}%");
        // Search in unpaid amount
        $q->orWhere('orders.unpaid_amount', 'like', "%{$keyword}%");
        // Search in order status
        $q->orWhere('orders.status', 'like', "%{$keyword}%");
        // Search in payment status
        $q->orWhere('orders.payment_status', 'like', "%{$keyword}%");
      });
    }
    
    // Calculate totals from filtered data (only aggregate columns, no GROUP BY needed)
    $totals = $totalsQuery->selectRaw('
        COALESCE(SUM(orders.subtotal), 0) as total_subtotal,
        COALESCE(SUM(orders.vat_amount), 0) as total_vat,
        COALESCE(SUM(orders.total_amount), 0) as total_amount,
        COALESCE(SUM(orders.paid_amount), 0) as total_paid,
        COALESCE(SUM(orders.unpaid_amount), 0) as total_balance
      ')->first();

    $dataTable = DataTables::eloquent($query)
      ->filter(function ($query) use ($request) {
        // Handle global search - search across all relevant columns
        if ($request->has('search') && !empty($request->input('search.value'))) {
          $keyword = $request->input('search.value');
          
          $query->where(function($q) use ($keyword) {
            // Search in order number
            $q->where('orders.order_number', 'like', "%{$keyword}%");
            
            // Search in order type + number combination (e.g., SO1, EST12, CN5)
            $orderTypes = ['SO', 'EST', 'CN'];
            foreach ($orderTypes as $type) {
              if (stripos($keyword, $type) === 0) {
                $numberPart = substr($keyword, strlen($type));
                if (!empty($numberPart)) {
                  $q->orWhere(function($subQ) use ($type, $numberPart) {
                    $subQ->where('orders.type', '=', $type)
                         ->where('orders.order_number', 'like', "%{$numberPart}%");
                  });
                } else {
                  $q->orWhere('orders.type', '=', $type);
                }
                break;
              }
            }
            
            // Search in customer name
            $q->orWhere('customers.company_name', 'like', "%{$keyword}%");
            // Search in customer email
            $q->orWhere('customers.email', 'like', "%{$keyword}%");
            // Search in order date
            $q->orWhere('orders.order_date', 'like', "%{$keyword}%");
            // Search in subtotal
            $q->orWhere('orders.subtotal', 'like', "%{$keyword}%");
            // Search in VAT amount
            $q->orWhere('orders.vat_amount', 'like', "%{$keyword}%");
            // Search in total amount
            $q->orWhere('orders.total_amount', 'like', "%{$keyword}%");
            // Search in paid amount
            $q->orWhere('orders.paid_amount', 'like', "%{$keyword}%");
            // Search in unpaid amount
            $q->orWhere('orders.unpaid_amount', 'like', "%{$keyword}%");
            // Search in order status
            $q->orWhere('orders.status', 'like', "%{$keyword}%");
            // Search in payment status
            $q->orWhere('orders.payment_status', 'like', "%{$keyword}%");
          });
        }
      })
      ->filterColumn('order_date', function ($query, $keyword) {
        $query->where('orders.order_date', 'like', "%{$keyword}%");
      })
      ->filterColumn('order_number', function ($query, $keyword) {
        $query->where('orders.order_number', 'like', "%{$keyword}%");
      })
      ->filterColumn('customer_name', function ($query, $keyword) {
        $query->where(function($q) use ($keyword) {
          $q->where('customers.company_name', 'like', "%{$keyword}%")
            ->orWhere('customers.email', 'like', "%{$keyword}%");
        });
      })
      ->filterColumn('subtotal', function ($query, $keyword) {
        $query->where('orders.subtotal', 'like', "%{$keyword}%");
      })
      ->filterColumn('vat_amount', function ($query, $keyword) {
        $query->where('orders.vat_amount', 'like', "%{$keyword}%");
      })
      ->filterColumn('total_amount', function ($query, $keyword) {
        $query->where('orders.total_amount', 'like', "%{$keyword}%");
      })
      ->filterColumn('paid_amount', function ($query, $keyword) {
        $query->where('orders.paid_amount', 'like', "%{$keyword}%");
      })
      ->filterColumn('unpaid_amount', function ($query, $keyword) {
        $query->where('orders.unpaid_amount', 'like', "%{$keyword}%");
      })
      ->filterColumn('payment_status', function ($query, $keyword) {
        $query->where('orders.payment_status', 'like', "%{$keyword}%");
      })
      ->editColumn('order_date', function ($order) {
        return $order->order_date ? Carbon::parse($order->order_date)->format('d/m/Y H:i') : '';
      })
      ->editColumn('order_number', function ($order) {
        return '#' . ($order->type ?? 'SO') . ($order->order_number ?? '');
      })
      ->editColumn('customer_name', function ($order) {
        $name = $order->customer_name ?? '';
        $email = $order->customer_email ?? '';
        return '<div class="d-flex flex-column">
          ' . ($name ? '<span class="fw-medium">' . htmlspecialchars($name) . '</span>' : '') . '
          ' . ($email ? '<small class="text-muted">' . htmlspecialchars($email) . '</small>' : '') . '
        </div>';
      })
      ->editColumn('subtotal', function ($order) {
        return number_format((float)($order->subtotal ?? 0), 2, '.', '');
      })
      ->editColumn('vat_amount', function ($order) {
        return number_format((float)($order->vat_amount ?? 0), 2, '.', '');
      })
      ->editColumn('total_amount', function ($order) {
        return number_format((float)($order->total_amount ?? 0), 2, '.', '');
      })
      ->editColumn('paid_amount', function ($order) {
        return number_format((float)($order->paid_amount ?? 0), 2, '.', '');
      })
      ->editColumn('unpaid_amount', function ($order) {
        return number_format((float)($order->unpaid_amount ?? 0), 2, '.', '');
      })
      ->editColumn('payment_status', function ($order) {
        $status = $order->payment_status ?? '';
        $statusClass = $status === 'Paid' ? 'bg-success' : ($status === 'Partial' ? 'bg-warning' : 'bg-danger');
        return '<span class="badge px-2 ' . $statusClass . ' text-capitalized">' . htmlspecialchars($status) . '</span>';
      })
      ->rawColumns(['customer_name', 'payment_status']);
    
    // Get the response and add totals
    $response = $dataTable->make(true);
    $responseData = $response->getData(true);
    $responseData['totals'] = [
      'subtotal' => (float)($totals->total_subtotal ?? 0),
      'vat' => (float)($totals->total_vat ?? 0),
      'amount' => (float)($totals->total_amount ?? 0),
      'paid' => (float)($totals->total_paid ?? 0),
      'balance' => (float)($totals->total_balance ?? 0)
    ];
    
    return response()->json($responseData);
  }

  public function dailySalesReport()
  {
    return view('content.report.daily-sales');
  }

  public function dailySalesReportAjax(Request $request)
  {
    $year = $request->input('year', date('Y'));
    $month = $request->input('month', date('m'));
    
    // Get start and end dates for the month
    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
    $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();
    
    // Get SO orders for the month - group by date
    $soOrders = Order::where('type', 'SO')
      ->whereBetween('order_date', [$startDate, $endDate])
      ->get()
      ->groupBy(function($order) {
        return Carbon::parse($order->order_date)->format('Y-m-d');
      })
      ->map(function($orders) {
        return (object)[
          'subtotal' => $orders->sum('subtotal'),
          'shipping' => $orders->sum('delivery_charge'),
          'product_tax' => $orders->sum('vat_amount'),
          'total' => $orders->sum('total_amount'),
        ];
      });
    
    // Get CN orders for the month - group by date
    $cnOrders = Order::where('type', 'CN')
      ->whereBetween('order_date', [$startDate, $endDate])
      ->get()
      ->groupBy(function($order) {
        return Carbon::parse($order->order_date)->format('Y-m-d');
      })
      ->map(function($orders) {
        return (object)[
          'subtotal' => $orders->sum('subtotal'),
          'shipping' => $orders->sum('delivery_charge'),
          'product_tax' => $orders->sum('vat_amount'),
          'total' => $orders->sum('total_amount'),
        ];
      });
    
    // Combine SO and CN data (SO - CN)
    $dailyData = [];
    $allDates = $soOrders->keys()->merge($cnOrders->keys())->unique();
    
    foreach ($allDates as $date) {
      $soData = $soOrders->get($date);
      $cnData = $cnOrders->get($date);
      
      $dailyData[$date] = [
        'subtotal' => (float)($soData->subtotal ?? 0) - (float)($cnData->subtotal ?? 0),
        'shipping' => (float)($soData->shipping ?? 0) - (float)($cnData->shipping ?? 0),
        'product_tax' => (float)($soData->product_tax ?? 0) - (float)($cnData->product_tax ?? 0),
        'total' => (float)($soData->total ?? 0) - (float)($cnData->total ?? 0),
      ];
    }
    
    return response()->json([
      'success' => true,
      'year' => $year,
      'month' => $month,
      'month_name' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
      'daily_data' => $dailyData
    ]);
  }

  public function monthlySalesReport()
  {
    return view('content.report.monthly-sales');
  }

  public function monthlySalesReportAjax(Request $request)
  {
    $year = $request->input('year', date('Y'));
    
    // Get start and end dates for the year
    $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
    $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
    
    // Get SO orders for the year - group by month
    $soOrders = Order::where('type', 'SO')
      ->whereBetween('order_date', [$startDate, $endDate])
      ->get()
      ->groupBy(function($order) {
        return Carbon::parse($order->order_date)->format('Y-m');
      })
      ->map(function($orders) {
        return (object)[
          'subtotal' => $orders->sum('subtotal'),
          'shipping' => $orders->sum('delivery_charge'),
          'product_tax' => $orders->sum('vat_amount'),
          'total' => $orders->sum('total_amount'),
        ];
      });
    
    // Get CN orders for the year - group by month
    $cnOrders = Order::where('type', 'CN')
      ->whereBetween('order_date', [$startDate, $endDate])
      ->get()
      ->groupBy(function($order) {
        return Carbon::parse($order->order_date)->format('Y-m');
      })
      ->map(function($orders) {
        return (object)[
          'subtotal' => $orders->sum('subtotal'),
          'shipping' => $orders->sum('delivery_charge'),
          'product_tax' => $orders->sum('vat_amount'),
          'total' => $orders->sum('total_amount'),
        ];
      });
    
    // Combine SO and CN data (SO - CN) for each month
    $monthlyData = [];
    $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                   'July', 'August', 'September', 'October', 'November', 'December'];
    
    for ($month = 1; $month <= 12; $month++) {
      $monthKey = sprintf('%d-%02d', $year, $month);
      $soData = $soOrders->get($monthKey);
      $cnData = $cnOrders->get($monthKey);
      
      $monthlyData[$monthKey] = [
        'month' => $month,
        'month_name' => $monthNames[$month - 1],
        'subtotal' => (float)($soData->subtotal ?? 0) - (float)($cnData->subtotal ?? 0),
        'shipping' => (float)($soData->shipping ?? 0) - (float)($cnData->shipping ?? 0),
        'product_tax' => (float)($soData->product_tax ?? 0) - (float)($cnData->product_tax ?? 0),
        'total' => (float)($soData->total ?? 0) - (float)($cnData->total ?? 0),
      ];
    }
    
    return response()->json([
      'success' => true,
      'year' => $year,
      'monthly_data' => $monthlyData
    ]);
  }

  public function netVatReport()
  {
    // Get default date range (last 30 days)
    $endDate = Carbon::now();
    $startDate = Carbon::now()->subDays(30);

    return view('content.report.net-vat', [
      'startDate' => $startDate->format('d/m/Y H:i'),
      'endDate' => $endDate->format('d/m/Y H:i')
    ]);
  }

  public function netVatReportAjax(Request $request)
  {
    $startDate = null;
    $endDate = null;

    // Parse start date
    if ($request->has('start_date') && !empty($request->start_date)) {
      try {
        $startDateStr = trim($request->start_date);
        if (strpos($startDateStr, '/') !== false) {
          // Try parsing with time first (d/m/Y H:i)
          if (strpos($startDateStr, ':') !== false) {
            $startDate = Carbon::createFromFormat('d/m/Y H:i', $startDateStr)->startOfDay();
          } else {
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateStr)->startOfDay();
          }
        } else {
          $startDate = Carbon::parse($startDateStr)->startOfDay();
        }
      } catch (\Exception $e) {
        // Invalid date format, use default
      }
    }

    // Parse end date
    if ($request->has('end_date') && !empty($request->end_date)) {
      try {
        $endDateStr = trim($request->end_date);
        if (strpos($endDateStr, '/') !== false) {
          // Try parsing with time first (d/m/Y H:i)
          if (strpos($endDateStr, ':') !== false) {
            $endDate = Carbon::createFromFormat('d/m/Y H:i', $endDateStr)->endOfDay();
          } else {
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateStr)->endOfDay();
          }
        } else {
          $endDate = Carbon::parse($endDateStr)->endOfDay();
        }
      } catch (\Exception $e) {
        // Invalid date format, use default
      }
    }

    // Build query for SO orders
    $soQuery = Order::where('type', 'SO');
    if ($startDate) {
      $soQuery->where('order_date', '>=', $startDate);
    }
    if ($endDate) {
      $soQuery->where('order_date', '<=', $endDate);
    }
    $soVatTotal = $soQuery->sum('vat_amount') ?? 0;

    // Build query for CN orders
    $cnQuery = Order::where('type', 'CN');
    if ($startDate) {
      $cnQuery->where('order_date', '>=', $startDate);
    }
    if ($endDate) {
      $cnQuery->where('order_date', '<=', $endDate);
    }
    $cnVatTotal = $cnQuery->sum('vat_amount') ?? 0;

    // Build query for Purchase VAT
    $purchaseQuery = Purchase::query();
    if ($startDate) {
      $purchaseQuery->where('date', '>=', $startDate);
    }
    if ($endDate) {
      $purchaseQuery->where('date', '<=', $endDate);
    }
    $purchaseVatTotal = $purchaseQuery->sum('vat') ?? 0;

    // Calculate Net VAT To Pay = SO VAT - (CN VAT + Purchase VAT)
    $netVatToPay = $soVatTotal - ($cnVatTotal + $purchaseVatTotal);

    return response()->json([
      'success' => true,
      'soVatTotal' => (float)$soVatTotal,
      'cnVatTotal' => (float)$cnVatTotal,
      'purchaseVatTotal' => (float)$purchaseVatTotal,
      'netVatToPay' => (float)$netVatToPay
    ]);
  }
}

