<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Supplier;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $data['total_suppliers_count'] = Supplier::all()->count();
        $data['active_suppliers_count'] = Supplier::where('is_active', 1)->count();
        $data['inactive_suppliers_count'] = Supplier::where('is_active', 0)->count();
        return view('content.supplier.list', $data);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->whereNull('deleted_at')
            ],
            'phone' => [
                'nullable',
                'string',
                'digits:10',
                Rule::unique('suppliers', 'phone')->whereNull('deleted_at')
            ],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable','in:0,1'],
        ], [
            'company.required' => 'Company is required',
            'full_name.required' => 'Full Name is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'This email already exists',
            'phone.digits' => 'Phone must be exactly 10 digits',
            'phone.unique' => 'This phone already exists',
        ]);

        $validated['is_active'] = (int)($request->get('is_active', 1));

        Supplier::create($validated);

        Toastr::success('Supplier created successfully!');
        return redirect()->route('supplier.list');
    }

    public function edit($id)
    {
        $data['supplier'] = Supplier::findOrFail($id);
        return view('content.supplier.edit', $data);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'exists:suppliers,id'],
            'company' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($request->id)->whereNull('deleted_at')
            ],
            'phone' => [
                'nullable',
                'string',
                'digits:10',
                Rule::unique('suppliers', 'phone')->ignore($request->id)->whereNull('deleted_at')
            ],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable','in:0,1'],
        ], [
            'company.required' => 'Company is required',
            'full_name.required' => 'Full Name is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'This email already exists',
            'phone.digits' => 'Phone must be exactly 10 digits',
            'phone.unique' => 'This phone already exists',
        ]);

        $validated['is_active'] = (int)($request->get('is_active', 1));

        Supplier::find($request->id)->update($validated);

        Toastr::success('Supplier updated successfully!');
        return redirect()->route('supplier.list');
    }

    public function add()
    {
        return view('content.supplier.add');
    }

    public function ajaxList(Request $request)
    {
        $query = Supplier::select([
            'id',
            'company',
            'full_name',
            'vat_number',
            'email',
            'phone',
            'is_active',
        ])->orderBy('id', 'desc');

        return DataTables::eloquent($query)
            ->filterColumn('company', function($query, $keyword) {
                $query->where('suppliers.company', 'like', "%{$keyword}%");
            })
            ->filterColumn('full_name', function($query, $keyword) {
                $query->where('suppliers.full_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('email', function($query, $keyword) {
                $query->where('suppliers.email', 'like', "%{$keyword}%");
            })
            ->filterColumn('phone', function($query, $keyword) {
                $query->where('suppliers.phone', 'like', "%{$keyword}%");
            })
            ->filterColumn('is_active', function($query, $keyword) {
                $isActive = null;
                if (stripos('active', $keyword) !== false) { $isActive = 1; }
                if (stripos('inactive', $keyword) !== false) { $isActive = 0; }
                if ($isActive !== null) {
                    $query->where('suppliers.is_active', $isActive);
                }
            })
            ->orderColumn('company', function ($query, $order) {
                $query->orderBy('suppliers.company', $order);
            })
            ->make(true);
    }

    public function delete($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        Toastr::success('Supplier deleted successfully!');
        return redirect()->back();
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['nullable','string'],
            'id' => ['nullable','integer']
        ]);

        $email = trim((string) $request->email);
        if ($email === '') {
            return response()->json(['valid' => true]);
        }

        $exists = Supplier::where('email', $email)
            ->when(!empty($request->id), function($q) use ($request) { $q->where('id', '!=', $request->id); })
            ->exists();

        return response()->json(['valid' => !$exists]);
    }

    public function checkPhone(Request $request)
    {
        $request->validate([
            'phone' => ['nullable','string'],
            'id' => ['nullable','integer']
        ]);

        $phone = trim((string) $request->phone);
        if ($phone === '') {
            return response()->json(['valid' => true]);
        }

        $exists = Supplier::where('phone', $phone)
            ->when(!empty($request->id), function($q) use ($request) { $q->where('id', '!=', $request->id); })
            ->exists();

        return response()->json(['valid' => !$exists]);
    }
}

