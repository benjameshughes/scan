<?php

namespace App\Tables\Concerns;

trait HasFilters
{
    public array $filters = [];

    public function getFilters(): array
    {
        return $this->filters;
    }
}
