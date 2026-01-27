<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

class PointDistributionTest extends Tests\TestCase
{
    use RefreshDatabase;

    public function test_point_distribution_limited_by_pool()
    {
        \App\Models\Setting::setValue('point_total_limit', 100);
        \App\Models\Setting::setValue('point_total_distributed', 90);

        $limit = \App\Models\Setting::getValue('point_total_limit');
        $distributed = \App\Models\Setting::getValue('point_total_distributed');

        $pointsToAward = min(20, $limit - $distributed);
        $this->assertEquals(10, $pointsToAward);

        \App\Models\Setting::setValue('point_total_distributed', $distributed + $pointsToAward);
        $this->assertEquals(100, \App\Models\Setting::getValue('point_total_distributed'));
    }

    public function test_point_redemption_increments_redeemed_setting()
    {
        \App\Models\Setting::setValue('point_total_redeemed', 0);

        $pointsToRedeem = 50;
        $currentRedeemed = \App\Models\Setting::getValue('point_total_redeemed', 0);
        \App\Models\Setting::setValue('point_total_redeemed', $currentRedeemed + $pointsToRedeem);

        $this->assertEquals(50, \App\Models\Setting::getValue('point_total_redeemed'));
    }

    public function test_unified_point_transaction_record()
    {
        $user = \App\Models\User::factory()->create();
        $point = $user->point;
        $point->points = 100;
        $point->save();

        \App\Services\BalanceTransactionService::incrementPoints($user, 50, 'point_earn', description: 'Test earn');

        $transaction = \App\Models\BalanceTransaction::query()->where('user_id', '=', $user->id)
            ->where('currency', '=', 'point')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals(50, (float) $transaction->amount);
        $this->assertEquals(150, (float) $transaction->points_after);
        $this->assertEquals('point_earn', $transaction->type);
    }
}
