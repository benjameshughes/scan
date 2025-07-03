<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locations = [
            ['id' => 'loc-1', 'code' => '11A'],
            ['id' => 'loc-2', 'code' => '11B'],
            ['id' => 'loc-3', 'code' => '12A'],
            ['id' => 'loc-4', 'code' => '12B'],
            ['id' => 'default', 'code' => 'Default'],
        ];

        $fromLocation = $this->faker->randomElement($locations);
        $toLocation = $this->faker->randomElement(array_filter($locations, fn ($loc) => $loc['id'] !== $fromLocation['id']));

        return [
            'product_id' => Product::factory(),
            'from_location_id' => $fromLocation['id'],
            'from_location_code' => $fromLocation['code'],
            'to_location_id' => $toLocation['id'],
            'to_location_code' => $toLocation['code'],
            'quantity' => $this->faker->numberBetween(1, 100),
            'type' => $this->faker->randomElement([
                StockMovement::TYPE_BAY_REFILL,
                StockMovement::TYPE_MANUAL_TRANSFER,
                StockMovement::TYPE_SCAN_ADJUSTMENT,
            ]),
            'user_id' => User::factory(),
            'moved_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'metadata' => [
                'stock_before' => $this->faker->numberBetween(100, 1000),
                'location_name' => $this->faker->words(3, true),
            ],
        ];
    }

    /**
     * Indicate that the movement is a bay refill.
     */
    public function bayRefill(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovement::TYPE_BAY_REFILL,
            'to_location_id' => 'default',
            'to_location_code' => 'Default',
        ]);
    }

    /**
     * Indicate that the movement is a manual transfer.
     */
    public function manualTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovement::TYPE_MANUAL_TRANSFER,
        ]);
    }

    /**
     * Indicate that the movement is a scan adjustment.
     */
    public function scanAdjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StockMovement::TYPE_SCAN_ADJUSTMENT,
        ]);
    }
}
