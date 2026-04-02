<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'LuxMotion Agency', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Premium Creative Services', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'info@luxmotion.agency', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '+1 (555) 000-0000', 'group' => 'general'],
            ['key' => 'site_address', 'value' => '123 Agency Lane, NY 10001', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'USD', 'group' => 'payment'],
            ['key' => 'stripe_enabled', 'value' => '1', 'group' => 'payment'],
            ['key' => 'wallet_enabled', 'value' => '1', 'group' => 'payment'],
            ['key' => 'referral_commission_percent', 'value' => '5', 'group' => 'referral'],
            ['key' => 'kyc_required', 'value' => '0', 'group' => 'kyc'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'general'],
            ['key' => 'theme', 'value' => 'dark', 'group' => 'appearance'],
            ['key' => 'hero_title', 'value' => 'Creative Agency for Modern Brands', 'group' => 'homepage'],
            ['key' => 'hero_subtitle', 'value' => 'We deliver premium design, development, and marketing services.', 'group' => 'homepage'],
            ['key' => 'orders_total', 'value' => '1200', 'group' => 'stats'],
            ['key' => 'clients_total', 'value' => '340', 'group' => 'stats'],
            ['key' => 'satisfaction_rate', 'value' => '98', 'group' => 'stats'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}
