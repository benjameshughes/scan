<?php

namespace App\Tables\Concerns;

trait HasFilters {

    public function hasFilters(): bool
    {
        return method_exists($this, 'hasFilters') && !empty($this->hasFilters());
    }

    public function applyFilters($query): void
    {
        if(empty($this->filters))
        {
            return $query;
        }

        $filters = $this->getTable()->getFilters();

        return $query->where(function ($query) use ($filters) {
            foreach ($filters as $filter) {
                $this->filter($filter, $option)
           }
        });


    }

}