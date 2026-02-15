<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureReceptionistActiveShift
{
    /**
     * Block receptionist operational modules when there is no active shift.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || $user->hasRole("Administrador")) {
            return $next($request);
        }

        $roleNames = $user->roles()->pluck("name");
        $isReceptionist = $roleNames->contains(function ($name) {
            return str_starts_with((string) $name, "Recepcionista");
        });

        if (!$isReceptionist) {
            return $next($request);
        }

        $hasActiveShift = $user->turnoActivo()->exists();
        if ($hasActiveShift) {
            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();
        $allowedWithoutShift = [
            "dashboard",
            "dashboard.receptionist.day",
            "dashboard.receptionist.night",
            "dashboard.receptionist.day.en",
            "dashboard.receptionist.night.en",
            "shift-handovers.receive",
            "shift-handovers.store-reception",
            "shift.start",
            "logout",
        ];

        if (in_array($routeName, $allowedWithoutShift, true)) {
            return $next($request);
        }

        $isNightReceptionist = $roleNames->contains(function ($name) {
            return stripos((string) $name, "Noche") !== false;
        });

        $targetRoute = $isNightReceptionist
            ? "dashboard.receptionist.night"
            : "dashboard.receptionist.day";

        if (!app("router")->has($targetRoute)) {
            $targetRoute = "dashboard";
        }

        return redirect()
            ->route($targetRoute)
            ->with(
                "error",
                "No hay turnos activos. Debes recibir o iniciar un turno para habilitar los paneles.",
            );
    }
}

