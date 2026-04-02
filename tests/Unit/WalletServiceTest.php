<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WalletService::class);
    }

    public function test_deposit_creates_wallet_and_transaction(): void
    {
        $user = User::factory()->create();

        $tx = $this->service->deposit($user, 100.00, 'Test deposit');

        $this->assertEquals(100.00, $this->service->getBalance($user));
        $this->assertEquals('deposit', $tx->type);
        $this->assertEquals(100.00, (float) $tx->amount);
        $this->assertEquals('completed', $tx->status);
    }

    public function test_deposit_increments_existing_wallet(): void
    {
        $user = User::factory()->create();
        $this->service->deposit($user, 50.00);
        $this->service->deposit($user, 75.00);

        $this->assertEquals(125.00, $this->service->getBalance($user));
    }

    public function test_deduct_reduces_balance_and_returns_transaction(): void
    {
        $user = User::factory()->create();
        $this->service->deposit($user, 200.00);

        $tx = $this->service->deduct($user, 80.00, 'Payment');

        $this->assertEquals(120.00, $this->service->getBalance($user));
        $this->assertEquals('deduction', $tx->type);
        $this->assertEquals(80.00, (float) $tx->amount);
    }

    public function test_deduct_throws_on_insufficient_balance(): void
    {
        $user = User::factory()->create();
        $this->service->deposit($user, 30.00);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient wallet balance.');

        $this->service->deduct($user, 100.00);
    }

    public function test_refund_adds_back_to_balance(): void
    {
        $user = User::factory()->create();
        $this->service->deposit($user, 100.00);
        $this->service->deduct($user, 100.00);

        $tx = $this->service->refund($user, 100.00, 'Refund');

        $this->assertEquals(100.00, $this->service->getBalance($user));
        $this->assertEquals('refund', $tx->type);
    }

    public function test_transfer_moves_funds_between_users(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $this->service->deposit($sender, 200.00);

        [$debit, $credit] = $this->service->transfer($sender, $receiver, 60.00);

        $this->assertEquals(140.00, $this->service->getBalance($sender));
        $this->assertEquals(60.00, $this->service->getBalance($receiver));
        $this->assertEquals('deduction', $debit->type);
        $this->assertEquals('deposit', $credit->type);
    }

    public function test_transfer_fails_if_sender_has_insufficient_balance(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $this->service->deposit($sender, 10.00);

        $this->expectException(RuntimeException::class);
        $this->service->transfer($sender, $receiver, 100.00);

        // Receiver should not have received anything
        $this->assertEquals(0.00, $this->service->getBalance($receiver));
    }

    public function test_get_balance_returns_zero_for_new_user(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(0.00, $this->service->getBalance($user));
    }
}
