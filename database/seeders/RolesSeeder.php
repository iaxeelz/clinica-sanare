<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $roles = [
            'admin' => 'Administrador General',
            'medico' => 'Médico',
            'enfermera' => 'Enfermera',
            'recepcionista' => 'Recepcionista',
            'inventario' => 'Encargado de Inventario'
        ];

        foreach ($roles as $name => $display_name) {
            Role::create(['name' => $name, 'guard_name' => 'web']);
        }

        // Crear permisos básicos
        $permissions = [
            'view_dashboard',
            'manage_users',
            'manage_roles',
            'view_appointments',
            'create_appointments',
            'edit_appointments',
            'delete_appointments',
            'view_patients',
            'create_patients',
            'edit_patients',
            'delete_patients',
            'view_services',
            'create_services',
            'edit_services',
            'delete_services',
            'view_inventory',
            'create_inventory',
            'edit_inventory',
            'delete_inventory',
            'view_reports',
            'view_cash_flow',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Asignar todos los permisos al admin
        $admin = Role::findByName('admin', 'web');
        $admin->givePermissionTo(Permission::all());
    }
}