<?php

namespace App\Tables\Columns;

use Carbon\Carbon;

class TextColumn extends Column {

    public function dateForHumans(): static
    {
        $this->renderCallback = fn($row) => Carbon::parse($row->{$this->name})->diffForHumans();
        return $this;
    }

    /**
     * Display the value using the given callback.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function value(callable $callback)
    {
        $this->renderCallback = $callback;
        return $this;
    }
}