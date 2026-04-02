<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderService::class);
    }

    public function test_create_order_without_coupon(): void
    {
        $user    = User::factory()->create();
        $package = Package::factory()->create(['price' => 300.00, 'delivery_days' => 5]);

        $order = $this->service->create($user, $package);

        $this->assertEquals('pending', $order->status);
        $this->assertEquals(300.00, (float) $order->subtotal);
        $this->assertEquals(0, (float) $order->discount);
        $this->assertEquals(300.00, (float) $order->total);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals($package->id, $order->package_id);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    public function test_create_order_with_percent_coupon(): void
    {
        $user    = User::factory()->create();
        $package = Package::factory()->create(['price' => 200.00]);
        $coupon  = Coupon::factory()->create(['type' => 'percent', 'value' => 10]);

        $order = $this->service->create($user, $package, [], $coupon);

        $this->assertEquals(20.00, (float) $order->discount);
        $this->assertEquals(180.00, (float) $order->total);
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_create_order_with_fixed_coupon(): void
    {
        $user    = User::factory()->create();
        $package = Package::factory()->create(['price' => 100.00]);
        $coupon  = Coupon::factory()->fixed()->create(['value' => 30]);

        $order = $this->service->create($user, $package, [], $coupon);

        $this->assertEquals(30.00, (float) $order->discount);
        $this->assertEquals(70.00, (float) $order->total);
    }

    public function test_invalid_coupon_is_not_applied(): void
    {
        $user    = User::factory()->create();
        $package = Package::factory()->create(['price' => 100.00]);
        $coupon  = Coupon::factory()->expired()->create();

        $order = $this->service->create($user, $package, [], $coupon);

        $this->assertEquals(0.00, (float) $order->discount);
        $this->assertEquals(100.00, (float) $order->total);
    }

    public function test_assign_sets_staff_and_status(): void
    {
        $order = Order::factory()->create();
        $staff = User::factory()->admin()->create();

        $updated = $this->service->assign($order, $staff);

        $this->assertEquals($staff->id, $updated->assigned_to);
        $this->assertEquals('in_progress', $updated->status);
    }

    public function test_revise_creates_revision_and_updates_status(): void
    {
        $user  = User::factory()->create();
        $order = Order::factory()->inProgress()->create(['user_id' => $user->id]);

        $updated = $this->service->revise($order, $user, 'Please change the logo color.');

        $this->assertEquals('revision', $updated->status);
        $this->assertCount(1, $order->fresh()->revisions);
    }

    public function test_complete_sets_status_and_timestamps(): void
    {
        $order = Order::factory()->inProgress()->paid()->create();

        $completed = $this->service->complete($order);

        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->completed_at);
    }

    public function test_cancel_sets_status_cancelled(): void
    {
        $order = Order::factory()->create(['status' => 'pending', 'payment_status' => 'unpaid']);

        $cancelled = $this->service->cancel($order);

        $this->assertEquals('cancelled', $cancelled->status);
    }

    public function test_cancel_refunds_wallet_when_already_paid(): void
    {
        $user  = User::factory()->create();
        $order = Order::factory()->paid()->create(['user_id' => $user->id, 'total' => 150.00]);
        $walletService = app(WalletService::class);
        $walletService->deposit($user, 150.00); // simulate prior payment

        $this->service->cancel($order);

        // Balance should be refunded back
        $this->assertEquals(300.00, $walletService->getBalance($user));
    }

    public function test_total_is_never_negative_with_excessive_fixed_coupon(): void
    {
        $user    = User::factory()->create();
        $package = Package::factory()->create(['price' => 50.00]);
        $coupon  = Coupon::factory()->fixed()->create(['value' => 999]);

        $order = $this->service->create($user, $package, [], $coupon);

        $this->assertEquals(0.00, (float) $order->total);
    }
}
