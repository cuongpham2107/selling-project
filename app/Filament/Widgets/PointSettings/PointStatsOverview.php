<?php

namespace App\Filament\Widgets\PointSettings;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PointStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $limit = \App\Models\Setting::getValue('point_total_limit', 50000000);
        $distributed = \App\Models\Setting::getValue('point_total_distributed', 0);
        $redeemed = \App\Models\Setting::getValue('point_total_redeemed', 0);
        $remaining = $limit - $distributed;

        return [
            Stat::make('Tổng phát tối đa', number_format($limit).' Point')
                ->description('Giới hạn hệ thống')
                ->color('info'),
            Stat::make('Đã phát', number_format($distributed).' Point')
                ->description('Số điểm đã cộng cho người dùng')
                ->color('success'),
            Stat::make('Đã quy đổi', number_format($redeemed).' Point')
                ->description('Số điểm đã đổi thành VNĐ')
                ->color('warning'),
            Stat::make('Còn lại', number_format($remaining).' Point')
                ->description('Số điểm còn trong pool')
                ->color($remaining > 0 ? 'success' : 'danger'),
        ];
    }
}
