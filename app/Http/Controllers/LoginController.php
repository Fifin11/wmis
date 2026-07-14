<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    // 1. Display the Login Blade View
    public function showLogin()
    {
        return view('auth.login');
    }

    // 2. Process the Login Authentication Attempt
    public function login(LoginRequest $request)
    {
        // Credentials have already been validated by LoginRequest
        $credentials = $request->only('email', 'password');

        // Attempt to match credentials and log the user in
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // Protects against session fixation attacks

            $user = Auth::user();

            // Role-Based Redirection Matrix
            switch ($user->role) {
                case 'Admin':
                    return redirect()->intended('/admin/dashboard');
                case 'Driver':
                    return redirect()->intended('/driver/portal');
                case 'Citizen':
                    return redirect()->intended('/citizen/dashboard');
                default:
                    Auth::logout();
                    return redirect()->route('login')->withErrors(['email' => 'Unauthorized role classification.']);
            }
        }

        // Return back with an error message if the credentials fail
        return back()->withErrors([
            'email' => 'The provided credentials do not match our municipal records.',
        ])->onlyInput('email');
    }

    // 3. Process Logout Security Routine
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken(); // Refreshes the CSRF security token
        return redirect()->route('login');
    }
}