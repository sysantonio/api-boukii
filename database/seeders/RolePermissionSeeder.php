<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear roles
        $roles = ['superadmin', 'admin', 'monitor', 'client'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Crear permisos para cada tabla
        $tablas = ['clients', 'bookings', 'monitors', 'courses', 'degrees', 'evaluations', 'schools', 'stations', 'services', 'tasks', 'seasons', 'vouchers'];
        foreach ($tablas as $tabla) {
            $permisos = ["view $tabla", "create $tabla", "update $tabla", "delete $tabla"];
            foreach ($permisos as $permiso) {
                Permission::firstOrCreate(['name' => $permiso]);
            }
        }

        // Asignar todos los permisos al rol superadmin
        $superAdmin = Role::where('name', 'superadmin')->first();
        if ($superAdmin && Permission::count() > 0) {
            $superAdmin->givePermissionTo(Permission::all());
        }
    }
}
