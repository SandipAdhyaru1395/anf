<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
  public function show()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('auth.login', ['pageConfigs' => $pageConfigs]);
  }

  // public function login(Request $request)
  // {
  //   $validated = $request->validate([
  //     'email' => ['required', 'string'],
  //     'password' => ['required', 'string'],
  //   ]);

  //   $login = $validated['email'];
  //   $password = $validated['password'];
  //   $remember = (bool) $request->boolean('remember');

  //   // Try email first, then username (name)
  //   if (
  //     Auth::attempt(['email' => $login, 'password' => $password], $remember) 
  //   ) {

  //     if(Auth::user()->status == 'inactive') {
  //       Auth::logout();
  //       $request->session()->invalidate();
  //       $request->session()->regenerateToken();
  //       Toastr::error('Your account is not active');
  //       return redirect()->route('login');
  //     }

  //     $request->session()->regenerate();
  //     return redirect()->intended(route('dashboard.read'));
  //   }

  //   return back()
  //     ->withErrors(['email' => 'Invalid credentials'])
  //     ->withInput($request->only('email'));
  // }


public function login(Request $request)
{
    $validated = $request->validate([
        'email' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $login = $validated['email'];
    $password = $validated['password'];
    $remember = (bool) $request->boolean('remember');

    // Get user including soft deleted
    $user = User::withTrashed()
        ->where('email', $login)
        ->first();

    // 1️⃣ First check credentials
    if (!$user || !Hash::check($password, $user->password)) {
        return back()
            ->withErrors(['email' => 'Invalid credentials'])
            ->withInput($request->only('email'));
    }

    // 2️⃣ Then check if deleted
    if ($user->trashed()) {
        return back()
            ->withErrors(['email' => 'Your account has been deleted. Please contact support.'])
            ->withInput($request->only('email'));
    }

    // 3️⃣ Then check active/inactive
    if ($user->status === 'inactive') {
        return back()
            ->withErrors(['email' => 'Your account is not active.'])
            ->withInput($request->only('email'));
    }

    // 4️⃣ Finally login
    Auth::login($user, $remember);
    $request->session()->regenerate();

    return redirect()->intended(route('dashboard.read'));
}



  public function logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
  }
}
