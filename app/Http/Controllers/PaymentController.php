<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly WalletService $walletService,
    ) {}

    public function checkout(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'coupon_code' => 'nullable|string',
            'requirements' => 'nullable|string',
        ]);

        $package = Package::findOrFail($request->package_id);
        $coupon = null;
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
        }

        $order = $this->orderService->create(
            $request->user(),
            $package,
            ['requirements' => $request->requirements, 'payment_method' => 'stripe'],
            $coupon
        );

        if (!class_exists(\Stripe\Stripe::class)) {
            return back()->withErrors(['payment' => 'Stripe is not configured.']);
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => $package->name . ' Package'],
                    'unit_amount' => (int) ($order->total * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => ['order_id' => $order->id],
            'success_url' => route('dashboard.orders.show', $order) . '?payment=success',
            'cancel_url' => route('dashboard.orders.show', $order) . '?payment=cancelled',
        ]);

        $order->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature mismatch', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;

            if ($orderId) {
                $order = Order::find($orderId);
                if ($order && $order->payment_status === 'unpaid') {
                    $order->update(['payment_status' => 'paid', 'status' => 'in_progress']);
                }
            }
        }

        return response('OK', 200);
    }
}
