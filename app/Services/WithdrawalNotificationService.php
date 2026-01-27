<?php

namespace App\Services;

use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\NewWithdrawalRequest;
use Illuminate\Support\Facades\Notification;

class WithdrawalNotificationService
{
    /**
     * Gửi thông báo cho tất cả super admin khi có yêu cầu rút tiền mới.
     */
    public function notifySuperAdmins(Withdrawal $withdrawal): void
    {
        // Lấy tất cả user có role super_admin
        $superAdmins = User::role(config('filament-shield.super_admin.name'))->get();

        if ($superAdmins->isEmpty()) {
            \Log::warning('No super admins found to notify about withdrawal #'.$withdrawal->id);

            return;
        }

        // Gửi notification cho tất cả super admins
        Notification::send($superAdmins, new NewWithdrawalRequest($withdrawal));

        \Log::info('Notified '.count($superAdmins).' super admins about withdrawal #'.$withdrawal->id);
    }

    /**
     * Gửi thông báo cho một admin cụ thể.
     */
    public function notifyAdmin(User $admin, Withdrawal $withdrawal): void
    {
        $admin->notify(new NewWithdrawalRequest($withdrawal));
    }
}
