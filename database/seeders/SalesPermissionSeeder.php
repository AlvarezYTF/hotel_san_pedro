<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class SalesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener o crear permisos de sales
        $permissions = [
            'view_sales',
            'create_sales',
            'edit_sales',
            'delete_sales',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Obtener el primer usuario y asignarle todos los permisos de sales
        $user = User::first();
        if ($user) {
            $user->givePermissionTo($permissions);
            echo "Permisos de sales asignados al usuario: " . $user->name . "\n";
        }

        // También asignar al rol admin si existe
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
            echo "Permisos de sales asignados al rol admin\n";
        }
    }
}
