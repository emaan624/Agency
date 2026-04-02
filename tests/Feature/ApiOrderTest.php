<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function auth(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_packages_endpoint_is_publicly_accessible(): void
    {
        Package::factory()->count(3)->create();

        $this->getJson('/api/v1/packages')
            ->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_orders_list_requires_auth(): void
    {
        $this->getJson('/api/v1/orders')->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_their_orders(): void
    {
        Order::factory()->count(2)->create(['user_id' => $this->user->id]);
        Order::factory()->create(); // other user's order

        $response = $this->withHeaders($this->auth())
            ->getJson('/api/v1/orders');

        $response->assertOk();
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_authenticated_user_can_create_order_via_api(): void
    {
        $package = Package::factory()->create();

        $response = $this->withHeaders($this->auth())
            ->postJson('/api/v1/orders', [
                'package_id'   => $package->id,
                'requirements' => 'Build a landing page.',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['id', 'order_number', 'status']]);

        $this->assertDatabaseHas('orders', [
            'user_id'    => $this->user->id,
            'package_id' => $package->id,
        ]);
    }

    public function test_create_order_validates_required_fields(): void
    {
        $this->withHeaders($this->auth())
            ->postJson('/api/v1/orders', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['package_id']);
    }

    public function test_user_can_view_own_order_via_api(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->withHeaders($this->auth())
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.order_number', $order->order_number);
    }

    public function test_user_cannot_view_another_users_order_via_api(): void
    {
        $otherOrder = Order::factory()->create();

        $this->withHeaders($this->auth())
            ->getJson("/api/v1/orders/{$otherOrder->id}")
            ->assertForbidden();
    }

    public function test_user_can_cancel_own_pending_order_via_api(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $this->withHeaders($this->auth())
            ->deleteJson("/api/v1/orders/{$order->id}")
            ->assertOk();

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_user_cannot_cancel_another_users_order(): void
    {
        $otherOrder = Order::factory()->create(['status' => 'pending']);

        $this->withHeaders($this->auth())
            ->deleteJson("/api/v1/orders/{$otherOrder->id}")
            ->assertForbidden();
    }
}
