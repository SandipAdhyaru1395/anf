<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\traits\BulkDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Address;
use App\Helpers\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Services\CustomerWelcomeEmailService;
use App\Support\PhoneNormalizer;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
  use BulkDeletes;

  protected $model = Customer::class;
  protected $customerWelcomeEmailService;

  public function __construct(CustomerWelcomeEmailService $customerWelcomeEmailService)
  {
    $this->customerWelcomeEmailService = $customerWelcomeEmailService;
  }
  
  public function index()
  {
    $sales_persons = Helpers::getSalesPersons();
    $customer_groups = Helpers::getCustomerGroups();
    $price_lists = Helpers::getPriceLists();

    return view('content.customer.list', compact('sales_persons','customer_groups','price_lists'));
  }

  private function init($id = null)
  {

    if (!$id) {
      return redirect()->route('customer.list');
    }

    $customer = Customer::query()->findOrFail($id);

    $orderAgg = Order::query()
      ->where('customer_id', $id)
      ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total_amount),0) as total_spent')
      ->first();

    $settings = Helpers::setting();
    $currencySymbol = $settings['currency_symbol'] ?? '$';
    $sales_persons = Helpers::getSalesPersons();
    $customer_groups = Helpers::getCustomerGroups();
    $price_lists = Helpers::getPriceLists();

    // Addresses (branches) for defaults in overview
    $branches = Branch::query()
      ->where('customer_id', $id)
      ->orderByDesc('is_default_delivery')
      ->orderByDesc('is_default_billing')
      ->orderBy('created_at', 'asc')
      ->get();

    return [
      'customer' => $customer,
      'ordersCount' => (int) ($orderAgg->orders_count ?? 0),
      'totalSpent' => (float) ($orderAgg->total_spent ?? 0),
      'currencySymbol' => $currencySymbol,
      'sales_persons' => $sales_persons,
      'customer_groups' => $customer_groups,
      'price_lists' => $price_lists,
      'branches' => $branches
    ];
  }
  public function overview($id = null)
  {
    $data = $this->init($id);

    return view('content.customer.overview', $data);
  }

  public function ajaxList(Request $request)
  {
    // Column mapping for manual sorting (Name, Main Contact, Group, Last Seen, Last Order, Min. Spend, Status)
    $columns = [
      0 => DB::raw('COALESCE(customers.company_name, customers.email)'),
      1 => 'customers.email',
      2 => 'customer_groups.name',
      3 => 'customers.last_login',
      4 => 'order_stats.last_order_at',
      5 => 'customers.is_active',
    ];

    // Subquery for order stats (include last order date)
    $orderStats = DB::table('orders')
      ->selectRaw('
            customer_id,
            COUNT(id) as orders_count,
            COALESCE(SUM(total_amount),0) as total_spent,
            MAX(created_at) as last_order_at
        ')
      ->groupBy('customer_id');

    // Main query with customer_groups for group name
    $query = Customer::query()
      ->leftJoinSub($orderStats, 'order_stats', function ($join) {
        $join->on('customers.id', '=', 'order_stats.customer_id');
      })
      ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
      ->whereNull('customers.deleted_at')
      ->select([
        'customers.id',
        'customers.company_name',
        'customers.email',
        'customers.phone',
        'customers.last_login',
        'customers.is_active',
        'customer_groups.name as group_name',
        DB::raw('COALESCE(order_stats.orders_count,0) as orders_count'),
        DB::raw('COALESCE(order_stats.total_spent,0) as total_spent'),
        'order_stats.last_order_at',
      ]);

    // Status filter (Show: All except closed = active only by default, active, inactive, all)
    $statusFilter = $request->input('status_filter');
    if ($statusFilter === 'active') {
      $query->where('customers.is_active', true);
    } elseif ($statusFilter === 'inactive') {
      $query->where('customers.is_active', false);
    } elseif ($statusFilter === 'except_closed' || $statusFilter === '') {
      $query->where('customers.is_active', true);
    }

    if ($request->has('order') && !empty($request->order[0])) {
      $orderColumnIndex = (int) $request->order[0]['column'];
      $orderDirection = $request->order[0]['dir'];

      if (isset($columns[$orderColumnIndex])) {
        $query->orderBy($columns[$orderColumnIndex], $orderDirection);
      }
    } else {
      $query->orderBy(DB::raw('COALESCE(customers.company_name, customers.email)'), 'asc');
    }

    return DataTables::of($query)

      ->filter(function ($query) use ($request) {
        if ($search = $request->input('search.value')) {
          $query->where(function ($q) use ($search) {
            $q->where('customers.email', 'like', "%{$search}%")
              ->orWhere('customers.company_name', 'like', "%{$search}%")
              ->orWhere('customers.phone', 'like', "%{$search}%")
              ->orWhere('customer_groups.name', 'like', "%{$search}%");
          });
        }
      })

      ->addColumn('name', function ($row) {
        return $row->company_name ?: $row->email;
      })
      ->addColumn('main_contact', function ($row) {
        return $row->email ?: '-';
      })
      ->addColumn('group', function ($row) {
        return $row->group_name ?: '';
      })
      ->addColumn('last_seen', function ($row) {
        if (!$row->last_login) {
          return '';
        }
        $date = $row->last_login instanceof \Carbon\Carbon
          ? $row->last_login
          : Carbon::parse($row->last_login);
        return $date->format('d M Y');
      })
      ->addColumn('last_order', function ($row) {
        return $row->last_order_at ? Carbon::parse($row->last_order_at)->format('d M Y') : '';
      })
      ->addColumn('min_spend', function () {
        return '-';
      })
      ->addColumn('status', function ($row) {
        $active = (bool) $row->is_active;
        $class = $active ? 'badge-status-active' : 'badge-status-inactive';
        $text = $active ? 'Active' : 'Inactive';
        return '<span class="' . $class . '">' . $text . '</span>';
      })
      ->rawColumns(['status'])
      ->make(true);
  }

  public function ordersAjax(Request $request, $id)
  {
    $customer = Customer::query()->findOrFail($id);

    $orders = Order::query()
      ->where('customer_id', $customer->id)
      ->latest('created_at')
      ->get();

    $data = $orders->map(function (Order $order) {
      $orderDateValue = $order->order_date ?: $order->created_at;
      $deliverOnValue = $order->estimated_delivery_date;

      $orderDateIso = null;
      if (!empty($orderDateValue)) {
        $orderDateIso = $orderDateValue instanceof \Carbon\Carbon
          ? $orderDateValue->toISOString()
          : Carbon::parse($orderDateValue)->toISOString();
      }

      $deliverOnIso = null;
      if (!empty($deliverOnValue)) {
        $deliverOnIso = $deliverOnValue instanceof \Carbon\Carbon
          ? $deliverOnValue->toISOString()
          : Carbon::parse($deliverOnValue)->toISOString();
      }

      return [
        'id' => $order->id,
        'order_number' => $order->order_number ?? null,
        'number' => $order->order_number ?? $order->id,
        'order_date' => $orderDateIso,
        'deliver_on' => $deliverOnIso,
        'total' => number_format((float) ($order->total_amount ?? 0), 2),
        'invoice' => '-',
        'status' => $order->status ?? 'New',
      ];
    });

    return response()->json(['data' => $data]);
  }

  public function security($id = null)
  {

    $data = $this->init($id);

    return view('content.customer.security', $data);
  }

  public function branches($id = null)
  {

    $data = $this->init($id);

    // Load addresses for the customer
    $branches = Branch::where('customer_id', $id)
      ->orderByDesc('is_default_delivery')
      ->orderByDesc('is_default_billing')
      ->orderBy('created_at', 'asc')
      ->get();

    $data['branches'] = $branches;

    return view('content.customer.branches', $data);
  }


  public function notifications($id = null)
  {

    $data = $this->init($id);

    return view('content.customer.notifications', $data);
  }

  public function destroy($id)
  {
    $customer = Customer::query()->findOrFail($id);
    $customer->delete();

    return response()->json([
      'success' => true,
      'message' => 'Customer deleted successfully.'
    ]);
  }

  public function updatePassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|integer|exists:customers,id',
      'newPassword' => 'required|string|min:6',
      'confirmPassword' => 'required|string|same:newPassword',
    ], [
      'newPassword.required' => 'Please enter new password',
      'newPassword.min' => 'Password must be more than 6 characters',
      'confirmPassword.required' => 'Please confirm new password',
      'confirmPassword.same' => 'The password and its confirm are not the same',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    $customer = Customer::query()->findOrFail($request->id);
    $customer->password = $request->newPassword;
    $customer->save();

    Toastr::success('Password updated successfully!');
    return redirect()->back();
  }

  public function store(Request $request)
  {
    $request->merge([
      'mobile' => PhoneNormalizer::normalize($request->mobile) ?? '',
    ]);

    $validator = Validator::make($request->all(), [
      'companyName' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
      'mobile' => ['required', 'string', 'min:10', 'max:20', 'regex:/^[a-zA-Z0-9]+$/', Rule::unique('customers', 'phone')],
      'password' => ['required', 'string', 'min:6'],
      'status' => ['required'],
      'addressLine1' => ['required', 'string', 'max:255'],
      'city' => ['required', 'string', 'max:255'],
      'zip_code' => ['required', 'string', 'max:255'],
      'vatNumber' => ['nullable', 'string', 'max:100'],
      'eoriNumber' => ['nullable', 'string', 'max:100'],
      'isPartOfGroup' => ['nullable', Rule::in(['yes', 'no'])],
      'businessType' => ['nullable', Rule::in(['Wholesaler', 'Distributor', 'Retailer', 'Online retailer', 'Vape shop'])],
      'averageMonthlySpendExVat' => ['nullable', 'string', 'max:255'],
      'storesServicedCount' => ['nullable', 'integer', 'min:0'],
      'yourName' => ['nullable', 'string', 'max:255'],
    ], [
      'companyName.required' => 'Please enter company name',
      'email.required' => 'Please enter email',
      'email.unique' => 'Email already exists',
      'password.required' => 'Please enter password',
      'password.min' => 'Password must be more than 6 characters',
      'mobile.required' => 'Please enter mobile number',
      'mobile.min' => 'Mobile must be at least 10 characters.',
      'mobile.max' => 'Mobile must be at most 20 characters.',
      'mobile.regex' => 'Mobile must contain only letters and numbers.',
      'mobile.unique' => 'Mobile number already exists',
      'addressLine1.required' => 'Please enter address line 1',
      'city.required' => 'Please enter city',
      'zip_code.required' => 'Please enter postcode',
      'isPartOfGroup.required' => 'Please select if customer is part of a group',
      'businessType.required' => 'Please select type of business',
      'yourName.required' => 'Please enter contact name',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'add')->withInput();
    }

    try {
      DB::beginTransaction();

      $customer = Customer::create([
        'company_name' => $request->companyName,
        'email' => $request->email,
        'phone' => $request->mobile,
        'password' => $request->password, // ✅ hash password
        'approved_at' => now(),
        'approved_by' => Auth::id(),
        'is_active' => $request->status === 'active' ? 1 : 0,
        'company_address_line1' => $request->addressLine1,
        'company_address_line2' => $request->addressLine2,
        'company_city' => $request->city,
        'company_country' => $request->country,
        'company_zip_code' => $request->zip_code,
        'vat_number' => $request->vatNumber,
        'eori_number' => $request->eoriNumber,
        'is_part_of_group' => $request->isPartOfGroup === 'yes' ? 1 : 0,
        'business_type' => $request->businessType,
        'average_monthly_spend_ex_vat' => $request->averageMonthlySpendExVat,
        'stores_serviced_count' => $request->storesServicedCount,
        'contact_person_name' => $request->yourName,
        'rep_id' => $request->rep_id ?? null,
        'customer_group_id' => $request->customer_group_id ?? null,
        'price_list_id' => $request->price_list_id ?? null
      ]);

      DB::commit();
      $this->customerWelcomeEmailService->send($customer);

      Toastr::success('Customer created successfully!');
    } catch (\Exception $e) {
      Toastr::error('Something went wrong');
      Log::error($e);
      DB::rollBack();
    }

    return redirect()->back();
  }

  public function update(Request $request)
  {
    $request->merge([
      'mobile' => PhoneNormalizer::normalize($request->mobile) ?? '',
    ]);

    $validator = Validator::make($request->all(), [
      'companyName' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email,' . $request->id],
      'mobile' => ['required', 'string', 'min:10', 'max:20', 'regex:/^[a-zA-Z0-9]+$/', Rule::unique('customers', 'phone')->ignore($request->id)],
      'password' => ['nullable', 'string', 'min:6'],
      'status' => ['required'],
      'addressLine1' => ['required', 'string', 'max:255'],
      'city' => ['required', 'string', 'max:255'],
      'zip_code' => ['required', 'string', 'max:255'],
      'vatNumber' => ['nullable', 'string', 'max:100'],
      'eoriNumber' => ['nullable', 'string', 'max:100'],
      'isPartOfGroup' => ['nullable', Rule::in(['yes', 'no'])],
      'businessType' => ['nullable', Rule::in(['Wholesaler', 'Distributor', 'Retailer', 'Online retailer', 'Vape shop'])],
      'averageMonthlySpendExVat' => ['nullable', 'string', 'max:255'],
      'storesServicedCount' => ['nullable', 'integer', 'min:0'],
      'yourName' => ['nullable', 'string', 'max:255'],
    ], [
      'companyName.required' => 'Please enter company name',
      'email.required' => 'Please enter email',
      'email.unique' => 'Email already exists',
      'password.min' => 'Password must be more than 6 characters',
      'mobile.required' => 'Please enter mobile number',
      'mobile.min' => 'Mobile must be at least 10 characters.',
      'mobile.max' => 'Mobile must be at most 20 characters.',
      'mobile.regex' => 'Mobile must contain only letters and numbers.',
      'mobile.unique' => 'Mobile number already exists',
      'addressLine1.required' => 'Please enter address line 1',
      'city.required' => 'Please enter city',
      'zip_code.required' => 'Please enter postcode',
      'isPartOfGroup.required' => 'Please select if customer is part of a group',
      'businessType.required' => 'Please select type of business',
      'yourName.required' => 'Please enter contact name',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'editCustomer')->withInput();
    }

    $customer = Customer::findOrFail($request->id);

    $data = [
      'company_name' => $request->companyName,
      'email' => $request->email,
      'phone' => $request->mobile,
      'is_active' => $request->status === 'active' ? 1 : 0,
      'company_address_line1' => $request->addressLine1,
      'company_address_line2' => $request->addressLine2,
      'company_city' => $request->city,
      'company_country' => $request->country,
      'company_zip_code' => $request->zip_code,
      'vat_number' => $request->vatNumber,
      'eori_number' => $request->eoriNumber,
      'is_part_of_group' => $request->isPartOfGroup === 'yes' ? 1 : 0,
      'business_type' => $request->businessType,
      'average_monthly_spend_ex_vat' => $request->averageMonthlySpendExVat,
      'stores_serviced_count' => $request->storesServicedCount,
      'contact_person_name' => $request->yourName,
      'rep_id' => $request->rep_id ?? null,
      'customer_group_id' => $request->customer_group_id ?? null,
      'price_list_id' => $request->price_list_id ?? null
    ];

    if ($request->password) {
      $data['password'] = $request->password;
    }

    $customer->update($data);

    Toastr::success('Customer updated successfully!');
    return redirect()->back();
  }
}

