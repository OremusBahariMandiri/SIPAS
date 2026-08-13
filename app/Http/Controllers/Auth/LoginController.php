<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Ganti field login dari 'email' menjadi 'nrk'
     */
    public function username(): string
    {
        return 'nrk';
    }

    /**
     * Validasi field login
     */
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            'nrk'      => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
    }
}