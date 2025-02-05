<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'sku' => $this->faker->numberBetween(001,999) . '-' . $this->faker->numberBetween(001,999),
            'barcode' => $this->faker->ean13(),
            'quantity' => $this->faker->numberBetween(1, 100),
        ];
    }
}
