<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Permission;
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
     */
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        // Create permissions first
        $this->createPermissions();

        // Check if the user's email matches the admin email from the config
        if ($user->email === config('services.admin.email')) {
            // Admin user
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $adminRole->givePermissionTo(Permission::all());
            $user->assignRole($adminRole);
        } else {
            // Normal user
            $userRole = Role::firstOrCreate(['name' => 'user']);
            $permissions = [
                'view documents',
                'create documents',
                'edit documents',
                'delete documents',
                'analyze documents',
            ];
            $userRole->givePermissionTo($permissions);
            $user->assignRole($userRole);
        }
    }

    /**
     * Create all necessary permissions if they don't exist.
     */
    private function createPermissions(): void
    {
        $permissions = [
            // Document permissions
            'view documents',
            'create documents',
            'edit documents',
            'delete documents',
            'analyze documents',
            // Admin-only permissions
            'view users',
            'edit users',
            'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
