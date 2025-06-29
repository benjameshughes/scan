<?php

namespace App\Enums;

enum ImportTypes
{
    case CREATE;
    case UPDATE;
    case DELETE;

    public function getLabel(): string
    {
        return match ($this) {
            self::CREATE => __('Create'),
            self::UPDATE => __('Update'),
            self::DELETE => __('Delete'),
        };
    }

    public static function toArray(): array
    {
        return array_map(fn (self $case) => [
            'name' => $case->name,
            'label' => $case->getLabel(),
        ], self::cases());
    }
}
