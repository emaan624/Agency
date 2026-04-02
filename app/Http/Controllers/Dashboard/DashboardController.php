<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Services\WalletService;

class DashboardController extends Controller
{
    public function __construct(private readonly WalletService $walletService) {}

    public function index()
    {
        $user = auth()->user();
        $stats = [
            'orders_total'    => Order::where('user_id', $user->id)->count(),
            'orders_active'   => Order::where('user_id', $user->id)->whereIn('status', ['pending','in_progress','revision'])->count(),
            'orders_done'     => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'wallet_balance'  => $this->walletService->getBalance($user),
            'tickets_open'    => Ticket::where('user_id', $user->id)->where('status', 'open')->count(),
            'notifications'   => $user->notifications()->whereNull('read_at')->count(),
        ];
        return view('dashboard.index', compact('stats'));
    }
}
