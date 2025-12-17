<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum VibrationPattern: string
{
    case OFF = 'off';
    case SHORT = 'short';
    case MEDIUM = 'medium';
    case LONG = 'long';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::OFF => 'Off',
            self::SHORT => 'Short',
            self::MEDIUM => 'Medium',
            self::LONG => 'Long',
        };
    }

    /**
     * Get pattern description
     */
    public function description(): string
    {
        return match ($this) {
            self::OFF => 'No vibration',
            self::SHORT => 'Quick single pulse',
            self::MEDIUM => 'Double pulse pattern',
            self::LONG => 'Triple pulse pattern',
        };
    }

    /**
     * Get vibration pattern array for JavaScript
     */
    public function pattern(): array
    {
        return match ($this) {
            self::OFF => [],
            self::SHORT => [100],
            self::MEDIUM => [100, 50, 200],
            self::LONG => [150, 80, 150, 80, 300],
        };
    }

    /**
     * Get icon for pattern
     */
    public function icon(): string
    {
        return match ($this) {
            self::OFF => '⚫',
            self::SHORT => '📳',
            self::MEDIUM => '📳📳',
            self::LONG => '📳📳📳',
        };
    }

    /**
     * Get CSS classes for pattern representation
     */
    public function cssClasses(): string
    {
        return match ($this) {
            self::OFF => 'bg-gray-300 dark:bg-gray-600',
            self::SHORT => 'bg-green-500',
            self::MEDIUM => 'bg-blue-500',
            self::LONG => 'bg-purple-500',
        };
    }

    /**
     * Check if pattern is enabled (not off)
     */
    public function isEnabled(): bool
    {
        return $this !== self::OFF;
    }

    /**
     * Get all patterns as collection
     */
    public static function collection(): Collection
    {
        return collect(self::cases());
    }

    /**
     * Get options for forms (value => label)
     */
    public static function options(): Collection
    {
        return self::collection()->mapWithKeys(fn ($pattern) => [
            $pattern->value => $pattern->label(),
        ]);
    }

    /**
     * Get pattern by value with fallback
     */
    public static function fromValue(string $value): self
    {
        return self::tryFrom($value) ?? self::MEDIUM;
    }
}
