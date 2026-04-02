<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(['code' => 'DEMO20'], [
            'type' => 'percent',
            'value' => 20,
            'min_order' => 0,
            'is_active' => true,
        ]);

        Coupon::updateOrCreate(['code' => 'LAUNCH10'], [
            'type' => 'fixed',
            'value' => 10,
            'min_order' => 50,
            'is_active' => true,
        ]);
    }
}
