<?php

namespace App\Tables\Concerns;

use App\Tables\Columns\Column;

trait HasColumns {

    protected function getColumns(): array
    {
        return $this->columns();
    }

    protected function parseColumns(array $columns): array
    {
        $columns = $this->getColumns();

        return collect($columns)->map(function($column) {
            return $column instanceof Column ? $column : Column::make($column);
        })->toArray();
    }

}