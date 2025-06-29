<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
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
        $submitted = fake()->boolean(0.75); // 75% chance of being submitted
        $action = fake()->randomElement(['increase', 'decrease']);

        // Create realistic sync status based on submission
        if ($submitted) {
            $syncStatus = fake()->randomElement([
                'synced' => 0.8,    // 80% chance if submitted
                'failed' => 0.15,   // 15% chance of failure
                'pending' => 0.05,   // 5% chance still pending
            ]);
        } else {
            $syncStatus = fake()->randomElement([
                'pending' => 0.7,   // 70% chance if not submitted
                'failed' => 0.2,    // 20% chance of failure
                'synced' => 0.1,     // 10% chance already synced
            ]);
        }

        $createdAt = fake()->dateTimeBetween('-1 year', 'now');
        $submittedAt = $submitted ?
            fake()->dateTimeBetween($createdAt, 'now') :
            null;

        return [
            'barcode' => function () {
                // Try to get an existing product, or create one if none exist
                $product = Product::inRandomOrder()->first();
                if (! $product) {
                    $product = Product::factory()->create();
                }

                // Sometimes use secondary barcodes for variety
                $barcodes = array_filter([
                    $product->barcode,
                    $product->barcode_2,
                    $product->barcode_3,
                ]);

                return fake()->randomElement($barcodes);
            },
            'quantity' => fake()->numberBetween(1, 50),
            'action' => $action,
            'submitted' => $submitted ? 1 : 0,
            'submitted_at' => $submittedAt,
            'sync_status' => $syncStatus,
            'user_id' => function () {
                // Try to get an existing user, or create one if none exist
                return User::inRandomOrder()->first()?->id
                    ?? User::factory()->create()->id;
            },
            'created_at' => $createdAt,
            'updated_at' => $submittedAt ?? $createdAt,
        ];
    }
}
