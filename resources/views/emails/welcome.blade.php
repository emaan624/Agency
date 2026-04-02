<!DOCTYPE html>
<html><body style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:32px;">
<h1 style="color:#6366f1;">Welcome to LuxMotion Agency!</h1>
<p>Hi {{ $user->name }},</p>
<p>Thank you for joining LuxMotion Agency. We're excited to help you build your brand.</p>
<p>Your referral code is: <strong>{{ $user->referral_code }}</strong></p>
<p>— The LuxMotion Team</p>
</body></html>
