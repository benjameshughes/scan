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
        $productTypes = [
            'Premium', 'Standard', 'Basic', 'Deluxe', 'Pro', 'Elite', 'Classic', 'Modern', 'Vintage', 'Essential',
        ];

        $productCategories = [
            'Widget', 'Gadget', 'Tool', 'Device', 'Component', 'Accessory', 'Module', 'Unit', 'Part', 'Kit',
        ];

        $colors = [
            'Red', 'Blue', 'Green', 'Black', 'White', 'Silver', 'Gold', 'Orange', 'Purple', 'Yellow',
        ];

        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

        $type = $this->faker->randomElement($productTypes);
        $category = $this->faker->randomElement($productCategories);
        $color = $this->faker->optional(0.7)->randomElement($colors);
        $size = $this->faker->optional(0.3)->randomElement($sizes);

        // Create realistic product name
        $name = $type.' '.$category;
        if ($color) {
            $name .= ' - '.$color;
        }
        if ($size) {
            $name .= ' ('.$size.')';
        }

        // Generate barcode with your prefix
        $baseBarcode = '505903'.$this->faker->numerify('#######');

        return [
            'name' => $name,
            'sku' => strtoupper($this->faker->bothify('??##-###')),
            'barcode' => $baseBarcode,
            'barcode_2' => $this->faker->optional(0.4)->numerify('505903#######'), // 40% chance of second barcode
            'barcode_3' => $this->faker->optional(0.2)->numerify('505903#######'), // 20% chance of third barcode
            'quantity' => $this->faker->numberBetween(0, 500),
        ];
    }
}
