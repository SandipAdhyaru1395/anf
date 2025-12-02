<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;

class LoginController extends Controller
{
  public function show()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('auth.login', ['pageConfigs' => $pageConfigs]);
  }

  public function login(Request $request)
  {
    $validated = $request->validate([
      'email' => ['required', 'string'],
      'password' => ['required', 'string'],
    ]);

    $login = $validated['email'];
    $password = $validated['password'];
    $remember = (bool) $request->boolean('remember');
    
    // Try email first, then username (name)
    if (
      Auth::attempt(['email' => $login, 'password' => $password], $remember) 
    ) {

      if(Auth::user()->status == 'inactive') {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Toastr::error('Your account is not active');
        return redirect()->route('login');
      }

      $request->session()->regenerate();
      return redirect()->intended(route('dashboard.read'));
    }

    return back()
      ->withErrors(['email' => 'Invalid credentials'])
      ->withInput($request->only('email'));
  }

  public function logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
  }
}
