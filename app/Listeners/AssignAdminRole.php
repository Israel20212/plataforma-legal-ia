<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignAdminRole
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;
        $adminEmail = env('ADMIN_EMAIL');

        // Asegurar que todos los permisos existan en la base de datos.
        $this->createPermissions();

        if ($adminEmail && $user->email === $adminEmail) {
            // Asignar el rol de Administrador
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $adminRole->givePermissionTo(Permission::all());
            $user->assignRole($adminRole);
        } else {
            // Asignar el rol de Usuario normal
            $userRole = Role::firstOrCreate(['name' => 'user']);
            $permissions = [
                'ver documentos', 'crear documentos', 'editar documentos',
                'eliminar documentos', 'analizar documentos'
            ];
            $userRole->syncPermissions($permissions);
            $user->assignRole($userRole);
        }
    }

    /**
     * Crea todos los permisos necesarios para la aplicación si no existen.
     */
    private function createPermissions(): void
    {
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

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }
    }
}
