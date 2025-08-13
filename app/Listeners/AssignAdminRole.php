<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AssignAdminRole
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event): void
    {
        Log::info('[AssignAdminRole] Listener ejecutado con lógica de email de admin.');

        /** @var User $user */
        $user = $event->user;
        $adminEmail = env('ADMIN_EMAIL');

        Log::info("[AssignAdminRole] Usuario registrándose: {$user->email}.");
        Log::info("[AssignAdminRole] Email de admin configurado en .env: " . ($adminEmail ?: 'No definido'));

        // Comprobar si el email del admin está definido y si coincide con el del usuario
        if ($adminEmail && $user->email === $adminEmail) {
            Log::info("[AssignAdminRole] Coincidencia de email. Intentando asignar rol 'admin'.");

            $adminRole = Role::where('name', 'admin')->first();

            if ($adminRole) {
                $user->assignRole($adminRole);
                Log::info("[AssignAdminRole] Rol 'admin' asignado exitosamente a {$user->email}.");
            } else {
                Log::info("[AssignAdminRole] ¡ERROR CRÍTICO! Rol 'admin' NO encontrado en la DB. Ejecuta los seeders.");
            }
        } else {
            Log::info("[AssignAdminRole] El email no es el del admin. No se asigna rol especial.");
        }
    }
}
