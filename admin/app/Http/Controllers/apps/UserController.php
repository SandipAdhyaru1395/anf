<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\UserDeletionService;

class UserController extends Controller
{
  protected $userDeletionService;

  public function __construct(UserDeletionService $userDeletionService)
  {
    $this->userDeletionService = $userDeletionService;
  }

  public function index()
  {
    $data['users'] = User::with('role')
      ->select('id', 'name', 'role_id', 'email', 'status', 'image')
      ->where(function ($q) {
        $q->where('role_id', '!=', 1)
          ->orWhereNull('role_id');
      })->orderBy('id', 'desc')->get();

    $data['roles'] = Role::where('id', '!=', 1)->get();

    $all_users_count = User::where(function ($q) {
      $q->where('role_id', '!=', 1)
        ->orWhereNull('role_id');
    })->count();
    $active_users_count = User::where(function ($q) {
      $q->where('role_id', '!=', 1)
        ->orWhereNull('role_id');
    })->where('status', 'active')->count();
    $inactive_users_count = User::where(function ($q) {
      $q->where('role_id', '!=', 1)
        ->orWhereNull('role_id');
    })->where('status', 'inactive')->count();

    $data['all_users_count'] = $all_users_count;
    $data['active_users_count'] = $active_users_count;
    $data['inactive_users_count'] = $inactive_users_count;
    return view('content.user.list', $data);
  }

  public function ajaxUserAll()
  {

    $users = User::with('role')
      ->select('id', 'name', 'role_id', 'email', 'status', 'image')
      ->where(function ($q) {
        $q->where('role_id', '!=', 1)
          ->orWhereNull('role_id');
      })->orderBy('id', 'desc')->get();

    $data = [];
    if ($users) {
      foreach ($users as $key => $val) {
        $data[$key]['id'] = $val->id;
        $data[$key]['full_name'] = $val->name;
        $data[$key]['username'] = $val->name;
        $data[$key]['email'] = $val->email;
        $data[$key]['current_plan'] = '-';
        $data[$key]['billing'] = '-';
        $data[$key]['role'] = $val?->role?->name ?? '-';
        $data[$key]['status'] = $val->status;
        $data[$key]['avatar'] = $val->image;
      }
    }

    return response()->json(['data' => $data]);
  }

  public function ajaxUserListWithRoles()
  {

    $users = User::with('role')
      ->select('id', 'name', 'role_id', 'email', 'status', 'image')
      ->where('role_id', '!=', 1)->orderBy('id', 'desc')->get();

    $data = [];
    if ($users) {
      foreach ($users as $key => $val) {
        $data[$key]['id'] = $val->id;
        $data[$key]['full_name'] = $val->name;
        $data[$key]['username'] = $val->name;
        $data[$key]['email'] = $val->email;
        $data[$key]['current_plan'] = '-';
        $data[$key]['billing'] = '-';
        $data[$key]['role'] = $val->role->name;
        $data[$key]['status'] = $val->status;
        $data[$key]['avatar'] = $val->image;
      }
    }

    return response()->json(['data' => $data]);
  }

  public function update(Request $request)
  {

    $user = User::findOrFail($request->id);

    if ($user->role_id != 1) {

      $user->name = $request->modalEditUserName;
      $user->email = $request->modalEditUserEmail;
      $user->status = $request->modalEditUserStatus;
      $user->phone = $request->modalEditUserPhone;
      $user->role_id = $request->modalEditUserRole;
      $user->save();
    }

    Toastr::success('User updated successfully!');
    return redirect()->back();

  }

  public function updatePassword(Request $request)
  {

    $user = User::findOrFail($request->id);

    if ($user->role_id != 1) {
      $user->password = $request->newPassword;
      $user->save();
    }

    Toastr::success('Password updated successfully!');
    return redirect()->back();
  }

  public function ajaxShow(Request $request)
  {

    $user = User::select('id', 'name', 'email', 'status', 'phone', 'role_id')
      ->where('id', $request->id)->first();

    if ($user->role_id == 1) {
      $user = null;
    }

    return response()->json($user);
  }

  public function changeStatus($id)
  {

    $user = User::findOrFail($id);

    if ($user->role_id != 1) {
      $user->status = ($user->status == 'inactive') ? 'active' : 'inactive';
      $user->save();
    }
    Toastr::success('Status updated successfully!');
    return redirect()->back();
  }

  public function create(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'modalAddUserName' => 'required',
      'modalAddUserEmail' => 'required|email|unique:users,email',
      'modalAddUserPhone' => 'nullable|unique:users,phone|digits:10',
      'modalAddUserStatus' => 'required',
      'newPassword' => 'required',
    ], [
      'modalAddUserName.required' => 'Name is required',
      'modalAddUserEmail.required' => 'Email is required',
      'modalAddUserEmail.email' => 'Must be valid email',
      'modalAddUserEmail.unique' => 'Email already exists',
      'modalAddUserPhone.unique' => 'Number already exists',
      'modalAddUserPhone.digits' => 'Number must be 10 digits',
      'modalAddUserStatus.required' => 'Status is required',
      'newPassword.required' => 'Password is required',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator, 'addModal')->withInput();
    }

    User::create([
      'name' => $request->modalAddUserName,
      'email' => $request->modalAddUserEmail,
      'status' => $request->modalAddUserStatus,
      'phone' => $request->modalAddUserPhone,
      'role_id' => $request->modalAddUserRole,
      'password' => $request->newPassword
    ]);

    Toastr::success('User created successfully!');
    return redirect()->back();
  }

  // public function delete($id)
  // {
  //   $user = User::findOrFail($id);

  //   // Prevent deleting Super Admin
  //   if ($user->role_id == 1) {
  //     Toastr::error('Super Admin cannot be deleted.');
  //     return redirect()->back();
  //   }

  //   // Prevent deleting yourself
  //   if ($user->id == auth()->id()) {
  //     Toastr::error('You cannot delete your own account.');
  //     return redirect()->back();
  //   }

  //   $user->delete();

  //   Toastr::success('User deleted successfully!');
  //   return redirect()->back();
  // }

  public function delete($id)
  {
    $user = User::findOrFail($id);

    try {
      $this->userDeletionService->delete($user);
      Toastr::success('User deleted successfully!');
    } catch (\Exception $e) {
      Toastr::error($e->getMessage());
    }

    return redirect()->back();
  }


  public function viewAccount($id)
  {

    $data['user'] = User::findOrFail($id);
    $data['roles'] = Role::where('id', '!=', 1)->get();

    return view('content.user.account', $data);
  }

  public function viewSecurity($id)
  {
    $data['user'] = User::findOrFail($id);
    $data['roles'] = Role::where('id', '!=', 1)->get();

    return view('content.user.security', $data);
  }


  public function viewNotifications($id)
  {
    $data['user'] = User::findOrFail($id);
    $data['roles'] = Role::where('id', '!=', 1)->get();

    return view('content.user.notifications', $data);
  }

  public function viewConnections($id)
  {
    $data['user'] = User::findOrFail($id);
    $data['roles'] = Role::where('id', '!=', 1)->get();

    return view('content.user.connections', $data);
  }

  public function deleteMultiple(Request $request)
  {
    DB::transaction(function () use ($request) {

      $users = User::whereIn('id', $request->ids)->get();

      foreach ($users as $user) {
        $this->userDeletionService->delete($user);
      }
    });

    return response()->json([
      'success' => true,
      'message' => 'Users deleted successfully.'
    ]);
  }

}
