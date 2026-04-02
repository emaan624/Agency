<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Services\OrderService;
use App\Services\PaymentGatewayService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderService         $orderService,
        private readonly WalletService        $walletService,
        private readonly PaymentGatewayService $gatewayService,
    ) {}

    // ── Checkout dispatcher ───────────────────────────────────────────────

    public function checkout(Request $request)
    {
        $request->validate([
            'package_id'     => 'required|exists:packages,id',
            'gateway'        => 'nullable|in:stripe,paypal,crypto,wallet',
            'coupon_code'    => 'nullable|string',
            'requirements'   => 'nullable|string',
        ]);

        $package = Package::findOrFail($request->package_id);
        $coupon  = null;
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
        }

        $gateway = $request->input('gateway', 'stripe');

        $order = $this->orderService->create(
            $request->user(),
            $package,
            ['requirements' => $request->requirements, 'payment_method' => $gateway],
            $coupon
        );

        try {
            return match ($gateway) {
                'paypal' => $this->redirectPayPal($order),
                'crypto' => $this->redirectCrypto($order),
                'wallet' => $this->payWithWallet($order, $request->user()),
                default  => $this->redirectStripe($order),
            };
        } catch (\RuntimeException $e) {
            Log::error("Payment gateway error [{$gateway}]", ['error' => $e->getMessage(), 'order' => $order->id]);
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }

    // ── Stripe ────────────────────────────────────────────────────────────

    private function redirectStripe(Order $order)
    {
        if (!$this->gatewayService->isStripeEnabled()) {
            return back()->withErrors(['payment' => 'Stripe payments are currently disabled.']);
        }

        if (!class_exists(\Stripe\Stripe::class)) {
            return back()->withErrors(['payment' => 'Stripe SDK is not installed.']);
        }

        return redirect($this->gatewayService->createStripeSession($order));
    }

    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = $this->gatewayService->stripeWebhookSecret();

        try {
            \Stripe\Stripe::setApiKey($this->gatewayService->stripeSecretKey());
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

    // ── PayPal ────────────────────────────────────────────────────────────

    private function redirectPayPal(Order $order)
    {
        if (!$this->gatewayService->isPayPalEnabled()) {
            return back()->withErrors(['payment' => 'PayPal payments are currently disabled.']);
        }

        return redirect($this->gatewayService->createPayPalOrder($order));
    }

    public function paypalReturn(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $paypalOrderId = $request->query('token') ?? $order->paypal_order_id;

        if (!$paypalOrderId) {
            return redirect()->route('dashboard.orders.show', $order)
                ->withErrors(['payment' => 'PayPal order ID missing.']);
        }

        try {
            $capture = $this->gatewayService->capturePayPalOrder($paypalOrderId);
            $status  = $capture['status'] ?? '';

            if ($status === 'COMPLETED') {
                $order->update(['payment_status' => 'paid', 'status' => 'in_progress']);
                return redirect()->route('dashboard.orders.show', $order)
                    ->with('success', 'PayPal payment completed successfully.');
            }

            return redirect()->route('dashboard.orders.show', $order)
                ->withErrors(['payment' => 'PayPal payment was not completed (status: ' . $status . ').']);
        } catch (\RuntimeException $e) {
            Log::error('PayPal capture error', ['error' => $e->getMessage(), 'order' => $order->id]);
            return redirect()->route('dashboard.orders.show', $order)
                ->withErrors(['payment' => 'PayPal capture failed: ' . $e->getMessage()]);
        }
    }

    public function paypalCancel(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        return redirect()->route('dashboard.orders.show', $order)
            ->with('info', 'PayPal payment was cancelled.');
    }

    public function paypalWebhook(Request $request)
    {
        // PayPal sends IPN/webhook events — basic logging only; extend as needed
        Log::info('PayPal webhook received', ['payload' => $request->all()]);
        return response('OK', 200);
    }

    // ── Crypto (NowPayments) ─────────────────────────────────────────────

    private function redirectCrypto(Order $order)
    {
        if (!$this->gatewayService->isCryptoEnabled()) {
            return back()->withErrors(['payment' => 'Crypto payments are currently disabled.']);
        }

        return redirect($this->gatewayService->createCryptoInvoice($order));
    }

    public function cryptoWebhook(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('x-nowpayments-sig', '');

        if (!$this->gatewayService->verifyCryptoIpn($payload, $signature)) {
            Log::warning('NowPayments IPN signature mismatch');
            return response('Invalid signature', 400);
        }

        $data    = $request->json()->all();
        $orderId = $data['order_id'] ?? null;
        $status  = $data['payment_status'] ?? null;

        if ($orderId && in_array($status, ['finished', 'confirmed', 'sending'])) {
            $order = Order::find($orderId);
            if ($order && $order->payment_status === 'unpaid') {
                $order->update(['payment_status' => 'paid', 'status' => 'in_progress']);
            }
        }

        return response('OK', 200);
    }

    // ── Wallet payment ────────────────────────────────────────────────────

    private function payWithWallet(Order $order, $user)
    {
        $balance = $this->walletService->getBalance($user);

        if ($balance < (float) $order->total) {
            return back()->withErrors(['payment' => 'Insufficient wallet balance.']);
        }

        $this->walletService->deduct($user, (float) $order->total, "Payment for order {$order->order_number}");
        $order->update(['payment_status' => 'paid', 'status' => 'in_progress']);

        return redirect()->route('dashboard.orders.show', $order)
            ->with('success', 'Order paid from wallet balance.');
    }
}
