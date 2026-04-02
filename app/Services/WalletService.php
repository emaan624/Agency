<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    public function deposit(User $user, float $amount, string $description = '', array $meta = []): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description, $meta) {
            $wallet = $this->getOrCreateWallet($user);
            $wallet->increment('balance', $amount);

            return Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'currency' => $wallet->currency,
                'status' => 'completed',
                'reference' => Str::uuid(),
                'description' => $description,
                'meta' => $meta,
            ]);
        });
    }

    public function deduct(User $user, float $amount, string $description = '', array $meta = []): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description, $meta) {
            $wallet = $this->getOrCreateWallet($user);

            if ($wallet->balance < $amount) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }

            $wallet->decrement('balance', $amount);

            return Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'deduction',
                'amount' => $amount,
                'currency' => $wallet->currency,
                'status' => 'completed',
                'reference' => Str::uuid(),
                'description' => $description,
                'meta' => $meta,
            ]);
        });
    }

    public function refund(User $user, float $amount, string $description = 'Refund', array $meta = []): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description, $meta) {
            $wallet = $this->getOrCreateWallet($user);
            $wallet->increment('balance', $amount);

            return Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'refund',
                'amount' => $amount,
                'currency' => $wallet->currency,
                'status' => 'completed',
                'reference' => Str::uuid(),
                'description' => $description,
                'meta' => $meta,
            ]);
        });
    }

    public function transfer(User $from, User $to, float $amount, string $description = 'Transfer'): array
    {
        return DB::transaction(function () use ($from, $to, $amount, $description) {
            $debit = $this->deduct($from, $amount, "Transfer to {$to->name}");
            $credit = $this->deposit($to, $amount, "Transfer from {$from->name}");
            return [$debit, $credit];
        });
    }

    public function getBalance(User $user): float
    {
        return (float) $this->getOrCreateWallet($user)->balance;
    }

    private function getOrCreateWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'USD']
        );
    }
}
