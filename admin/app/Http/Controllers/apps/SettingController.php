<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerGroupRequest;
use App\Http\Requests\PriceListRequest;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\DeliveryMethod;
use App\Models\Permission;
use App\Models\PriceList;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\Role;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use App\Models\VatMethod;
use App\Models\Unit;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
  public function viewGeneralSettings()
  {
    $setting = Setting::all()->pluck('value', 'key');
    $currencies = Currency::orderBy('currency_code')->get(['id', 'currency_code', 'currency_name', 'symbol']);
    // Preselect default currency when only currency_symbol exists (e.g. after migration)
    if (empty($setting['default_currency_id']) && !empty($setting['currency_symbol'])) {
      $match = Currency::where('symbol', $setting['currency_symbol'])->first();
      if ($match) {
        $setting->put('default_currency_id', (string) $match->id);
      }
    }
    return view('content.settings.general', compact('setting', 'currencies'));
  }

  public function viewDeliveryMethod()
  {
    // $delivery_methods = DeliveryMethod::all();
    return view('content.settings.delivery_method');
  }

  public function viewVatMethod()
  {
    return view('content.settings.vat_method');
  }

  public function viewUnit()
  {
    return view('content.settings.unit');
  }

  public function viewCustomerGroup()
  {
    return view('content.settings.customer_group');
  }

  public function viewPriceList()
  {
    return view('content.settings.price_list');
  }
  public function deliveryMethodListAjax()
  {
    $methods = DeliveryMethod::orderBy('id', 'desc')->get(['id', 'name', 'time', 'rate', 'status']);

    $data = [];
    foreach ($methods as $key => $method) {
      $data[$key]['id'] = $method->id;
      $data[$key]['name'] = $method->name;
      $data[$key]['time'] = $method->time;
      $data[$key]['rate'] = $method->rate;
      $data[$key]['status'] = $method->status;
    }

    return response()->json(['data' => $data]);
  }

  public function checkGroupName(Request $request)
  {
    $name = $request->query('name');
    $id = $request->query('id'); // optional for edit

    $existsQuery = CustomerGroup::where('name', $name);

    // If editing, exclude current record
    if ($id) {
      $existsQuery->whereNot('id', $id);
    }

    $exists = $existsQuery->exists();

    return response()->json([
      'valid' => !$exists // true if not exists, false if already exists
    ]);
  }

  public function checkPriceListName(Request $request)
  {
    $name = $request->query('name');
    $id = $request->query('id'); // optional for edit

    $existsQuery = PriceList::where('name', $name);

    // If editing, exclude current record
    if ($id) {
      $existsQuery->whereNot('id', $id);
    }

    $exists = $existsQuery->exists();

    return response()->json([
      'valid' => !$exists // true if not exists, false if already exists
    ]);
  }
  /**
   * Build path rows for column-per-level UI: one row per branch (root → leaf).
   * Returns pathRows (each has 'path' => [categories], 'brands' => collection) and maxDepth.
   */
  private function buildCategoryPathRows()
  {
    $withChildren = function ($q) use (&$withChildren) {
      $q->where('is_active', 1)
        ->orderBy('is_special', 'desc')
        ->orderBy('sort_order', 'asc')
        ->with([
          'children' => $withChildren,
          'brands' => fn ($q) => $q->where('is_active', 1),
        ]);
    };

    $roots = Category::with([
      'children' => $withChildren,
      'brands' => fn ($q) => $q->where('is_active', 1),
    ])
      ->whereNull('parent_id')
      ->where('is_active', 1)
      ->orderBy('is_special', 'desc')
      ->orderBy('sort_order', 'asc')
      ->get();

    $pathRows = $this->collectPathsToLeaves($roots, []);
    $rawMax = $pathRows->isEmpty() ? 0 : $pathRows->max(fn ($row) => count($row['path']));
    $maxDepth = (int) max(2, $rawMax); // at least 2 columns: Category | Sub Category / Brands

    // Group by root (path[0]) so parent appears once with rowspan
    $groupedByRoot = $pathRows->groupBy(fn ($row) => $row['path'][0]->id)->values()->map(function ($paths) {
      return [
        'root' => $paths->first()['path'][0],
        'paths' => $paths->values(),
      ];
    });

    return [
      'groupedByRoot' => $groupedByRoot,
      'maxDepth' => $maxDepth,
    ];
  }

  /**
   * Collect all paths from root to leaf (category with no children). Each path = ['path' => [cat1, ...], 'brands' => collection].
   */
  private function collectPathsToLeaves($categories, $prefix)
  {
    $rows = collect();
    foreach ($categories as $cat) {
      $path = array_merge($prefix, [$cat]);
      if (!$cat->children || $cat->children->isEmpty()) {
        $rows->push([
          'path' => $path,
          'brands' => $cat->brands ?? collect(),
        ]);
      } else {
        $rows = $rows->merge(
          $this->collectPathsToLeaves($cat->children->values(), $path)
        );
      }
    }
    return $rows;
  }

  /**
   * @deprecated Use buildCategoryPathRows() for column-per-level UI.
   */
  private function buildCategoryTreeRows()
  {
    $withChildren = function ($q) use (&$withChildren) {
      $q->where('is_active', 1)
        ->orderBy('is_special', 'desc')
        ->orderBy('sort_order', 'asc')
        ->with([
          'children' => $withChildren,
          'brands' => fn ($q) => $q->where('is_active', 1),
        ]);
    };

    $roots = Category::with([
      'children' => $withChildren,
      'brands' => fn ($q) => $q->where('is_active', 1),
    ])
      ->whereNull('parent_id')
      ->where('is_active', 1)
      ->orderBy('is_special', 'desc')
      ->orderBy('sort_order', 'asc')
      ->get();

    return $this->flattenCategoryRows($roots, 0);
  }

  private function flattenCategoryRows($categories, $depth)
  {
    $rows = [];
    foreach ($categories as $cat) {
      $rows[] = [
        'category' => $cat,
        'depth' => $depth,
        'children' => $cat->children ?? collect(),
        'brands' => $cat->brands ?? collect(),
      ];
      if ($cat->children && $cat->children->isNotEmpty()) {
        $rows = array_merge(
          $rows,
          $this->flattenCategoryRows($cat->children->values(), $depth + 1)
        );
      }
    }
    return $rows;
  }

  public function customerGroupAdd()
  {
    $data = $this->buildCategoryPathRows();

    return view('content.settings.customer_group_add', [
      'groupedByRoot' => $data['groupedByRoot'],
      'maxDepth' => $data['maxDepth'],
    ]);
  }

  public function priceListAdd()
  {
    return view('content.settings.price_list_add');
  }

  public function priceListStore(PriceListRequest $request)
  {

    PriceList::create([
      'name' => $request->name,
      'conversion_rate' => $request->conversion_rate,
      'price_list_type' => $request->price_list_type,
    ]);

    return redirect()
      ->route('settings.priceList')
      ->with('success', 'Price List created successfully.');
  }

  public function customerGroupStore(CustomerGroupRequest $request)
  {
    // Validate the request
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'restrict_categories' => 'required|boolean',
      // categories optional depending on restrict flag
      'categories' => 'nullable|array',
      'categories.*' => 'integer|exists:categories,id',

      // brands optional
      'brands' => 'nullable|array',
      'brands.*' => 'integer|exists:brands,id',
    ]);


    DB::transaction(function () use ($validated) {

      $customerGroup = CustomerGroup::create([
        'name' => $validated['name'],
        'restrict_categories' => $validated['restrict_categories'],
      ]);

      if ($validated['restrict_categories']) {

        // Sync categories (child categories)
        if (!empty($validated['categories'])) {
          $customerGroup->categories()->sync($validated['categories']);
        }

        // Sync brands (if selected where no child exists)
        if (!empty($validated['brands'])) {
          $customerGroup->brands()->sync($validated['brands']);
        }
      }
    });

    Toastr::success('Customer Group created successfully');
    return redirect()->route('settings.customerGroup');
  }


  public function customerGroupListAjax()
  {

    $customerGroups = CustomerGroup::withCount('customers')
      // ->orderBy('id', 'desc')
      ->get(['id', 'name', 'restrict_categories']);

    $data = [];
    foreach ($customerGroups as $key => $customerGroup) {
      $data[$key]['id'] = $customerGroup->id;
      $data[$key]['name'] = $customerGroup->name;
      $data[$key]['restrict_categories'] = $customerGroup->restrict_categories;
      $data[$key]['customers_count'] = $customerGroup->customers_count; // New field
    }

    return response()->json(['data' => $data]);
  }

  public function priceListAjax()
  {

    $priceLists = PriceList::withCount('customers')
      ->get(['id', 'name', 'price_list_type']);

    $data = [];
    foreach ($priceLists as $key => $priceList) {
      $data[$key]['id'] = $priceList->id;
      $data[$key]['name'] = $priceList->name;
      $data[$key]['price_list_type'] = $priceList->price_list_type == 1 ? 'Wholesale and Retail Prices' : 'Wholesale Prices';
      $data[$key]['customers_count'] = $priceList->customers_count;
    }

    return response()->json(['data' => $data]);
  }

  public function customerGroupEdit($id)
  {
    $customerGroup = CustomerGroup::with('categories', 'brands')->findOrFail($id);

    $data = $this->buildCategoryPathRows();

    return view('content.settings.customer_group_edit', [
      'customerGroup' => $customerGroup,
      'groupedByRoot' => $data['groupedByRoot'],
      'maxDepth' => $data['maxDepth'],
    ]);
  }


  public function priceListEdit($id)
  {
    $priceList = PriceList::findOrFail($id);
    return view('content.settings.price_list_edit', [
      'priceList' => $priceList,
    ]);
  }
  //   public function customerGroupUpdate(CustomerGroupRequest $request)
