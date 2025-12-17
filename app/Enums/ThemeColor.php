<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Collection;

enum ThemeColor: string
{
    // Classic Blues
    case Blue = 'blue';
    case Sky = 'sky';
    case Cyan = 'cyan';
    case Indigo = 'indigo';
    case Navy = 'navy';

    // Nature Greens
    case Green = 'green';
    case Emerald = 'emerald';
    case Teal = 'teal';
    case Lime = 'lime';
    case Forest = 'forest';

    // Warm Colors
    case Red = 'red';
    case Orange = 'orange';
    case Amber = 'amber';
    case Yellow = 'yellow';
    case Coral = 'coral';

    // Cool Purples & Pinks
    case Purple = 'purple';
    case Violet = 'violet';
    case Pink = 'pink';
    case Rose = 'rose';
    case Fuchsia = 'fuchsia';

    // Neutrals & Earthy
    case Slate = 'slate';
    case Gray = 'gray';
    case Stone = 'stone';
    case Zinc = 'zinc';
    case Neutral = 'neutral';

    public function label(): string
    {
        return match ($this) {
            // Classic Blues
            self::Blue => 'Ocean Blue',
            self::Sky => 'Sky Blue',
            self::Cyan => 'Bright Cyan',
            self::Indigo => 'Deep Indigo',
            self::Navy => 'Navy Blue',

            // Nature Greens
            self::Green => 'Fresh Green',
            self::Emerald => 'Emerald',
            self::Teal => 'Teal',
            self::Lime => 'Electric Lime',
            self::Forest => 'Forest Green',

            // Warm Colors
            self::Red => 'Cherry Red',
            self::Orange => 'Sunset Orange',
            self::Amber => 'Golden Amber',
            self::Yellow => 'Bright Yellow',
            self::Coral => 'Coral',

            // Cool Purples & Pinks
            self::Purple => 'Royal Purple',
            self::Violet => 'Soft Violet',
            self::Pink => 'Hot Pink',
            self::Rose => 'Rose Gold',
            self::Fuchsia => 'Electric Fuchsia',

            // Neutrals & Earthy
            self::Slate => 'Cool Slate',
            self::Gray => 'Classic Gray',
            self::Stone => 'Warm Stone',
            self::Zinc => 'Modern Zinc',
            self::Neutral => 'Neutral Beige',
        };
    }

    public function description(): string
    {
        return match ($this) {
            // Classic Blues
            self::Blue => 'Professional and trustworthy',
            self::Sky => 'Light and airy feel',
            self::Cyan => 'Modern and energetic',
            self::Indigo => 'Deep and sophisticated',
            self::Navy => 'Classic and authoritative',

            // Nature Greens
            self::Green => 'Fresh and natural',
            self::Emerald => 'Luxurious and calming',
            self::Teal => 'Modern and balanced',
            self::Lime => 'Vibrant and energetic',
            self::Forest => 'Earthy and grounded',

            // Warm Colors
            self::Red => 'Bold and passionate',
            self::Orange => 'Energetic and creative',
            self::Amber => 'Warm and inviting',
            self::Yellow => 'Bright and optimistic',
            self::Coral => 'Friendly and approachable',

            // Cool Purples & Pinks
            self::Purple => 'Creative and regal',
            self::Violet => 'Gentle and dreamy',
            self::Pink => 'Playful and bold',
            self::Rose => 'Elegant and refined',
            self::Fuchsia => 'Electric and modern',

            // Neutrals & Earthy
            self::Slate => 'Cool and professional',
            self::Gray => 'Timeless and versatile',
            self::Stone => 'Warm and natural',
            self::Zinc => 'Modern and minimalist',
            self::Neutral => 'Soft and understated',
        };
    }

