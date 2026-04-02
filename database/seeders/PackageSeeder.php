<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Perfect for small projects and startups.',
                'price' => 99.00,
                'revisions' => 1,
                'delivery_days' => 7,
                'features' => ['Logo Design', 'Business Card', '1 Revision', 'Source Files'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'Great for growing businesses.',
                'price' => 199.00,
                'revisions' => 3,
                'delivery_days' => 5,
                'features' => ['Logo Design', 'Business Card', 'Social Media Kit', '3 Revisions', 'Source Files', 'Priority Support'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Full-service for established brands.',
                'price' => 499.00,
                'revisions' => 10,
                'delivery_days' => 3,
                'features' => ['Logo Design', 'Full Brand Identity', 'Website Design', 'Social Media Kit', 'Unlimited Revisions', 'Source Files', 'Dedicated Manager'],
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $pkg) {
            Package::updateOrCreate(['slug' => $pkg['slug']], $pkg);
        }
    }
}
