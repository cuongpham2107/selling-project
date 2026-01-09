<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Listeners\AssignDefaultRole;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event subscriber classes to register.
     *
     * @var array<int, class-string>
     */
    protected $subscribe = [
        AssignDefaultRole::class,
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
