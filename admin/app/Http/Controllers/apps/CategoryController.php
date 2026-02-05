<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\traits\BulkDeletes;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Category;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
  use BulkDeletes;

  protected $model = Category::class;
  

  public function index()
  {
    $data['total_categories_count'] = Category::all()->count();
    $data['active_categories_count'] = Category::where('is_active', 1)->count();
    $data['inactive_categories_count'] = Category::where('is_active', 0)->count();

    return view('content.category.list', $data);
  }

  public function ajaxList(Request $request)
  {
    $query = Category::select([
      'categories.id',                   // needed for checkbox
      'categories.name',
      'categories.is_active',
      'categories.parent_id',
      'parents.name as parent_name'
    ])
      ->leftJoin('categories as parents', 'categories.parent_id', '=', 'parents.id')
      ->with('children');

    return DataTables::eloquent($query)
      ->filter(function ($query) use ($request) {
        $searchValue = $request->get('search')['value'] ?? '';
        if (!empty($searchValue)) {
          $query->where(function ($q) use ($searchValue) {
            $q->where('categories.name', 'like', "%{$searchValue}%")
              ->orWhere('parents.name', 'like', "%{$searchValue}%")
              ->orWhereHas('children', function ($childQuery) use ($searchValue) {
                $childQuery->where('name', 'like', "%{$searchValue}%");
              });
          });
        }
      })
      ->filterColumn('name', function ($query, $keyword) {
        $query->where('categories.name', 'like', "%{$keyword}%");
      })
      ->filterColumn('parent_category', function ($query, $keyword) {
        $query->where('parents.name', 'like', "%{$keyword}%");
      })
      ->filterColumn('child_categories', function ($query, $keyword) {
        $query->whereHas('children', function ($q) use ($keyword) {
          $q->where('name', 'like', "%{$keyword}%");
        });
      })
      ->order(function ($query) use ($request) {
        // Handle backend sorting
        if ($request->has('order')) {
          $columns = [
            0 => 'categories.id',        // control column (ignore)
            1 => 'categories.id',        // checkbox (not sortable)
            2 => 'categories.name',
            3 => 'parents.name',
            4 => 'categories.child_categories',
            5 => 'categories.is_active',
          ];

          $colIndex = $request->order[0]['column'];
          $dir = $request->order[0]['dir'];

          // only sort visible/sortable columns
          if (in_array($colIndex, [2, 3, 4, 5])) {
            $query->orderBy($columns[$colIndex], $dir);
          } else {
            // fallback default: sort by id descending
            $query->orderBy('categories.id', 'desc');
          }
        } else {
          // default latest by id
          $query->orderBy('categories.id', 'desc');
        }
      })
      ->addColumn('parent_category', function ($category) {
        return $category->parent_name ?? '-';
      })
      ->addColumn('child_categories', function ($category) {
        return ($category->children && $category->children->isNotEmpty())
          ? $category->children->implode('name', ', ')
          : '-';
      })
      ->addColumn('image', function () {
        return null;
      })
      ->toJson();
  }




  public function add()
  {

    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();
    return view('content.category.add', $data);
  }
  public function edit($id)
  {

    $data['main_category'] = Category::findOrFail($id);

    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    return view('content.category.edit', $data);
  }

  public function create(Request $request)
  {

    $request->validate([
      'categoryName' => 'required',
      'categoryStatus' => 'required',
    ], [
      'categoryName.required' => 'Name is required',
      'categoryStatus.required' => 'Status is required',
    ]);


    Category::create(
      [
        'name' => $request->categoryName,
        'parent_id' => $request->parentCategory,
        'description' => $request->categoryDescription,
        'is_special' => (isset($request->is_special) && $request->is_special == 'on' && $request->parentCategory == null) ? true : false,
        'sort_order' => $request->sortOrder ?? 1,
        'is_active' => $request->categoryStatus,
      ]
    );


    Toastr::success('Category created successfully!');

    return redirect()->route('category.list');
  }

  public function listAjax()
  {
    $categories = Category::orderBy('id', 'desc')->get();
    $data = [];
    if ($categories) {
      foreach ($categories as $category) {
        $data[] = [
          'id' => $category->id,
          'categories' => $category->name,
          'category_detail' => $category->description,
          'status' => $category->status
        ];
      }
    }
    return response()->json([
      'data' => $data
    ]);
  }

  public function getCategoryAjax(Request $request)
  {
    $category = Category::find($request->id);
    return response()->json($category);
  }

  public function update(Request $request)
  {
    $request->validate([
      'categoryName' => 'required',
      'categoryStatus' => 'required'
    ]);

    $data = [
      'name' => $request->categoryName,
      'parent_id' => $request->parentCategory,
      'description' => $request->categoryDescription,
      'is_special' => (isset($request->is_special) && $request->is_special == 'on' && $request->parentCategory == null) ? true : false,
      'is_active' => $request->categoryStatus
    ];

    if (isset($request->sortOrder) && $request->sortOrder > 0) {
      $data['sort_order'] = $request->sortOrder;
    }

    Category::find($request->id)->update($data);

    Toastr::success('Category updated successfully!');

    return redirect()->route('category.list');
  }

  public function delete($id)
  {
    $category = Category::findOrFail($id);
    $category->delete();
    Toastr::success('Category deleted successfully!');
    return redirect()->back();
  }

}
