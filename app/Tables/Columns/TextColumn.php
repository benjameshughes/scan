<?php

namespace App\Tables\Columns;

use Carbon\Carbon;

class TextColumn extends Column {

    public function dateForHumans(): static
    {
        $this->renderCallback = fn($row) => Carbon::parse($row->{$this->name})->diffForHumans();
        return $this;
    }
}