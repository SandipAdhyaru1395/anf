<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\traits\BulkDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Address;
use App\Helpers\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
  use BulkDeletes;

  protected $model = Customer::class;
  
  public function index()
  {
    $sales_persons = Helpers::getSalesPersons();
    $customer_groups = Helpers::getCustomerGroups();

    return view('content.customer.list', compact('sales_persons','customer_groups'));
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

    return [
      'customer' => $customer,
      'ordersCount' => (int) ($orderAgg->orders_count ?? 0),
      'totalSpent' => (float) ($orderAgg->total_spent ?? 0),
      'currencySymbol' => $currencySymbol,
      'sales_persons' => $sales_persons
    ];
  }
  public function overview($id = null)
  {
    $data = $this->init($id);
    $data['customer_groups'] = Helpers::getCustomerGroups();

    return view('content.customer.overview', $data);
  }

  //   public function ajaxList(Request $request)
// {
//     $columns = [
//         2 => 'customers.email',
//         3 => 'customers.phone',
//         4 => 'customers.credit_balance',
//         5 => 'orders_count',
//         6 => 'total_spent',
//     ];

  //     $query = Customer::query()
//         ->leftJoin('orders', 'orders.customer_id', '=', 'customers.id')
//         ->groupBy(
//             'customers.id',
//             'customers.email',
//             'customers.phone',
//             'customers.credit_balance'
//         )
//         ->selectRaw('
//             customers.id,
//             customers.email,
//             customers.phone,
//             customers.credit_balance,
//             COUNT(orders.id) as orders_count,
//             COALESCE(SUM(orders.total_amount), 0) as total_spent
//         ');

  //     // Apply sorting
//     if ($request->has('order')) {
//         $orderColumnIndex = $request->order[0]['column'];
//         $orderDirection = $request->order[0]['dir'];

  //         if (isset($columns[$orderColumnIndex])) {
//             $query->orderBy($columns[$orderColumnIndex], $orderDirection);
//         }
//     } else {
//         $query->orderBy('customers.id', 'desc');
//     }

  //     $customerStats = $query->get();

  //     $data = $customerStats->map(function ($row) {
//         return [
//             'id' => (int) $row->id,
//             'customer' => $row->name ?? '',
//             'email' => $row->email ?? '',
//             'phone' => $row->phone ?? '',
//             'credit_balance' => number_format((float) $row->credit_balance, 2, '.', ''),
//             'order' => (int) $row->orders_count,
//             'total_spent' => number_format((float) $row->total_spent, 2, '.', ''),
//         ];
//     });

  //     return response()->json(['data' => $data]);
