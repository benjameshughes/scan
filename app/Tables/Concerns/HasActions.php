<?php

namespace App\Tables\Concerns;

use Filament\Actions\Action;

trait HasActions {

    public function mountAction(string $name)
    {
        $action = collect($this->getActions())
            ->first(fn (Action $action) => $action->getName() === $name);

        if (!$action) {
            return [];
        }

        $action->livewire($this);

        return $action;
    }

}