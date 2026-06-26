<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Demo logic as requested: "demo123 (any password works)"
        // But let's stick to standard Auth for security, 
        // the seeder already set the password to demo123.
        
        if (Auth::attempt($credentials, $request->remember)) {
            $user = Auth::user();
            if (!$user->aktif) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Hubungi Owner.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            
            if ($user->role === 'operator') {
                return redirect()->intended('/pos');
            }

            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->save(); // Force session save to release any locks immediately

        return redirect('/login');
    }
}
