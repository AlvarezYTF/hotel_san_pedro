<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of roles and users.
     */
    public function index(): View
    {
        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::whereIn('name', ['Administrador', 'Recepcionista Día', 'Recepcionista Noche'])->get();

        return view('roles.index', compact('users', 'roles'));
    }
}

