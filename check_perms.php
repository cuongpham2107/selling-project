<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== CHECKING USER ROLES & PERMISSIONS ===\n\n";

$users = ['admin', 'nguyenvana', 'vuthif', 'tranvanc'];

foreach ($users as $username) {
    $user = User::where('username', $username)->first();
    
    if (!$user) {
        echo "User '$username' not found!\n\n";
        continue;
    }
    
    echo "User: {$user->username}\n";
    echo "DB Role: {$user->role}\n";
    echo "Spatie Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
    echo "Total Permissions: " . $user->getAllPermissions()->count() . "\n\n";
}
