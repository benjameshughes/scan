<?php

namespace App\Tables\Columns;

use App\Tables\Columns\Column;

class BadgeColumn extends Column
{

    protected string $color = 'bg-green-100 text-green-800';
    protected array $colors = [
        'bg-green-100 text-green-800',
        'bg-red-100 text-red-800',
        'bg-yellow-100 text-yellow-800',
        'bg-blue-100 text-blue-800',
        'bg-indigo-100 text-indigo-800',
        'bg-purple-100 text-purple-800',
        'bg-pink-100 text-pink-800',
    ];

    public function color(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    public function render($row)
    {
        $value = $row->$this->name;
        if ($value) {
            return view('components.tables.table-badge', [
                'value' => $value,
                'color' => $this->color,
                'colors' => $this->colors,
            ]);
        }
    }
}