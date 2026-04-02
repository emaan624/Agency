<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWalletTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_wallet_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.wallet.index'))
            ->assertOk();
    }

    public function test_wallet_shows_zero_balance_for_new_user(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.wallet.index'))
            ->assertSee('0');
    }

    public function test_user_can_initiate_deposit(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('dashboard.wallet.deposit'), [
                'amount' => 100,
            ]);

        // Redirects to Stripe or back — just assert no server error
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    public function test_deposit_requires_positive_amount(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('dashboard.wallet.deposit'), [
                'amount' => -50,
            ]);

        $response->assertSessionHasErrors('amount');
    }
}
