<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AssignOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Order $order,
        private readonly User $staff,
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $this->order->update(['assigned_to' => $this->staff->id, 'status' => 'in_progress']);

        $notificationService->send(
            $this->order->user,
            'Your order has been assigned',
            "Order {$this->order->order_number} is now being processed.",
            'info',
            route('dashboard.orders.show', $this->order)
        );
    }
}