// }


  public function ajaxList(Request $request)
  {
    // Column mapping for manual sorting
    $columns = [
      2 => 'customers.email',
      3 => 'customers.phone',
      4 => 'customers.credit_balance',
      5 => 'order_stats.orders_count',
      6 => 'order_stats.total_spent',
    ];

    // Subquery for order stats
    $orderStats = DB::table('orders')
      ->selectRaw('
            customer_id,
            COUNT(id) as orders_count,
            COALESCE(SUM(total_amount),0) as total_spent
        ')
      ->groupBy('customer_id');

    // Main query
    $query = Customer::query()
      ->leftJoinSub($orderStats, 'order_stats', function ($join) {
        $join->on('customers.id', '=', 'order_stats.customer_id');
      })
      ->whereNull('customers.deleted_at') // remove if not using soft deletes
      ->select([
        'customers.id',
        'customers.email as customer',
        'customers.phone',
        'customers.credit_balance',
        DB::raw('COALESCE(order_stats.orders_count,0) as orders_count'),
        DB::raw('COALESCE(order_stats.total_spent,0) as total_spent'),
      ]);

    /*
    |--------------------------------------------------------------------------
    | Manual Sorting
    |--------------------------------------------------------------------------
    */
    if ($request->has('order')) {

      $orderColumnIndex = $request->order[0]['column'];
      $orderDirection = $request->order[0]['dir'];

      if (isset($columns[$orderColumnIndex])) {
        $query->orderBy($columns[$orderColumnIndex], $orderDirection);
      }

    } else {
      // Default sort: customer DESC
      $query->orderBy('customers.email', 'desc');
    }

    return DataTables::of($query)

      /*
      |--------------------------------------------------------------------------
      | Fix Global Search (important)
      |--------------------------------------------------------------------------
      */
      ->filter(function ($query) use ($request) {

        if ($search = $request->input('search.value')) {

          $query->where(function ($q) use ($search) {

            $q->where('customers.email', 'like', "%{$search}%")
              ->orWhere('customers.phone', 'like', "%{$search}%")
              ->orWhere('customers.credit_balance', 'like', "%{$search}%")
              ->orWhere('order_stats.orders_count', 'like', "%{$search}%")
              ->orWhere('order_stats.total_spent', 'like', "%{$search}%");
          });
        }
      })

      /*
      |--------------------------------------------------------------------------
      | Format Columns
      |--------------------------------------------------------------------------
      */
      ->editColumn('credit_balance', function ($row) {
        return number_format((float) $row->credit_balance, 2, '.', '');
      })

      ->editColumn('total_spent', function ($row) {
        return number_format((float) $row->total_spent, 2, '.', '');
      })

      ->addColumn('actions', function ($row) {
        return '<button class="btn btn-sm btn-primary">Edit</button>';
      })

      ->rawColumns(['actions'])

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
      return [
        'id' => $order->id,
        'order' => $order->order_number ?? $order->id,
        'order_number' => $order->order_number ?? '',
        'date' => optional($order->created_at)->toISOString(),
        'payment_status' => $order->payment_status ?? 'Due',
        'order_status' => $order->status ?? 'Completed',
        'spent' => number_format((float) ($order->total_amount ?? 0), 2),
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

    $validator = Validator::make($request->all(), [
      'companyName' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
      'mobile' => ['required', 'string', 'digits:10', 'unique:customers,phone'],
      'password' => ['required', 'string', 'min:6'],
      'status' => ['required'],
      'addressLine1' => ['required', 'string', 'max:255'],
      'city' => ['required', 'string', 'max:255'],
      'zip_code' => ['required', 'string', 'max:255'],
    ], [
      'companyName.required' => 'Please enter company name',
      'email.required' => 'Please enter email',
      'email.unique' => 'Email already exists',
      'password.required' => 'Please enter password',
      'password.min' => 'Password must be more than 6 characters',
      'mobile.required' => 'Please enter mobile number',
      'mobile.digits' => 'Mobile number must be 10 digits',
      'mobile.unique' => 'Mobile number already exists',
      'addressLine1.required' => 'Please enter address line 1',
      'city.required' => 'Please enter city',
      'zip_code.required' => 'Please enter postcode'
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'add')->withInput();
    }

    try {
      DB::beginTransaction();

      Customer::create([
        'company_name' => $request->companyName,
        'email' => $request->email,
        'phone' => $request->mobile,
        'password' => $request->password, // ✅ hash password
        'approved_at' => now(),
        'approved_by' => auth()->id(),
        'is_active' => $request->status === 'active' ? 1 : 0,
        'company_address_line1' => $request->addressLine1,
        'company_address_line2' => $request->addressLine2,
        'company_city' => $request->city,
        'company_country' => $request->country,
        'company_zip_code' => $request->zip_code,
        'rep_id' => $request->rep_id ?? null,
        'customer_group_id' => $request->customer_group_id ?? null
      ]);

      DB::commit();

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

    $validator = Validator::make($request->all(), [
      'companyName' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email,' . $request->id],
      'mobile' => ['required', 'string', 'digits:10', 'unique:customers,phone,' . $request->id],
      'password' => ['nullable', 'string', 'min:6'],
      'status' => ['required'],
      'addressLine1' => ['required', 'string', 'max:255'],
      'city' => ['required', 'string', 'max:255'],
      'zip_code' => ['required', 'string', 'max:255'],
    ], [
      'companyName.required' => 'Please enter company name',
      'email.required' => 'Please enter email',
      'email.unique' => 'Email already exists',
      'password.min' => 'Password must be more than 6 characters',
      'mobile.required' => 'Please enter mobile number',
      'mobile.digits' => 'Mobile number must be 10 digits',
      'mobile.unique' => 'Mobile number already exists',
      'companyName.required' => 'Please enter company name',
      'addressLine1.required' => 'Please enter address line 1',
      'city.required' => 'Please enter city',
      'zip_code.required' => 'Please enter postcode'
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
      'rep_id' => $request->rep_id ?? null,
      'customer_group_id' => $request->customer_group_id ?? null
    ];

    if ($request->password) {
      $data['password'] = $request->password;
    }

    $customer->update($data);

    Toastr::success('Customer updated successfully!');
    return redirect()->back();
  }
}
