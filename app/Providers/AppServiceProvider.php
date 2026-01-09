<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register User Observer to auto-create Balance and Point
        User::observe(UserObserver::class);

        // Register Message Observer to broadcast events
        \App\Models\Message::observe(\App\Observers\MessageObserver::class);

        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_START,
            fn () => \Livewire\Livewire::mount('topbar-balance'),
        );

        FilamentView::registerRenderHook(
           PanelsRenderHook::GLOBAL_SEARCH_START,
                fn () => \Livewire\Livewire::mount('shopping-cart-icon'),
        );

        
    }
}
