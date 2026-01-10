<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // public static function shouldRegisterNavigation(): bool
    // {
    //     // Chỉ hiển thị Dashboard cho super_admin
    //     return auth()->check() && auth()->user()->hasRole('super_admin');
    // }

    // public static function canAccess(): bool
    // {
    //     // Chỉ cho phép super_admin truy cập
    //     return auth()->check() && auth()->user()->hasRole('super_admin');
    // }
}
