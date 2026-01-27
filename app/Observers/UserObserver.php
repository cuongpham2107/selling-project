<?php

namespace App\Observers;

use App\Models\Balance;
use App\Models\Point;
use App\Models\User;
use Illuminate\Support\Str;
class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Auto-create Balance for new user
        Balance::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'held_balance' => 0]
        );

        // Auto-create Point for new user
        Point::firstOrCreate(
            ['user_id' => $user->id],
            ['points' => 0]
        );

        // Tạo 1 mã referral_code ngẫu nhiên
        $referral_code = strtoupper(Str::random(10));
        $existing = User::where('referral_code', $referral_code)->first();
        while ($existing) {
            $referral_code = strtoupper(Str::random(10));
            $existing = User::where('referral_code', $referral_code)->first();
        }
        $user->referral_code = $referral_code;
        $user->save();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Optional: Clean up related Balance and Point records
        $user->balance()?->delete();
        $user->point()?->delete();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
