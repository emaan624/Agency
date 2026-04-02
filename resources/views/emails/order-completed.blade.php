<!DOCTYPE html>
<html><body style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:32px;">
<h1 style="color:#6366f1;">Your Order is Complete!</h1>
<p>Hi {{ $order->user->name }},</p>
<p>Great news! Order <strong>{{ $order->order_number }}</strong> has been completed.</p>
<p>Please log in to your dashboard to download your deliverables.</p>
<p>— The LuxMotion Team</p>
</body></html>
