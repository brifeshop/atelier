<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // ROLES
        // ============================================
        $roles = [
            'Owner',
            'Manager',
            'Sales',
            'PPIC',
            'Purchasing',
            'Warehouse',
            'Produksi',
            'QC',
            'Finance',
            'Admin',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // ============================================
        // PERMISSIONS
        // ============================================
        $permissions = [
            // Dashboard
            'view executive dashboard',
            'view operations dashboard',
            'view sales dashboard',
            'view planning dashboard',
            'view procurement dashboard',
            'view inventory dashboard',
            'view production dashboard',
            'view quality dashboard',
            'view finance dashboard',

            // Master Data
            'manage customers',
            'manage suppliers',
            'manage employees',
            'manage machines',
            'manage materials',
            'manage products',
            'manage work centers',

            // Sales
            'view sales orders',
            'create sales orders',
            'edit sales orders',
            'delete sales orders',

            // Purchasing
            'view purchase orders',
            'create purchase orders',
            'approve purchase orders',
            'receive goods',

            // Warehouse
            'view inventory',
            'manage inventory',
            'perform stock opname',

            // Production
            'view work orders',
            'create work orders',
            'input production',
            'input waste',
            'input rework',

            // Quality
            'view qc',
            'perform qc',

            // Finance
            'view invoices',
            'create invoices',
            'manage payments',

            // Reports
            'view reports',
            'export reports',

            // Settings
            'manage users',
            'manage roles',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ============================================
        // ASSIGN PERMISSIONS TO ROLES
        // ============================================

        // Owner: semua permission
        Role::findByName('Owner')->syncPermissions(Permission::all());

        // Admin: semua permission
        Role::findByName('Admin')->syncPermissions(Permission::all());

        // Manager: operasional + laporan
        Role::findByName('Manager')->syncPermissions([
            'view operations dashboard',
            'view sales dashboard',
            'view planning dashboard',
            'view procurement dashboard',
            'view inventory dashboard',
            'view production dashboard',
            'view quality dashboard',
            'view sales orders',
            'view purchase orders',
            'view inventory',
            'view work orders',
            'view qc',
            'view reports',
            'export reports',
        ]);

        // Sales
        Role::findByName('Sales')->syncPermissions([
            'view sales dashboard',
            'manage customers',
            'view sales orders',
            'create sales orders',
            'edit sales orders',
            'view reports',
        ]);

        // PPIC
        Role::findByName('PPIC')->syncPermissions([
            'view planning dashboard',
            'view work orders',
            'create work orders',
            'view inventory',
            'view reports',
        ]);

        // Purchasing
        Role::findByName('Purchasing')->syncPermissions([
            'view procurement dashboard',
            'manage suppliers',
            'view purchase orders',
            'create purchase orders',
            'receive goods',
            'view inventory',
            'view reports',
        ]);

        // Warehouse
        Role::findByName('Warehouse')->syncPermissions([
            'view inventory dashboard',
            'view inventory',
            'manage inventory',
            'perform stock opname',
            'receive goods',
            'view reports',
        ]);

        // Produksi
        Role::findByName('Produksi')->syncPermissions([
            'view production dashboard',
            'view work orders',
            'input production',
            'input waste',
            'input rework',
            'view inventory',
        ]);

        // QC
        Role::findByName('QC')->syncPermissions([
            'view quality dashboard',
            'view qc',
            'perform qc',
            'view work orders',
            'view reports',
        ]);

        // Finance
        Role::findByName('Finance')->syncPermissions([
            'view finance dashboard',
            'view invoices',
            'create invoices',
            'manage payments',
            'view reports',
            'export reports',
        ]);

        $this->command->info('✅ Roles & permissions created successfully!');
        $this->command->info('   Roles: ' . Role::count());
        $this->command->info('   Permissions: ' . Permission::count());
    }
}