<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 1000);
        return [
            'order_number'   => 'ORD-' . strtoupper(Str::random(8)),
            'user_id'        => User::factory(),
            'package_id'     => Package::factory(),
            'status'         => 'pending',
            'payment_status' => 'unpaid',
            'subtotal'       => $subtotal,
            'discount'       => 0,
            'total'          => $subtotal,
            'requirements'   => fake()->sentence(),
            'due_at'         => now()->addDays(7),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }
}
