<?php

namespace App\Tables\Columns;

class Column
{
    public string $name;

    public string $label;

    protected $renderCallback = null;

    protected bool $sortable = false;

    protected string $alignment = 'left';

    protected string $sortDirection = 'asc';

    protected string $url;

    public static function make(string $name)
    {
        return new static($name);
    }

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucwords(str_replace('_', ' ', $name));
    }

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function sortable(): static
    {
        $this->sortable = true;

        return $this;
    }

    public function sort(string $direction = 'asc'): static
    {
        $this->sortDirection = $direction;

        return $this;
    }

    public function alignLeft(): static
    {
        $this->alignment = 'left';

        return $this;
    }

    public function alignCenter(): static
    {
        $this->alignment = 'center';

        return $this;
    }

    public function alignRight(): static
    {
        $this->alignment = 'right';

        return $this;
    }

    public function render($row)
    {
        if ($this->renderCallback) {
            return call_user_func($this->renderCallback, $row);
        }

        return $row->{$this->name};
    }

    /*
     * Getters
     */

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRenderCallback(): string
    {
        return $this->renderCallback;
    }

    public function getAlignment(): string
    {
        return $this->alignment;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }
}
