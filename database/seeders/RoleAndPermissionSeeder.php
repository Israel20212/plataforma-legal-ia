<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetear roles y permisos en caché
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            'ver documentos',
            'crear documentos',
            'editar documentos',
            'eliminar documentos',
            'analizar documentos',
            'gestionar usuarios',
            'gestionar roles',
            'acceso panel admin'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleUser = Role::create(['name' => 'user']);
        $roleUser->givePermissionTo([
            'ver documentos',
            'crear documentos',
            'editar documentos',
            'eliminar documentos',
            'analizar documentos'
        ]);

        // Asignar rol de administrador al primer usuario (opcional)
        // Descomenta esto si quieres asignar automáticamente el rol de admin al primer usuario
        // $user = User::first();
        // if ($user) {
        //     $user->assignRole('admin');
        // }
    }
}