<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code'       => strtoupper(fake()->unique()->bothify('??##??')),
            'type'       => 'percent',
            'value'      => 10,
            'min_order'  => 0,
            'max_uses'   => null,
            'used_count' => 0,
            'is_active'  => true,
            'expires_at' => null,
        ];
    }

    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'  => 'fixed',
            'value' => 20,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function maxed(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_uses'   => 1,
            'used_count' => 1,
        ]);
    }
}
