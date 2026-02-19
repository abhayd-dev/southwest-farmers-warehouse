<?php

namespace App\Http\Controllers\Warehouse\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // LOG LOGIN ACTIVITY
            \App\Models\WareActivityLog::create([
                'causer_id'    => Auth::id(),
                'causer_type'  => get_class(Auth::user()),
                'subject_type' => get_class(Auth::user()),
                'subject_id'   => Auth::id(),
                'action'       => 'login',
                'description'  => 'User logged in',
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]);

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials',
        ]);
    }

    public function logout(Request $request)
    {
        // LOG LOGOUT ACTIVITY
        if (Auth::check()) {
            \App\Models\WareActivityLog::create([
                'causer_id'    => Auth::id(),
                'causer_type'  => get_class(Auth::user()),
                'subject_type' => get_class(Auth::user()),
                'subject_id'   => Auth::id(),
                'action'       => 'logout',
                'description'  => 'User logged out',
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
