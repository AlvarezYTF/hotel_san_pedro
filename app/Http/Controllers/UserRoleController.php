<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    /**
     * Update the specified user's role.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // 1. Validar que el usuario no se esté cambiando el rol a sí mismo
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        // 2. Validar que el rol exista y sea de los permitidos
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $roleName = $request->input('role');
        $allowedRoles = ['Administrador', 'Recepcionista Día', 'Recepcionista Noche'];

        if (!in_array($roleName, $allowedRoles)) {
            return back()->with('error', 'El rol seleccionado no es válido.');
        }

        // 3. Sincronizar el rol (Spatie Laravel Permission)
        // syncRoles reemplaza todos los roles actuales por el nuevo
        $user->syncRoles([$roleName]);

        return back()->with('success', "Rol de {$user->name} actualizado a {$roleName} correctamente.");
    }
}

