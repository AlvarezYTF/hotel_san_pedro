@extends('layouts.app')

@section('title', 'Matriz de Permisos')
@section('header', 'Configuración de Seguridad')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 shadow-sm">
        <div class="flex items-center space-x-4 mb-6">
            <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Matriz de Permisos</h1>
                <p class="text-sm text-gray-500">Asigna permisos específicos a cada rol del sistema</p>
            </div>
        </div>

        <form action="{{ route('admin.security.permissions.update') }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permiso</th>
                            @foreach($roles as $role)
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $role->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($permissions as $category => $categoryPermissions)
                            <tr class="bg-gray-50/50">
                                <td colspan="{{ $roles->count() + 1 }}" class="px-6 py-2 text-xs font-bold text-gray-400 uppercase tracking-widest">
                                    Módulo: {{ $category }}
                                </td>
                            </tr>
                            @foreach($categoryPermissions as $permission)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ str_replace('_', ' ', $permission->name) }}
                                    </td>
                                    @foreach($roles as $role)
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($role->name === 'Administrador')
                                                <i class="fas fa-check-circle text-emerald-500" title="Acceso total heredado"></i>
                                                <input type="hidden" name="permissions[{{ $role->id }}][{{ $permission->name }}]" value="1">
                                            @else
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" 
                                                           name="permissions[{{ $role->id }}][{{ $permission->name }}]" 
                                                           value="1"
                                                           {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                </label>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-xl font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios de Matriz
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

