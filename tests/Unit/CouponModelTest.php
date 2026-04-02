<?php

namespace Tests\Unit;

use App\Models\Coupon;
use Database\Factories\CouponFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_percent_coupon_calculates_discount_correctly(): void
    {
        $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 20]);

        $this->assertTrue($coupon->isValid(100));
        $this->assertEquals(20.00, $coupon->calculateDiscount(100));
    }

    public function test_valid_fixed_coupon_calculates_discount_correctly(): void
    {
        $coupon = Coupon::factory()->fixed()->create();

        $this->assertTrue($coupon->isValid(100));
        $this->assertEquals(20.00, $coupon->calculateDiscount(100));
    }

    public function test_fixed_coupon_discount_does_not_exceed_total(): void
    {
        $coupon = Coupon::factory()->fixed()->create(['value' => 50]);

        $this->assertEquals(30.00, $coupon->calculateDiscount(30));
    }

    public function test_inactive_coupon_is_invalid(): void
    {
        $coupon = Coupon::factory()->inactive()->create();

        $this->assertFalse($coupon->isValid(100));
    }

    public function test_expired_coupon_is_invalid(): void
    {
        $coupon = Coupon::factory()->expired()->create();

        $this->assertFalse($coupon->isValid(100));
    }

    public function test_maxed_out_coupon_is_invalid(): void
    {
        $coupon = Coupon::factory()->maxed()->create();

        $this->assertFalse($coupon->isValid(100));
    }

    public function test_coupon_invalid_when_order_below_min_order(): void
    {
        $coupon = Coupon::factory()->create(['min_order' => 200]);

        $this->assertFalse($coupon->isValid(150));
    }

    public function test_coupon_valid_at_exact_min_order(): void
    {
        $coupon = Coupon::factory()->create(['min_order' => 100]);

        $this->assertTrue($coupon->isValid(100));
    }
}
