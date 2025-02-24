<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scan>
 */
class ScanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'barcode' => Product::inRandomOrder()->first()->barcode,
            'quantity' => fake()->numberBetween(1, 100),
            'submitted' => fake()->boolean(),
            'submitted_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'user_id' => fake()->randomElement(User::pluck('id')->toArray()),
            'sync_status'  => function (array $attributes) {
                return $attributes['submitted']
                    ? 'synced'
                    : fake()->randomElement(['pending', 'syncing']);
            },
            'created_at'   => function (array $attributes) {
                // Use Carbon to subtract one minute from the submitted_at value.
                return Carbon::parse($attributes['submitted_at'])->subMinute();
            },
        ];
    }
}
