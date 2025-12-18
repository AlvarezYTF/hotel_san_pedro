@extends('layouts.app')

@section('title', 'Gestión de Roles')
@section('header', 'Gestión de Roles y Usuarios')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Navigation Tabs -->
    <div class="flex items-center space-x-4 border-b border-gray-200">
        <a href="{{ route('roles.index') }}" class="px-4 py-2 border-b-2 border-indigo-500 text-indigo-600 font-semibold text-sm">
            Usuarios y Roles
        </a>
        <a href="{{ route('admin.security.permissions') }}" class="px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-sm transition-all">
            Matriz de Permisos
        </a>
        <a href="{{ route('admin.security.audit') }}" class="px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-sm transition-all">
            Registro de Auditoría
        </a>
    </div>

    <!-- Header Info -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-indigo-50 text-indigo-600">
                    <i class="fas fa-user-shield text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Configuración de Accesos</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Gestione usuarios, roles y seguridad avanzada del hotel</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol Actual</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Restricciones</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">ID: #{{ $user->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $roleName = $user->getRoleNames()->first() ?? 'Sin Rol';
                                $roleClass = match($roleName) {
                                    'Administrador' => 'bg-purple-100 text-purple-700',
                                    'Recepcionista Día' => 'bg-amber-100 text-amber-700',
                                    'Recepcionista Noche' => 'bg-indigo-100 text-indigo-700',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleClass }}">
                                {{ $roleName }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                @if($user->allowed_ip)
                                    <span class="text-[10px] text-gray-500 flex items-center">
                                        <i class="fas fa-network-wired mr-1"></i> IP: {{ $user->allowed_ip }}
                                    </span>
                                @endif
                                @if($user->working_hours)
                                    <span class="text-[10px] text-gray-500 flex items-center">
                                        <i class="fas fa-clock mr-1"></i> {{ $user->working_hours['start'] }} - {{ $user->working_hours['end'] }}
                                    </span>
                                @endif
                                @if(!$user->allowed_ip && !$user->working_hours)
                                    <span class="text-[10px] text-gray-400 italic">Sin restricciones</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            @if($user->id !== auth()->id())
                                <div class="inline-flex items-center space-x-3">
                                    <form action="{{ route('usuarios.rol.update', $user) }}" method="POST" class="inline-flex items-center space-x-2">
                                        @csrf
                                        <select name="role" class="block w-40 pl-3 pr-10 py-1.5 text-xs border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-lg">
                                            <option value="">Nuevo rol...</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors" title="Guardar Rol">
                                            <i class="fas fa-save text-xs"></i>
                                        </button>
                                    </form>

                                    @if(!$user->hasRole('Administrador'))
                                        <form action="{{ route('admin.security.impersonate.start', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors" title="Ver como este usuario">
                                                <i class="fas fa-user-secret text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Sesión actual</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

