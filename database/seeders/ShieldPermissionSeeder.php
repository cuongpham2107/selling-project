<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get all permissions
        $allPermissions = Permission::all();

        // SUPER ADMIN: Full Access
        $superAdmin = Role::findByName('super_admin');
        $superAdmin->syncPermissions($allPermissions);
        $this->command->info("✓ super_admin: " . $allPermissions->count() . " permissions assigned");

        // SUPPORT ADMIN
        // - Xử lý tranh chấp (Disputes, ShopTransactions, Transactions)
        // - Xoá bài vi phạm ở chat tổng (Chats, Messages)
        // - User mgmt (View only)
        // - Access pages: Chat, Market, SinglePageUser
        $supportPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', '%:Dispute')
                ->orWhere('name', 'like', '%:Chat')
                ->orWhere('name', 'like', '%:Message')
                ->orWhere('name', 'like', 'View%:User')
                ->orWhere('name', 'like', 'page_%');
        })->get();

        $supportAdmin = Role::findByName('support_admin');
        $supportAdmin->syncPermissions($supportPermissions);
        $this->command->info("✓ support_admin: " . $supportPermissions->count() . " permissions assigned");

        // CENSOR STAFF (Kiểm duyệt viên)
        // - Giao dịch trung gian: xử lý tranh chấp (Transactions)
        // - Gian hàng: xử lý khiếu nại, khoá gian hàng (ShopTransactions, ShopProducts)
        // - Xoá bài viết vi phạm (Messages)
        // - Xem tranh chấp (Disputes)
        // - Access pages
        $censorPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', '%:Transaction')
                ->orWhere('name', 'like', '%:ShopProduct')
                ->orWhere('name', 'like', '%:ShopTransaction')
                ->orWhere('name', 'like', '%:Dispute')
                ->orWhere('name', 'like', '%:Message')
                ->orWhere('name', 'like', 'page_%');
        })->get();

        $censorStaff = Role::findByName('censor_staff');
        $censorStaff->syncPermissions($censorPermissions);
        $this->command->info("✓ censor_staff: " . $censorPermissions->count() . " permissions assigned");

        // PANEL USER
        // - Basic access to resources they own (Policies will handle "own" check)
        // - Transactions, Shop, Deposits, Withdrawals, Balance, Points, Chat
        // - No delete/force delete/restore permissions
        // - Access pages
        $panelUserPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', 'View%:Transaction')
                ->orWhere('name', 'like', 'Create:Transaction')
                ->orWhere('name', 'like', 'Update:Transaction')
                ->orWhere('name', 'like', 'View%:ShopProduct')
                ->orWhere('name', 'like', 'Create:ShopProduct')
                ->orWhere('name', 'like', 'Update:ShopProduct')
                ->orWhere('name', 'like', 'Delete:ShopProduct')
                ->orWhere('name', 'like', 'View%:ShopTransaction')
                ->orWhere('name', 'like', 'Create:ShopTransaction')
                ->orWhere('name', 'like', 'Update:ShopTransaction')
                ->orWhere('name', 'like', 'View%:Deposit')
                ->orWhere('name', 'like', 'Create:Deposit')
                ->orWhere('name', 'like', 'View%:Dispute')
                ->orWhere('name', 'like', 'View%:Withdrawal')
                ->orWhere('name', 'like', 'Create:Withdrawal')
                ->orWhere('name', 'like', 'View%:Balance')
                ->orWhere('name', 'like', 'View%:Point')
                ->orWhere('name', 'like', 'View%:PointTransaction')
                ->orWhere('name', 'like', 'View%:Chat')
                ->orWhere('name', 'like', 'Create:Chat')
                ->orWhere('name', 'like', 'View%:Message')
                ->orWhere('name', 'like', 'Create:Message')
                ->orWhere('name', 'like', 'View:ChatPage')
                ->orWhere('name', 'like', 'View:Market');
        })->get();

        $panelUser = Role::findByName('panel_user');
        $panelUser->syncPermissions($panelUserPermissions);
        $this->command->info("✓ panel_user: " . $panelUserPermissions->count() . " permissions assigned");

        $this->command->info("\n🛡️  Shield permissions assigned successfully!");
    }
}
