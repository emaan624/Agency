<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@luxmotion.agency'], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'referral_code' => Str::random(8),
            'email_verified_at' => now(),
        ]);
        $admin->wallet()->firstOrCreate(['user_id' => $admin->id], ['balance' => 0, 'currency' => 'USD']);

        $user = User::firstOrCreate(['email' => 'user@luxmotion.agency'], [
            'name' => 'Demo User',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'referral_code' => Str::random(8),
            'email_verified_at' => now(),
        ]);
        $user->wallet()->firstOrCreate(['user_id' => $user->id], ['balance' => 100.00, 'currency' => 'USD']);
    }
}
