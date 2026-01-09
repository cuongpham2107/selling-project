<?php

namespace Database\Seeders;

use App\Models\FeeTier;
use App\Models\PointTier;
use Illuminate\Database\Seeder;

class SystemTierSeeder extends Seeder
{
    public function run(): void
    {
        FeeTier::truncate();
        PointTier::truncate();

        // Fee Tiers (Middleman transactions)
        $feeTiers = [
            ['min_amount' => 0, 'max_amount' => 99999, 'fee' => 4000, 'type' => 'middle'],
            ['min_amount' => 100000, 'max_amount' => 199999, 'fee' => 6000, 'type' => 'middle'],
            ['min_amount' => 200000, 'max_amount' => 999999, 'fee' => 10000, 'type' => 'middle'],
            ['min_amount' => 1000000, 'max_amount' => 1999999, 'fee' => 16000, 'type' => 'middle'],
            ['min_amount' => 2000000, 'max_amount' => 4999999, 'fee' => 36000, 'type' => 'middle'],
            ['min_amount' => 5000000, 'max_amount' => 9999999, 'fee' => 66000, 'type' => 'middle'],
            ['min_amount' => 10000000, 'max_amount' => 29999999, 'fee' => 150000, 'type' => 'middle'],
            ['min_amount' => 30000000, 'max_amount' => null, 'fee' => 300000, 'type' => 'middle'],
            // Shop transactions fee is fixed 1% (represented as 1 in this context, logic will handle decimal)
            ['min_amount' => 0, 'max_amount' => null, 'fee' => 1, 'type' => 'shop'],
        ];

        foreach ($feeTiers as $tier) {
            FeeTier::updateOrCreate(
                ['min_amount' => $tier['min_amount'], 'max_amount' => $tier['max_amount'], 'type' => $tier['type']],
                $tier
            );
        }

        // Point Tiers
        $pointTiers = [
            ['min_amount' => 0, 'max_amount' => 99999, 'points' => 2],
            ['min_amount' => 100000, 'max_amount' => 199999, 'points' => 3],
            ['min_amount' => 200000, 'max_amount' => 999999, 'points' => 5],
            ['min_amount' => 1000000, 'max_amount' => 1999999, 'points' => 8],
            ['min_amount' => 2000000, 'max_amount' => 4999999, 'points' => 16],
            ['min_amount' => 5000000, 'max_amount' => 9999999, 'points' => 32],
            ['min_amount' => 10000000, 'max_amount' => 29999999, 'points' => 75],
            ['min_amount' => 30000000, 'max_amount' => null, 'points' => 150],
        ];

        foreach ($pointTiers as $tier) {
            PointTier::updateOrCreate(
                ['min_amount' => $tier['min_amount'], 'max_amount' => $tier['max_amount']],
                $tier
            );
        }
    }
}
