<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Setting::setValue('point_total_limit', 50000000, 'int', 'Tổng phát tối đa');
        \App\Models\Setting::setValue('point_total_distributed', 0, 'int', 'Đã phát');
        \App\Models\Setting::setValue('point_total_redeemed', 0, 'int', 'Đã quy đổi');
    }
}
