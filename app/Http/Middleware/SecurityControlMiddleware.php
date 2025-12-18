<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SecurityControlMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Allow bypassing security controls if being impersonated by an Admin
        if (session()->has('impersonated_by')) {
            return $next($request);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Skip checks for Administrators
        if ($user->hasRole('Administrador')) {
            return $next($request);
        }

        // 2. IP Restriction
        if ($user->allowed_ip && $request->ip() !== $user->allowed_ip) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Acceso denegado: IP no autorizada.');
        }

        // 3. Schedule Restriction
        if ($user->working_hours) {
            $now = now();
            $hours = $user->working_hours; // {"start": "08:00", "end": "18:00"}
            
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $hours['start']);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $hours['end']);

            if (!$now->between($startTime, $endTime)) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Acceso denegado: Fuera de horario laboral.');
            }
        }

        return $next($request);
    }
}
