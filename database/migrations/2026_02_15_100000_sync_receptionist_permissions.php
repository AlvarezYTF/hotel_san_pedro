<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        // Asegurar que los permisos existan
        foreach (['view_products', 'generate_invoices', 'download_invoices'] as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $newPermissions = ['view_products', 'generate_invoices', 'download_invoices'];

        foreach (['Recepcionista Día', 'Recepcionista Noche'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($newPermissions);
            }
        }
    }

    public function down(): void
    {
        $permissionsToRemove = ['view_products', 'generate_invoices', 'download_invoices'];

        foreach (['Recepcionista Día', 'Recepcionista Noche'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->revokePermissionTo($permissionsToRemove);
            }
        }
    }
};
