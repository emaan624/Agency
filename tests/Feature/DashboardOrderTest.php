<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_orders_list_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.orders.index'))
            ->assertOk();
    }

    public function test_create_order_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.orders.create'))
            ->assertOk();
    }

    public function test_user_can_create_order(): void
    {
        $package = Package::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('dashboard.orders.store'), [
                'package_id'   => $package->id,
                'requirements' => 'Please design a modern logo.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id'    => $this->user->id,
            'package_id' => $package->id,
            'status'     => 'pending',
        ]);
    }

    public function test_user_can_view_own_order(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('dashboard.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $otherOrder = Order::factory()->create();

        $this->actingAs($this->user)
            ->get(route('dashboard.orders.show', $otherOrder))
            ->assertForbidden();
    }

    public function test_user_can_request_revision_on_in_progress_order(): void
    {
        $order = Order::factory()->inProgress()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->post(route('dashboard.orders.revision', $order), [
                'notes' => 'Please change the color scheme.',
            ]);

        $response->assertRedirect();
        $this->assertEquals('revision', $order->fresh()->status);
    }
}
