<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
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
        // Se comprueba si es el primer usuario registrado en la base de datos.
        if (User::count() === 1) {
            /** @var User $user */
            $user = $event->user;

            // Se busca el rol 'admin'. Es crucial que este rol exista.
            $adminRole = Role::where('name', 'admin')->first();

            if ($adminRole) {
                $user->assignRole($adminRole);
            }
        }
    }
}
