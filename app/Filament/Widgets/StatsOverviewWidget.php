<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $revenue = Transaction::where('type', 'deposit')->where('status', 'completed')->sum('amount');
        $activeOrders = Order::whereIn('status', ['pending', 'in_progress', 'revision'])->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalUsers = User::where('is_admin', false)->count();
        $newUsersToday = User::where('created_at', '>=', today())->count();
        $pendingKyc = \App\Models\KycVerification::where('status', 'pending')->count();

        return [
            Stat::make('Total Revenue', '$' . number_format($revenue, 2))
                ->description('From all deposits')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Active Orders', $activeOrders)
                ->description('Pending + in progress + revision')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),
            Stat::make('Completed Orders', $completedOrders)
                ->description('All time')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),
            Stat::make('Total Clients', $totalUsers)
                ->description('+' . $newUsersToday . ' today')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Pending KYC', $pendingKyc)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-identification')
                ->color($pendingKyc > 0 ? 'danger' : 'success'),
        ];
    }
}
