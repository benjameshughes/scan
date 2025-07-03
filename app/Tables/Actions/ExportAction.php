<?php

namespace App\Tables\Actions;

class ExportAction extends CustomAction
{
    protected string $label = 'Export';

    protected ?string $icon = 'download';

    protected ?string $color = 'indigo';

    public function __construct(?string $label = null)
    {
        parent::__construct($label ?? $this->label);
        $this->requiresPermission = false; // Export typically doesn't relate to specific records
    }

    public function route(string $route): self
    {
        $this->urlCallback = function ($record) use ($route) {
            return route($route, $record);
        };

        return $this;
    }

    public function download(string $livewireMethod = 'export'): self
    {
        return $this->livewire($livewireMethod);
    }

    public function format(string $format): self
    {
        $this->label = "Export {$format}";

        return $this;
    }
}
