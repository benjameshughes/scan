<?php

namespace App\Tables\Actions;

class ImportAction extends CustomAction
{
    protected string $label = 'Import';

    protected ?string $icon = 'upload';

    protected ?string $color = 'purple';

    public function __construct(?string $label = null)
    {
        parent::__construct($label ?? $this->label);
        $this->requiresPermission = false; // Import typically doesn't relate to specific records
    }

    public function route(string $route): self
    {
        $this->urlCallback = function ($record) use ($route) {
            return route($route);
        };

        return $this;
    }

    public function modal(string $livewireMethod = 'showImportModal'): self
    {
        return $this->livewire($livewireMethod);
    }
}
