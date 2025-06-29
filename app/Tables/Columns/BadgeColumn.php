<?php

namespace App\Tables\Columns;

class BadgeColumn extends TextColumn
{
    protected string $defaultColor = 'gray';

    protected array $colorMap = [];

    protected array $predefinedColors = [
        'green' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'indigo' => 'bg-indigo-100 text-indigo-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'pink' => 'bg-pink-100 text-pink-800',
        'gray' => 'bg-gray-100 text-gray-800',
    ];

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->searchable = false; // Badges usually aren't searchable
    }

    public function color(string $color): self
    {
        $this->defaultColor = $color;

        return $this;
    }

    public function colors(array $colorMap): self
    {
        $this->colorMap = $colorMap;

        return $this;
    }

    public function getValue($record)
    {
        $value = parent::getValue($record);

        if ($value) {
            $color = $this->getColorForValue($value);
            $colorClass = $this->predefinedColors[$color] ?? $color;

            return view('components.tables.table-badge', [
                'value' => $value,
                'color' => $colorClass,
            ])->render();
        }

        return $value;
    }

    protected function getColorForValue($value): string
    {
        return $this->colorMap[$value] ?? $this->defaultColor;
    }
}
