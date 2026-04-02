<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        return [
            'name'          => ucwords($name),
            'slug'          => Str::slug($name),
            'description'   => fake()->sentence(),
            'price'         => fake()->randomFloat(2, 50, 2000),
            'currency'      => 'USD',
            'revisions'     => fake()->numberBetween(1, 5),
            'delivery_days' => fake()->numberBetween(3, 30),
            'features'      => ['Feature A', 'Feature B'],
            'is_active'     => true,
            'sort_order'    => 0,
        ];
    }
}
