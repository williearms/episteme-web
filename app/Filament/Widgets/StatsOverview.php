<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $revenue = (int) Order::where('status', OrderStatus::Paid)->sum('total');

        return [
            Stat::make('Revenue (paid orders)', 'Rp'.number_format($revenue, 0, ',', '.')),
            Stat::make('Paid orders', Order::where('status', OrderStatus::Paid)->count()),
            Stat::make('Pending orders', Order::where('status', OrderStatus::Pending)->count()),
            Stat::make('Enrollments', Enrollment::count()),
            Stat::make('Users', User::count()),
        ];
    }
}
