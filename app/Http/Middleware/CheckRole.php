<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check() || Auth::user()->role !== $role) {
            if (!Auth::check()) {
                return redirect()->route('login');
            }
            
            $user = Auth::user();
            switch ($user->role) {
                case 'Admin':
                    return redirect()->route('admin.dashboard');
                case 'Driver':
                    return redirect()->route('driver.portal');
                case 'Citizen':
                    return redirect()->route('citizen.dashboard');
                default:
                    Auth::logout();
                    return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
