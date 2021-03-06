<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

  public function showLoginForm()
  {
    return view('auth.login');
  }

  public function login()
  {

    if (!Auth::attempt(request(['email', 'password']))) {
      return redirect('login')->with([
        'email' => 'These credentials does not match our records.'
      ]);
    }

    return redirect('/backstage/concerts');
  }

  public function logout()
  {

    Auth::logout();

    return redirect('/login');
  }
}
