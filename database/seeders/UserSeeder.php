<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Owner Atelier', 'email' => 'owner@atelier.id', 'role' => 'Owner'],
            ['name' => 'Manager Atelier', 'email' => 'manager@atelier.id', 'role' => 'Manager'],
            ['name' => 'Sales Atelier', 'email' => 'sales@atelier.id', 'role' => 'Sales'],
            ['name' => 'PPIC Atelier', 'email' => 'ppic@atelier.id', 'role' => 'PPIC'],
            ['name' => 'Purchasing Atelier', 'email' => 'purchasing@atelier.id', 'role' => 'Purchasing'],
            ['name' => 'Warehouse Atelier', 'email' => 'warehouse@atelier.id', 'role' => 'Warehouse'],
            ['name' => 'Produksi Atelier', 'email' => 'produksi@atelier.id', 'role' => 'Produksi'],
            ['name' => 'QC Atelier', 'email' => 'qc@atelier.id', 'role' => 'QC'],
            ['name' => 'Finance Atelier', 'email' => 'finance@atelier.id', 'role' => 'Finance'],
            ['name' => 'Admin Atelier', 'email' => 'admin@atelier.id', 'role' => 'Admin'],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$userData['role']]);
        }

        $this->command->info('✅ Users created successfully!');
        $this->command->info('');
        $this->command->info('Login test accounts (password: password):');
        $this->command->info('  owner@atelier.id       → Owner');
        $this->command->info('  manager@atelier.id     → Manager');
        $this->command->info('  sales@atelier.id       → Sales');
        $this->command->info('  produksi@atelier.id    → Produksi');
        $this->command->info('  admin@atelier.id       → Admin');
    }
}