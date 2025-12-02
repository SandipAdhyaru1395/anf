<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
  public function show()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('auth.register', ['pageConfigs' => $pageConfigs]);
  }

  public function register(Request $request)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255', 'unique:users,name'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
      'password' => ['required', 'string', 'min:6'],
      'terms' => ['accepted'],
    ]);
    
    $user = User::create([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'password' => $validated['password'],
    ]);

    Auth::login($user);
    $request->session()->regenerate();

    Toastr::success('Account created successfully!');
    return redirect()->intended(route('dashboard.read'));
  }
}
