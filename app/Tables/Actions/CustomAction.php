<?php

namespace App\Tables\Actions;

class CustomAction extends BaseAction
{
    protected string $type = 'custom';
    protected $urlCallback;
    protected ?string $livewireMethod = null;
    protected ?string $jsCallback = null;

    public function __construct(string $label, $urlCallback = null)
    {
        parent::__construct($label);
        $this->urlCallback = $urlCallback;
    }

    public function livewire(string $method): self
    {
        $this->livewireMethod = $method;
        $this->type = 'livewire';
        return $this;
    }

    public function javascript(string $callback): self
    {
        $this->jsCallback = $callback;
        $this->type = 'javascript';
        return $this;
    }

    public function getUrl($record): ?string
    {
        if ($this->livewireMethod) {
            // Return a javascript call to Livewire method
            return "javascript:Livewire.find('{component_id}').call('{$this->livewireMethod}', {$record->id})";
        }

        if ($this->jsCallback) {
            return "javascript:{$this->jsCallback}({$record->id})";
        }

        if (is_callable($this->urlCallback)) {
            return call_user_func($this->urlCallback, $record);
        }

        return $this->urlCallback;
    }

    public function toArray($record): array
    {
        $array = parent::toArray($record);
        
        if ($this->livewireMethod) {
            $array['livewire_method'] = $this->livewireMethod;
        }

        return $array;
    }

    protected function hasDefaultPermission($user, $record): bool
    {
        // Custom actions require explicit permission setup
        return true;
    }
}