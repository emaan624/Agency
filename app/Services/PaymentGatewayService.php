<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    // ── Stripe ────────────────────────────────────────────────────────────

    public function isStripeEnabled(): bool
    {
        return (bool) Setting::get('stripe_enabled', '1');
    }

    public function stripePublishableKey(): ?string
    {
        return Setting::get('stripe_key') ?: config('services.stripe.key');
    }

    public function stripeSecretKey(): ?string
    {
        return Setting::get('stripe_secret') ?: config('services.stripe.secret');
    }

    public function stripeWebhookSecret(): ?string
    {
        return Setting::get('stripe_webhook_secret') ?: config('services.stripe.webhook_secret');
    }

    /**
     * Create a Stripe Checkout Session and return the redirect URL.
     */
    public function createStripeSession(Order $order): string
    {
        \Stripe\Stripe::setApiKey($this->stripeSecretKey());

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => strtolower(Setting::get('currency', 'USD')),
                    'product_data' => ['name' => $order->package->name . ' Package'],
                    'unit_amount'  => (int) ($order->total * 100),
                ],
                'quantity' => 1,
            ]],
            'mode'         => 'payment',
            'metadata'     => ['order_id' => $order->id],
            'success_url'  => route('dashboard.orders.show', $order) . '?payment=success',
            'cancel_url'   => route('dashboard.orders.show', $order) . '?payment=cancelled',
        ]);

        $order->update(['stripe_session_id' => $session->id]);

        return $session->url;
    }

    // ── PayPal ────────────────────────────────────────────────────────────

    public function isPayPalEnabled(): bool
    {
        return (bool) Setting::get('paypal_enabled', '0');
    }

    public function payPalClientId(): ?string
    {
        return Setting::get('paypal_client_id') ?: config('services.paypal.client_id');
    }

    public function payPalClientSecret(): ?string
    {
        return Setting::get('paypal_client_secret') ?: config('services.paypal.client_secret');
    }

    public function payPalMode(): string
    {
        return Setting::get('paypal_mode', 'sandbox') === 'live' ? 'live' : 'sandbox';
    }

    private function payPalBaseUrl(): string
    {
        return $this->payPalMode() === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get a PayPal OAuth2 access token.
     */
    public function getPayPalAccessToken(): string
    {
        $response = Http::withBasicAuth($this->payPalClientId(), $this->payPalClientSecret())
            ->asForm()
            ->post($this->payPalBaseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('PayPal: failed to obtain access token — ' . $response->body());
        }

        return $response->json('access_token');
    }

    /**
     * Create a PayPal order and return the approval URL to redirect the customer to.
     */
    public function createPayPalOrder(Order $order): string
    {
        $token = $this->getPayPalAccessToken();
        $currency = strtoupper(Setting::get('currency', 'USD'));

        $response = Http::withToken($token)
            ->post($this->payPalBaseUrl() . '/v2/checkout/orders', [
                'intent'         => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $order->id,
                    'description'  => $order->package->name . ' Package',
                    'amount'       => [
                        'currency_code' => $currency,
                        'value'         => number_format((float) $order->total, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => route('payment.paypal.return', $order),
                    'cancel_url' => route('payment.paypal.cancel', $order),
                    'brand_name' => Setting::get('site_name', 'LuxMotion Agency'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('PayPal: failed to create order — ' . $response->body());
        }

        $paypalOrderId = $response->json('id');
        $order->update(['paypal_order_id' => $paypalOrderId]);

        $approveUrl = collect($response->json('links'))
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$approveUrl) {
            throw new \RuntimeException('PayPal: approval URL not found in response.');
        }

        return $approveUrl;
    }

    /**
     * Capture an approved PayPal order.
     */
    public function capturePayPalOrder(string $paypalOrderId): array
    {
        $token = $this->getPayPalAccessToken();

        $response = Http::withToken($token)
            ->post($this->payPalBaseUrl() . "/v2/checkout/orders/{$paypalOrderId}/capture");

        if (!$response->successful()) {
            throw new \RuntimeException('PayPal: failed to capture payment — ' . $response->body());
        }

        return $response->json();
    }

    // ── Crypto (NowPayments) ─────────────────────────────────────────────

    public function isCryptoEnabled(): bool
    {
        return (bool) Setting::get('crypto_enabled', '0');
    }

    public function cryptoApiKey(): ?string
    {
        return Setting::get('nowpayments_api_key') ?: config('services.nowpayments.api_key');
    }

    public function cryptoIpnSecret(): ?string
    {
        return Setting::get('nowpayments_ipn_secret') ?: config('services.nowpayments.ipn_secret');
    }

    public function acceptedCryptoCoins(): string
    {
        return Setting::get('crypto_accepted_coins', 'BTC,ETH,USDT,LTC,BNB');
    }

    /**
     * Create a NowPayments invoice and return the invoice URL.
     */
    public function createCryptoInvoice(Order $order): string
    {
        $currency = strtolower(Setting::get('currency', 'USD'));

        $response = Http::withHeaders([
            'x-api-key'    => $this->cryptoApiKey(),
            'Content-Type' => 'application/json',
        ])->post(config('services.nowpayments.base_url') . '/invoice', [
            'price_amount'      => (float) $order->total,
            'price_currency'    => $currency,
            'pay_currency'      => strtolower(explode(',', $this->acceptedCryptoCoins())[0] ?? 'btc'),
            'order_id'          => (string) $order->id,
            'order_description' => $order->package->name . ' Package',
            'success_url'       => route('dashboard.orders.show', $order) . '?payment=success',
            'cancel_url'        => route('dashboard.orders.show', $order) . '?payment=cancelled',
            'ipn_callback_url'  => route('payment.crypto.webhook'),
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('NowPayments: failed to create invoice — ' . $response->body());
        }

        $invoiceId = $response->json('id');
        $order->update(['crypto_invoice_id' => $invoiceId]);

        return $response->json('invoice_url')
            ?? "https://nowpayments.io/payment/?iid={$invoiceId}";
    }

    /**
     * Verify a NowPayments IPN signature.
     */
    public function verifyCryptoIpn(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha512', $payload, $this->cryptoIpnSecret());
        return hash_equals($expected, strtolower($signature));
    }
}
