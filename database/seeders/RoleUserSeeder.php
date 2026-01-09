<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset Cached Roles/Permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Define Roles (Shield will auto-generate permissions via shield:generate)
        $roles = ['super_admin', 'support_admin', 'censor_staff', 'panel_user'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Note: Run `php artisan shield:generate` to create permissions for all Resources/Pages/Widgets
        // Then manually assign permissions to roles via Filament Shield UI or programmatically

        // 3. Create Test Users
        $accounts = [
            // Admin accounts
            ['name' => 'Administrator', 'username' => 'admin', 'email' => 'admin@gmail.com', 'role' => 'super_admin', 'phone' => '0901234567'],
            ['name' => 'Support Admin', 'username' => 'support', 'email' => 'support@gmail.com', 'role' => 'support_admin', 'phone' => '0901234568'],
            ['name' => 'Censor Staff', 'username' => 'censor', 'email' => 'censor@gmail.com', 'role' => 'censor_staff', 'phone' => '0901234569'],
            
            // Regular users (panel_user) - for testing shop & transactions
            ['name' => 'Nguyễn Văn A', 'username' => 'nguyenvana', 'email' => 'nguyenvana@gmail.com', 'role' => 'panel_user', 'phone' => '0901234570'],
            ['name' => 'Trần Thị B', 'username' => 'tranthib', 'email' => 'tranthib@gmail.com', 'role' => 'panel_user', 'phone' => '0901234571'],
            ['name' => 'Lê Văn C', 'username' => 'levanc', 'email' => 'levanc@gmail.com', 'role' => 'panel_user', 'phone' => '0901234572'],
            ['name' => 'Phạm Thị D', 'username' => 'phamthid', 'email' => 'phamthid@gmail.com', 'role' => 'panel_user', 'phone' => '0901234573'],
            ['name' => 'Hoàng Văn E', 'username' => 'hoangvane', 'email' => 'hoangvane@gmail.com', 'role' => 'panel_user', 'phone' => '0901234574'],
            ['name' => 'Vũ Thị F', 'username' => 'vuthif', 'email' => 'vuthif@gmail.com', 'role' => 'panel_user', 'phone' => '0901234575'],
            ['name' => 'Đặng Văn G', 'username' => 'dangvang', 'email' => 'dangvang@gmail.com', 'role' => 'panel_user', 'phone' => '0901234576'],
            ['name' => 'Bùi Thị H', 'username' => 'buithih', 'email' => 'buithih@gmail.com', 'role' => 'panel_user', 'phone' => '0901234577'],
        ];

        foreach ($accounts as $acc) {
            $user = User::updateOrCreate(
                ['username' => $acc['username']],
                [
                    'name' => $acc['name'],
                    'email' => $acc['email'],
                    'password' => '12345678',
                    'phone' => $acc['phone'],
                ]
            );

            $user->syncRoles($acc['role']);

            $this->command->info("User created: {$user->username} with role {$acc['role']}");
        }
    }
}
