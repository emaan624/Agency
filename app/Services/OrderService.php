<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(private readonly WalletService $walletService) {}

    public function create(User $user, Package $package, array $data = [], ?Coupon $coupon = null): Order
    {
        return DB::transaction(function () use ($user, $package, $data, $coupon) {
            $subtotal = (float) $package->price;
            $discount = 0;

            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
                $coupon->increment('used_count');
            }

            $total = max(0, $subtotal - $discount);

            return Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'package_id' => $package->id,
                'coupon_id' => $coupon?->id,
                'status' => 'pending',
                'requirements' => $data['requirements'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => 'unpaid',
                'due_at' => now()->addDays($package->delivery_days),
            ]);
        });
    }

    public function assign(Order $order, User $staff): Order
    {
        $order->update([
            'assigned_to' => $staff->id,
            'status' => 'in_progress',
        ]);
        return $order->fresh();
    }

    public function revise(Order $order, User $user, string $notes): Order
    {
        DB::transaction(function () use ($order, $user, $notes) {
            $order->revisions()->create([
                'requested_by' => $user->id,
                'notes' => $notes,
            ]);
            $order->update(['status' => 'revision']);
        });
        return $order->fresh();
    }

    public function complete(Order $order): Order
    {
        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
            'payment_status' => 'paid',
        ]);
        return $order->fresh();
    }

    public function cancel(Order $order): Order
    {
        DB::transaction(function () use ($order) {
            if ($order->payment_status === 'paid') {
                $this->walletService->refund($order->user, (float) $order->total, "Refund for order {$order->order_number}");
            }
            $order->update(['status' => 'cancelled']);
        });
        return $order->fresh();
    }
}