    public function primaryClass(): string
    {
        return match ($this) {
            // Classic Blues
            self::Blue => 'text-blue-600 dark:text-blue-400',
            self::Sky => 'text-sky-600 dark:text-sky-400',
            self::Cyan => 'text-cyan-600 dark:text-cyan-400',
            self::Indigo => 'text-indigo-600 dark:text-indigo-400',
            self::Navy => 'text-blue-800 dark:text-blue-300',

            // Nature Greens
            self::Green => 'text-green-600 dark:text-green-400',
            self::Emerald => 'text-emerald-600 dark:text-emerald-400',
            self::Teal => 'text-teal-600 dark:text-teal-400',
            self::Lime => 'text-lime-600 dark:text-lime-400',
            self::Forest => 'text-green-800 dark:text-green-300',

            // Warm Colors
            self::Red => 'text-red-600 dark:text-red-400',
            self::Orange => 'text-orange-600 dark:text-orange-400',
            self::Amber => 'text-amber-600 dark:text-amber-400',
            self::Yellow => 'text-yellow-600 dark:text-yellow-400',
            self::Coral => 'text-orange-500 dark:text-orange-300',

            // Cool Purples & Pinks
            self::Purple => 'text-purple-600 dark:text-purple-400',
            self::Violet => 'text-violet-600 dark:text-violet-400',
            self::Pink => 'text-pink-600 dark:text-pink-400',
            self::Rose => 'text-rose-600 dark:text-rose-400',
            self::Fuchsia => 'text-fuchsia-600 dark:text-fuchsia-400',

            // Neutrals & Earthy
            self::Slate => 'text-slate-600 dark:text-slate-400',
            self::Gray => 'text-gray-600 dark:text-gray-400',
            self::Stone => 'text-stone-600 dark:text-stone-400',
            self::Zinc => 'text-zinc-600 dark:text-zinc-400',
            self::Neutral => 'text-neutral-600 dark:text-neutral-400',
        };
    }

    public function backgroundClass(): string
    {
        return match ($this) {
            // Classic Blues
            self::Blue => 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600',
            self::Sky => 'bg-sky-600 hover:bg-sky-700 dark:bg-sky-700 dark:hover:bg-sky-600',
            self::Cyan => 'bg-cyan-600 hover:bg-cyan-700 dark:bg-cyan-700 dark:hover:bg-cyan-600',
            self::Indigo => 'bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600',
            self::Navy => 'bg-blue-800 hover:bg-blue-900 dark:bg-blue-800 dark:hover:bg-blue-700',

            // Nature Greens
            self::Green => 'bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600',
            self::Emerald => 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600',
            self::Teal => 'bg-teal-600 hover:bg-teal-700 dark:bg-teal-700 dark:hover:bg-teal-600',
            self::Lime => 'bg-lime-600 hover:bg-lime-700 dark:bg-lime-700 dark:hover:bg-lime-600',
            self::Forest => 'bg-green-800 hover:bg-green-900 dark:bg-green-800 dark:hover:bg-green-700',

            // Warm Colors
            self::Red => 'bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600',
            self::Orange => 'bg-orange-600 hover:bg-orange-700 dark:bg-orange-700 dark:hover:bg-orange-600',
            self::Amber => 'bg-amber-600 hover:bg-amber-700 dark:bg-amber-700 dark:hover:bg-amber-600',
            self::Yellow => 'bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-700 dark:hover:bg-yellow-600',
            self::Coral => 'bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-500',

            // Cool Purples & Pinks
            self::Purple => 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600',
            self::Violet => 'bg-violet-600 hover:bg-violet-700 dark:bg-violet-700 dark:hover:bg-violet-600',
            self::Pink => 'bg-pink-600 hover:bg-pink-700 dark:bg-pink-700 dark:hover:bg-pink-600',
            self::Rose => 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-600',
            self::Fuchsia => 'bg-fuchsia-600 hover:bg-fuchsia-700 dark:bg-fuchsia-700 dark:hover:bg-fuchsia-600',

            // Neutrals & Earthy
            self::Slate => 'bg-slate-600 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600',
            self::Gray => 'bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600',
            self::Stone => 'bg-stone-600 hover:bg-stone-700 dark:bg-stone-700 dark:hover:bg-stone-600',
            self::Zinc => 'bg-zinc-600 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600',
            self::Neutral => 'bg-neutral-600 hover:bg-neutral-700 dark:bg-neutral-700 dark:hover:bg-neutral-600',
        };
    }

    /**
     * Get all theme colors as a Collection
     */
    public static function collection(): Collection
    {
        return collect(self::cases());
    }

    /**
     * Get theme colors as a Collection of arrays for form options
     */
    public static function options(): Collection
    {
        return self::collection()->map(fn (self $color) => [
            'value' => $color->value,
            'label' => $color->label(),
            'description' => $color->description(),
            'primary_class' => $color->primaryClass(),
            'background_class' => $color->backgroundClass(),
        ]);
    }

    /**
     * Get only the values as a Collection
     */
    public static function values(): Collection
    {
        return self::collection()->map(fn (self $color) => $color->value);
    }

    /**
     * Create from string value with validation
     */
    public static function fromString(string $value): ?self
    {
        return self::collection()
            ->first(fn (self $color) => $color->value === $value);
    }

    /**
     * Check if a value is valid
     */
    public static function isValid(string $value): bool
    {
        return self::values()->contains($value);
    }
}
