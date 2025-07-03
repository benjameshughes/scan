<?php

namespace App\Tables\Actions;

class CustomAction extends BaseAction
{
    protected ?string $type = 'custom';

    protected $urlCallback;

    protected ?string $livewireMethod = null;

    protected ?\Closure $actionCallback = null;

    protected ?\Closure $dynamicLabelCallback = null;

    protected ?\Closure $dynamicIconCallback = null;

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

    public function callback(\Closure $callback): self
    {
        $this->actionCallback = $callback;
        $this->type = 'callback';

        return $this;
    }

    public function dynamicLabel(\Closure $callback): self
    {
        $this->dynamicLabelCallback = $callback;

        return $this;
    }

    public function dynamicIcon(\Closure $callback): self
    {
        $this->dynamicIconCallback = $callback;

        return $this;
    }

    public function getUrl($record): ?string
    {
        // For Livewire actions and callbacks, we don't return URLs
        // The frontend will handle these securely using wire:click directives
        if ($this->livewireMethod || $this->actionMethod || $this->actionCallback) {
            return null;
        }

        // Only allow URL callbacks for legitimate links (external URLs, downloads, etc.)
        if (is_callable($this->urlCallback)) {
            return call_user_func($this->urlCallback, $record);
        }

        return $this->urlCallback;
    }

    public function toArray($record): array
    {
        $array = parent::toArray($record);

        // Handle dynamic labels
        if ($this->dynamicLabelCallback) {
            $array['label'] = call_user_func($this->dynamicLabelCallback, $record);
        }

        // Handle dynamic icons
        if ($this->dynamicIconCallback) {
            $array['icon'] = call_user_func($this->dynamicIconCallback, $record);
        }

        // Set Livewire method for secure action handling
        if ($this->livewireMethod) {
            $array['livewire_method'] = $this->livewireMethod;
            $array['type'] = 'livewire';
        } elseif ($this->actionMethod) {
            $array['livewire_method'] = $this->actionMethod;
            $array['type'] = 'livewire';
        } elseif ($this->actionCallback) {
            // For callbacks, we'll use a generic action method that executes the callback
            $array['livewire_method'] = 'executeCustomAction';
            $array['action_id'] = spl_object_hash($this); // Unique identifier for this action
            $array['type'] = 'callback';
        }

        return $array;
    }

    public function getActionCallback(): ?\Closure
    {
        return $this->actionCallback;
    }

    protected function hasDefaultPermission($user, $record): bool
    {
        // Custom actions require explicit permission setup
        return true;
    }
}
