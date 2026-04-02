<!DOCTYPE html>
<html><body style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:32px;">
<h1 style="color:#6366f1;">Order Confirmed!</h1>
<p>Hi {{ $order->user->name }},</p>
<p>Your order <strong>{{ $order->order_number }}</strong> has been confirmed.</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:8px;border:1px solid #ddd;">Package</td><td style="padding:8px;border:1px solid #ddd;">{{ $order->package?->name ?? 'Custom' }}</td></tr>
    <tr><td style="padding:8px;border:1px solid #ddd;">Total</td><td style="padding:8px;border:1px solid #ddd;">${{ number_format($order->total, 2) }}</td></tr>
    <tr><td style="padding:8px;border:1px solid #ddd;">Status</td><td style="padding:8px;border:1px solid #ddd;">{{ ucfirst($order->status) }}</td></tr>
</table>
<p>We'll get started on your project right away.</p>
<p>— The LuxMotion Team</p>
</body></html>
