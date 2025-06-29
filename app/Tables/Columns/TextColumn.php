<?php

namespace App\Tables\Columns;

class TextColumn
{
    protected string $name;

    protected string $label;

    protected bool $sortable = false;

    protected bool $searchable = true;

    protected ?\Closure $searchCallback = null;

    protected ?\Closure $valueCallback = null;

    protected bool $dateForHumans = false;

    protected ?\Closure $urlCallback = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucfirst(str_replace('_', ' ', $name));
    }

    public static function make(string $name): self
    {
        return new static($name);
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function sortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function searchable(bool|\Closure $searchable = true): self
    {
        if ($searchable instanceof \Closure) {
            $this->searchable = true;
            $this->searchCallback = $searchable;
        } else {
            $this->searchable = $searchable;
        }

        return $this;
    }

    public function value(\Closure $callback): self
    {
        $this->valueCallback = $callback;

        return $this;
    }

    public function dateForHumans(bool $dateForHumans = true): self
    {
        $this->dateForHumans = $dateForHumans;

        return $this;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getSearchCallback(): ?\Closure
    {
        return $this->searchCallback;
    }

    public function getValue($record)
    {
        if ($this->valueCallback) {
            return call_user_func($this->valueCallback, $record);
        }

        $value = data_get($record, $this->name);

        if ($this->dateForHumans && $value) {
            return $value->diffForHumans();
        }

        return $value;
    }

    public function url(\Closure $callback): self
    {
        $this->urlCallback = $callback;

        return $this;
    }

    public function getUrl($record): ?string
    {
        if ($this->urlCallback) {
            return call_user_func($this->urlCallback, $record);
        }

        return null;
    }
}
