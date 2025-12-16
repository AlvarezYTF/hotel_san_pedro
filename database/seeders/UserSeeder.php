<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hasUsernameColumn = Schema::hasColumn('users', 'username');

        // Usuario administrador (safe - won't duplicate if exists)
        $adminData = [
            'name' => 'Administrador',
            'password' => Hash::make('Brandon-Administrador-2025#'),
        ];

        if ($hasUsernameColumn) {
            $adminData['username'] = 'admin';
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@moviltech.com'],
            $adminData
        );
        
        if (!$admin->hasRole('Administrador')) {
            $admin->assignRole('Administrador');
        }

        // Usuario vendedor (safe - won't duplicate if exists)
        $sellerData = [
            'name' => 'Vendedor',
            'password' => Hash::make('Vendedor2025#'),
        ];

        if ($hasUsernameColumn) {
            $sellerData['username'] = 'vendedor';
        }

        $seller = User::firstOrCreate(
            ['email' => 'vendedor@moviltech.com'],
            $sellerData
        );
        
        if (!$seller->hasRole('Vendedor')) {
            $seller->assignRole('Vendedor');
        }
    }
}
