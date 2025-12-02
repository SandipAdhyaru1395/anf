<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Role;
use Brian2694\Toastr\Facades\Toastr;

class RoleController extends Controller
{
  public function index()
  {
    $data['roles'] = Role::where('id','!=',1)->get();

    return view('content.role.list', $data);
  }

  public function store(Request $request)
  {
    $role = new Role;
    $role->name = $request->modalRoleName;
    $role->save();

    $slugs = $request->except(['_token', 'modalRoleName']);

    if ($slugs) {

      foreach ($slugs as $key => $slug) {
        
        foreach ($slug as $permission => $value) {
          
          Permission::create([
            'role_id' => $role->id,
            'slug' => $key,
            'action' => $permission,
            'route' => $key.'.'.$permission
          ]);
        }
      }
    }

    Toastr::success('Role created successfully!');
    return redirect()->back();
  }

  public function show(Request $request)
  {

    $role = Role::with('permissions')->find($request->id);
    $data['id'] = $role->id;
    $data['role_name'] = $role->name;
    $data['menus'] = [];
    
    if ($role->permissions) {
      foreach ($role->permissions as $permission) {
        $data['menus'][$permission->slug][$permission->action] = true;
      }
    }

    return response()->json($data);
  }

  public function update(Request $request)
  {
    
    if($request->role_id == 1) {
      Toastr::error('Permission denied!');
      return redirect()->back();
    }

    $role = Role::find($request->role_id);
    $role->name = $request->modalRoleName;
    $role->save();

    Permission::where('role_id', $role->id)->delete();

    $slugs = $request->except(['_token', 'modalRoleName', 'role_id']);
   
    if ($slugs) {
      foreach ($slugs as $key => $slug) {
        foreach ($slug as $permission => $value) {
         
          Permission::create([
            'role_id' => $role->id,
            'slug' => $key,
            'action' => $permission,
            'route' => $key.'.'.$permission
          ]);
        }
      }
    }

    Toastr::success('Role updated successfully!');
    return redirect()->back();
  }
}