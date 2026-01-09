<?php

namespace App\Listeners;

use Filament\Auth\Events\Registered as FilamentRegistered;
use Illuminate\Contracts\Events\Dispatcher;
use Spatie\Permission\Models\Role;

class AssignDefaultRole
{
    /**
     * Handle the event.
     */
    public function handle(FilamentRegistered $event): void
    {
        $user = $event->getUser();

        if (! is_object($user)) {
            return;
        }

        try {
            $roleName = config('auth.default_user_role', 'panel_user');

            if (class_exists(Role::class) && ! Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
            }

            if (method_exists($user, 'assignRole')) {
                $user->assignRole($roleName);
            }
        } catch (\Throwable $e) {
            logger()->error('AssignDefaultRole listener failed: ' . $e->getMessage());
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            FilamentRegistered::class,
            [self::class, 'handle']
        );
    }
}
