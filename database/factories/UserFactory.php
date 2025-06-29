<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isVerified = fake()->boolean(0.85); // 85% chance of being verified
        $isActive = fake()->boolean(0.95); // 95% chance of being active

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => $isVerified ? fake()->dateTimeBetween('-1 year', 'now') : null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => $isActive ? 'active' : 'inactive',
            'settings' => json_encode([
                'notifications' => fake()->boolean(),
                'dark_mode' => fake()->boolean(),
                'auto_submit' => fake()->boolean(),
                'scan_sound' => fake()->boolean(),
            ]),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