// {
//     // Find the Customer Group
//     $customerGroup = CustomerGroup::findOrFail($request->id);

  //     // Manual validation
//     $validator = Validator::make($request->all(), [
//         'name' => 'required|string|max:255',
//         'restrict_categories' => 'required|boolean',
//         'categories' => 'required_if:restrict_categories,1|array',
//         'categories.*' => 'integer|exists:categories,id',
//     ]);

  //     // If validation fails, show only the first error in Toastr
//     if ($validator->fails()) {
//         $firstError = $validator->errors()->first();
//         Toastr::error($firstError, 'Validation Error');
//         return redirect()->back()->withInput();
//     }

  //     $validated = $validator->validated();

  //     // Update the Customer Group
//     $customerGroup->update([
//         'name' => $validated['name'],
//         'restrict_categories' => $validated['restrict_categories'],
//     ]);

  //     // Sync categories if restrict_categories is Yes (1)
//     if ($validated['restrict_categories']) {
//         $categoryIds = $validated['categories'];
//         $customerGroup->categories()->sync($categoryIds);
//     } else {
//         // Remove all categories if restrictions disabled
//         $customerGroup->categories()->sync([]);
//     }

  //     Toastr::success('Customer Group updated successfully');
//     return redirect()->route('settings.customerGroup');
// }

  public function customerGroupUpdate(CustomerGroupRequest $request)
  {
    $customerGroup = CustomerGroup::findOrFail($request->id);

    $validated = $request->validated();

    $customerGroup->update([
      'name' => $validated['name'],
      'restrict_categories' => $validated['restrict_categories'],
    ]);

    // Clear old relations
    $customerGroup->categories()->detach();
    $customerGroup->brands()->detach();

    if ($validated['restrict_categories'] == 1) {

      $categoryIds = $request->input('categories', []);
      $brandIds = $request->input('brands', []);

      $customerGroup->categories()->sync($categoryIds);
      $customerGroup->brands()->sync($brandIds);
    }

    Toastr::success('Customer Group updated successfully');

    return redirect()->route('settings.customerGroup');
  }

  public function priceListUpdate(PriceListRequest $request)
  {
    $priceList = PriceList::findOrFail($request->id);

    $validated = $request->validated();

    $priceList->update([
      'name' => $validated['name'],
      'conversion_rate' => $validated['conversion_rate'],
      'price_list_type' => $validated['price_list_type'],
    ]);

    Toastr::success('Price list updated successfully');

    return redirect()->route('settings.priceList');
  }

  public function customerGroupDelete($id)
  {
    $group = CustomerGroup::withCount('customers')->find($id);

    if (!$group) {
      return response()->json([
        'status' => false,
        'message' => 'Customer group not found.'
      ], 404);
    }

    if ($group->customers_count > 0) {
      return response()->json([
        'status' => false,
        'message' => 'This group cannot be deleted because customers are assigned to it.'
      ], 400);
    }

    $group->delete();

    return response()->json([
      'status' => true,
      'message' => 'Customer group deleted successfully.'
    ]);
  }

  public function priceListDelete($id)
  {
    $price_list = PriceList::withCount('customers')->find($id);

    if (!$price_list) {
      return response()->json([
        'status' => false,
        'message' => 'Price list not found.'
      ], 404);
    }

    if ($price_list->customers_count > 0) {
      return response()->json([
        'status' => false,
        'message' => 'This price list cannot be deleted because customers are assigned to it.'
      ], 400);
    }

    $price_list->delete();

    return response()->json([
      'status' => true,
      'message' => 'Price list deleted successfully.'
    ]);
  }

  public function vatMethodListAjax()
  {
    $methods = VatMethod::orderBy('id', 'desc')->get(['id', 'name', 'type', 'amount', 'status']);

    $data = [];
    foreach ($methods as $key => $method) {
      $data[$key]['id'] = $method->id;
      $data[$key]['name'] = $method->name;
      $data[$key]['type'] = $method->type;
      $data[$key]['amount'] = $method->amount;
      $data[$key]['status'] = $method->status;
    }

    return response()->json(['data' => $data]);
  }

  public function unitListAjax()
  {
    $units = Unit::orderBy('id', 'desc')->get(['id', 'name', 'status']);

    $data = [];
    foreach ($units as $key => $unit) {
      $data[$key]['id'] = $unit->id;
      $data[$key]['name'] = $unit->name;
      $data[$key]['status'] = $unit->status;
    }

    return response()->json(['data' => $data]);
  }

  public function deliveryMethodShow(Request $request)
  {
    $method = DeliveryMethod::select('id', 'name', 'time', 'rate', 'status', 'sort_order')
      ->where('id', $request->id)
      ->first();

    return response()->json($method);
  }

  public function vatMethodShow(Request $request)
  {
    $method = VatMethod::select('id', 'name', 'type', 'amount', 'status')
      ->where('id', $request->id)
      ->first();

    return response()->json($method);
  }

  public function unitShow(Request $request)
  {
    $unit = Unit::select('id', 'name', 'status')
      ->where('id', $request->id)
      ->first();

    return response()->json($unit);
  }

  public function viewCurrency()
  {
    return view('content.settings.currency');
  }

  public function currencyListAjax()
  {
    $currencies = Currency::orderBy('id', 'desc')->get(['id', 'currency_code', 'currency_name', 'symbol', 'exchange_rate']);

    $data = [];
    foreach ($currencies as $key => $currency) {
      $data[$key]['id'] = $currency->id;
      $data[$key]['currency_code'] = $currency->currency_code;
      $data[$key]['currency_name'] = $currency->currency_name;
      $data[$key]['symbol'] = $currency->symbol;
      $data[$key]['exchange_rate'] = $currency->exchange_rate;
    }

    return response()->json(['data' => $data]);
  }

  public function currencyShow(Request $request)
  {
    $currency = Currency::select('id', 'currency_code', 'currency_name', 'symbol', 'exchange_rate')
      ->where('id', $request->id)
      ->first();

    return response()->json($currency);
  }

  public function deliveryMethodStore(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'dmName' => 'required|string|max:255',
      'dmTime' => 'required|string|max:255',
      'dmPrice' => 'required|numeric|min:0',
      'dmStatus' => 'required|in:Active,Inactive',
      'dmSortOrder' => 'nullable|integer',
    ], [
      'dmName.required' => 'Delivery Name is required.',
      'dmTime.required' => 'Delivery Time is required.',
      'dmPrice.required' => 'Delivery Rate is required.',
      'dmStatus.required' => 'Status is required.',
      'dmSortOrder.integer' => 'The sort order field must be an integer.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator)->withInput();
    }

    DeliveryMethod::create([
      'name' => $request->dmName,
      'time' => $request->dmTime,
      'rate' => $request->dmPrice,
      'status' => $request->dmStatus,
      'sort_order' => $request->dmSortOrder,
    ]);

    Toastr::success('Delivery method created successfully!');
    return redirect()->back();
  }

  public function vatMethodStore(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'vatName' => 'required|string|max:255',
      'vatType' => 'required|in:Percentage,Fixed',
      'vatAmount' => 'required|numeric|min:0',
      'vatStatus' => 'required|in:Active,Inactive',
    ], [
      'vatName.required' => 'VAT Name is required.',
      'vatType.required' => 'VAT Type is required.',
      'vatAmount.required' => 'VAT Amount is required.',
      'vatStatus.required' => 'VAT Status is required.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'addVatModal')->withInput();
    }

    VatMethod::create([
      'name' => $request->vatName,
      'type' => $request->vatType,
      'amount' => $request->vatAmount,
      'status' => $request->vatStatus,
    ]);

    Toastr::success('VAT method created successfully!');
    return redirect()->back();
  }

  public function unitStore(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'unitName' => 'required|string|max:255|unique:units,name',
      'unitStatus' => 'required|in:Active,Inactive',
    ], [
      'unitName.required' => 'Unit Name is required.',
      'unitName.unique' => 'This unit already exists.',
      'unitStatus.required' => 'Unit Status is required.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'addUnitModal')->withInput();
    }

    Unit::create([
      'name' => $request->unitName,
      'status' => $request->unitStatus,
    ]);

    Toastr::success('Unit created successfully!');
    return redirect()->back();
  }

  public function deliveryMethodUpdate(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|exists:delivery_methods,id',
      'dmName' => 'required|string|max:255',
      'dmTime' => 'required|string|max:255',
      'dmPrice' => 'required|numeric|min:0',
      'dmStatus' => 'required|in:Active,Inactive',
      'dmSortOrder' => 'nullable|integer',
    ], [
      'dmName.required' => 'Delivery Name is required.',
      'dmTime.required' => 'Delivery Time is required.',
      'dmPrice.required' => 'Delivery Rate is required.',
      'dmStatus.required' => 'Status is required.',
      'dmSortOrder.integer' => 'The sort order field must be an integer.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'editModal')->withInput();
    }

    $method = DeliveryMethod::findOrFail($request->id);
    $method->name = $request->dmName;
    $method->time = $request->dmTime;
    $method->rate = $request->dmPrice;
    $method->status = $request->dmStatus;
    $method->sort_order = $request->dmSortOrder;
    $method->save();

    Toastr::success('Delivery method updated successfully!');
    return redirect()->back();
  }

  public function vatMethodUpdate(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|exists:vat_methods,id',
      'vatName' => 'required|string|max:255',
      'vatType' => 'required|in:Percentage,Fixed',
      'vatAmount' => 'required|numeric|min:0',
      'vatStatus' => 'required|in:Active,Inactive',
    ], [
      'vatName.required' => 'VAT Name is required.',
      'vatType.required' => 'VAT Type is required.',
      'vatAmount.required' => 'VAT Amount is required.',
      'vatStatus.required' => 'VAT Status is required.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'editVatModal')->withInput();
    }

    $method = VatMethod::findOrFail($request->id);
    $method->name = $request->vatName;
    $method->type = $request->vatType;
    $method->amount = $request->vatAmount;
    $method->status = $request->vatStatus;
    $method->save();

    Toastr::success('VAT method updated successfully!');
    return redirect()->back();
  }

  public function unitUpdate(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|exists:units,id',
      'unitName' => 'required|string|max:255|unique:units,name,' . $request->id,
      'unitStatus' => 'required|in:Active,Inactive',
    ], [
      'unitName.required' => 'Unit Name is required.',
      'unitName.unique' => 'This unit already exists.',
      'unitStatus.required' => 'Unit Status is required.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'editUnitModal')->withInput();
    }

    $unit = Unit::findOrFail($request->id);
    $unit->name = $request->unitName;
    $unit->status = $request->unitStatus;
    $unit->save();

    Toastr::success('Unit updated successfully!');
    return redirect()->back();
  }

  public function currencyStore(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'currency_code' => 'required|string|max:10|unique:currencies,currency_code',
      'currency_name' => 'required|string|max:255',
      'symbol' => 'required|string|max:20',
      'exchange_rate' => 'required|numeric|min:0',
    ], [
      'currency_code.required' => 'Currency Code is required.',
      'currency_code.unique' => 'This currency code already exists.',
      'currency_name.required' => 'Currency Name is required.',
      'symbol.required' => 'Symbol is required.',
      'exchange_rate.required' => 'Exchange Rate is required.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'addCurrencyModal')->withInput();
    }

    Currency::create([
      'currency_code' => $request->currency_code,
      'currency_name' => $request->currency_name,
      'symbol' => $request->symbol,
      'exchange_rate' => $request->exchange_rate,
    ]);

    Toastr::success('Currency added successfully!');
    return redirect()->back();
  }

  public function currencyUpdate(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'required|exists:currencies,id',
      'currency_code' => 'required|string|max:10|unique:currencies,currency_code,' . $request->id,
      'currency_name' => 'required|string|max:255',
      'symbol' => 'required|string|max:20',
      'exchange_rate' => 'required|numeric|min:0',
    ], [
      'currency_code.required' => 'Currency Code is required.',
      'currency_code.unique' => 'This currency code already exists.',
      'currency_name.required' => 'Currency Name is required.',
      'symbol.required' => 'Symbol is required.',
      'exchange_rate.required' => 'Exchange Rate is required.',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'editCurrencyModal')->withInput();
    }

    $currency = Currency::findOrFail($request->id);
    $currency->currency_code = $request->currency_code;
    $currency->currency_name = $request->currency_name;
    $currency->symbol = $request->symbol;
    $currency->exchange_rate = $request->exchange_rate;
    $currency->save();

    Toastr::success('Currency updated successfully!');
    return redirect()->back();
  }

  public function currencyDelete($id)
  {
    $currency = Currency::find($id);
    if (!$currency) {
      if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => false, 'message' => 'Currency not found.'], 404);
      }
      Toastr::error('Currency not found.');
      return redirect()->back();
    }
    $defaultCurrencyId = Setting::where('key', 'default_currency_id')->value('value');
    if ($defaultCurrencyId !== null && $defaultCurrencyId !== '' && (int) $defaultCurrencyId === (int) $currency->id) {
      if (request()->ajax() || request()->wantsJson()) {
        return response()->json([
          'success' => false,
          'message' => 'Cannot delete the default currency. Change the default currency in General Settings first.'
        ], 422);
      }
      Toastr::error('Cannot delete the default currency. Change the default currency in General Settings first.');
      return redirect()->back();
    }
    $currency->delete();
    if (request()->ajax() || request()->wantsJson()) {
      return response()->json(['success' => true, 'message' => 'Currency deleted successfully!']);
    }
    Toastr::success('Currency deleted successfully!');
    return redirect()->back();
  }

  public function updateGeneralSettings(Request $request)
  {
    $validated = $request->validate([
      'companyTitle' => 'required|string|max:255',
      'companyLogo' => 'nullable|image|mimes:jpg,jpeg,png',
      'companyName' => 'required|string|max:255',
      'companyAddress' => 'nullable|string|max:500',
      'companyEmail' => 'nullable|email|max:255',
      'companyPhone' => 'nullable|string|max:50',
      'sessionTimeout' => 'nullable|integer|min:1',
      'default_currency_id' => 'nullable|exists:currencies,id',
      'accountName' => 'nullable|string|max:255',
      'bank' => 'nullable|string|max:255',
      'sortCode' => 'nullable|string|max:20',
      'accountNo' => 'nullable|string|max:50'
    ]);

    $currencySymbol = '';
    if (!empty($validated['default_currency_id'])) {
      $defaultCurrency = Currency::find($validated['default_currency_id']);
      if ($defaultCurrency) {
        $currencySymbol = $defaultCurrency->symbol;
      }
    }

    $map = [
      'company_title' => $validated['companyTitle'],
      'company_name' => $validated['companyName'],
      'company_address' => $validated['companyAddress'] ?? '',
      'company_email' => $validated['companyEmail'] ?? '',
      'company_phone' => $validated['companyPhone'] ?? '',
      'session_timeout' => $validated['sessionTimeout'] ?? '',
      'default_currency_id' => $validated['default_currency_id'] ?? '',
      'currency_symbol' => $currencySymbol,
      'account_name' => $validated['accountName'] ?? '',
      'bank' => $validated['bank'] ?? '',
      'sort_code' => $validated['sortCode'] ?? '',
      'account_no' => $validated['accountNo'] ?? ''
    ];

    foreach ($map as $key => $value) {
      Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    if ($request->hasFile('companyLogo')) {
      $file = $request->file('companyLogo');
      $image = Setting::where('key', 'company_logo')->first()->value;

      if ($image) {
        Storage::disk('public')->delete($image);
      }

      $path = $file->store('settings', 'public');

      Setting::where('key', 'company_logo')->update(['value' => $path]);
    }

    Toastr::success('General settings updated successfully');
    return redirect()->route('settings.general');
  }

  public function viewBannerSettings()
  {
    $setting = Setting::all()->pluck('value', 'key');
    return view('content.settings.banner', compact('setting'));
  }

  public function updateBannerSettings(Request $request)
  {
    $validated = $request->validate([
      'bannerImage' => 'required|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    if ($request->hasFile('bannerImage')) {
      $file = $request->file('bannerImage');
      $existingBanner = Setting::where('key', 'banner')->first();

      // Delete existing banner if it exists
      if ($existingBanner && $existingBanner->value) {
        Storage::disk('public')->delete($existingBanner->value);
      }

      // Store new banner
      $path = $file->store('settings', 'public');

      // Update or create banner setting
      Setting::updateOrCreate(['key' => 'banner'], ['value' => $path]);
    }

    Toastr::success('Banner updated successfully');
    return redirect()->route('settings.banner');
  }

  public function viewMaintenanceSettings()
  {
    $setting = Setting::all()->pluck('value', 'key');
    return view('content.settings.maintenance', compact('setting'));
  }

  public function updateMaintenanceSettings(Request $request)
  {
    $validated = $request->validate([
      'maintenanceEnabled' => 'nullable|in:on,off',
      'maintenanceSecret' => 'nullable|string|max:64'
    ]);

    $enabled = ($validated['maintenanceEnabled'] ?? 'off') === 'on';
    $secret = $validated['maintenanceSecret'] ?? '';

    // Persist secret and single maintenance flag
    Setting::updateOrCreate(['key' => 'maintenance_secret'], ['value' => $secret]);
    Setting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => $enabled ? '1' : '0']);

    try {
      if ($enabled) {
        // Put application into maintenance mode. If secret provided, set bypass.
        $options = [];
        if (!empty($secret)) {
          $options['--secret'] = $secret;
        }
        Artisan::call('down', $options);
        Toastr::success('Application is now in maintenance mode');
      } else {
        Artisan::call('up');
        Toastr::success('Application is now live');
      }
    } catch (\Throwable $e) {
      Toastr::error('Failed to update maintenance mode: ' . $e->getMessage());
    }

    return redirect()->route('settings.maintenance');
  }

  public function viewThemeSettings()
  {
    $setting = Setting::all()->pluck('value', 'key');
    return view('content.settings.theme', compact('setting'));
  }

  public function updateThemeSettings(Request $request)
  {
    $validated = $request->validate([
      'useDefaultColors' => 'nullable|in:1',
      'primaryBgColor' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
      'primaryFontColor' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
      'secondaryBgColor' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
      'secondaryFontColor' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
      'buttonLoginColor' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
    ]);

    // Store default setting
    $useDefault = ($validated['useDefaultColors'] ?? '0') === '1';
    Setting::updateOrCreate(['key' => 'default_theme'], ['value' => $useDefault ? '1' : '0']);

    $map = [
      'primary_bg_color' => $validated['primaryBgColor'] ?? '',
      'primary_font_color' => $validated['primaryFontColor'] ?? '',
      'secondary_bg_color' => $validated['secondaryBgColor'] ?? '',
      'secondary_font_color' => $validated['secondaryFontColor'] ?? '',
      'theme_button_login' => $validated['buttonLoginColor'] ?? '',
    ];

    foreach ($map as $key => $value) {
      if (!empty($value)) {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
      }
    }

    Toastr::success('Theme settings updated successfully');
    return redirect()->route('settings.theme');
  }

  public function truncateData(Request $request)
  {
    // Optional: add authorization/permission checks if needed
    try {
      DB::statement('SET FOREIGN_KEY_CHECKS=0');
      \App\Models\Category::truncate();
      \App\Models\Brand::truncate();
      \App\Models\BrandCategory::truncate();
      \App\Models\BrandTag::truncate();
      \App\Models\ProductBrand::truncate();
      \App\Models\Product::truncate();
      \App\Models\Order::truncate();
      \App\Models\OrderItem::truncate();
      \App\Models\OrderStatusHistory::truncate();
      \App\Models\WalletTransaction::truncate();
      \App\Models\FavoriteProduct::truncate();
      \App\Models\QuantityAdjustment::truncate();
      \App\Models\QuantityAdjustmentItem::truncate();
      \App\Models\Supplier::truncate();
      \App\Models\WarehousesProduct::truncate();
      \App\Models\Purchase::truncate();
      \App\Models\PurchaseItem::truncate();
      DB::statement('SET FOREIGN_KEY_CHECKS=1');
      Toastr::success('Data truncated successfully');
    } catch (\Throwable $e) {
      try {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
      } catch (\Throwable $e2) {
      }
      Toastr::error('Failed to truncate data: ' . $e->getMessage());
    }

    return redirect()->back();
  }

  public function deliveryMethodDelete($id)
  {
    $method = DeliveryMethod::findOrFail($id);
    $method->delete();
    Toastr::success('Delivery method deleted successfully!');
    return redirect()->back();
  }

  public function vatMethodDelete($id)
  {
    $method = VatMethod::findOrFail($id);
    $method->delete();
    Toastr::success('VAT method deleted successfully!');
    return redirect()->back();
  }

  public function unitDelete($id)
  {
    $unit = Unit::findOrFail($id);
    $unit->delete();
    Toastr::success('Unit deleted successfully!');
    return redirect()->back();
  }
}