<?php

namespace App\Tables\Actions;

abstract class BaseAction
{
    protected string $label;

    protected ?string $icon = null;

    protected ?string $color = null;

    protected ?string $type = null;

    protected bool $requiresPermission = true;

    protected ?string $permission = null;

    protected ?string $confirmMessage = null;

    protected array $attributes = [];

    protected ?string $size = null;

    protected ?string $class = null;

    protected ?string $actionMethod = null;

    public function __construct(?string $label = null)
    {
        if ($label) {
            $this->label = $label;
        }
    }

    abstract public function getUrl($record): ?string;

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function confirm(string $message): self
    {
        $this->confirmMessage = $message;

        return $this;
    }

    public function permission(string $permission): self
    {
        $this->permission = $permission;

        return $this;
    }

    public function noPermissionCheck(): self
    {
        $this->requiresPermission = false;

        return $this;
    }

    public function attributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    public function size(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function class(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function action(string $method): self
    {
        $this->actionMethod = $method;

        return $this;
    }

    public function canExecute($record): bool
    {
        if (! $this->requiresPermission) {
            return true;
        }

        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($this->permission) {
            return $user->can($this->permission, $record);
        }

        // Default permission check based on action type
        return $this->hasDefaultPermission($user, $record);
    }

    abstract protected function hasDefaultPermission($user, $record): bool;

    public function toArray($record): array
    {
        return [
            'label' => $this->label,
            'url' => $this->getUrl($record),
            'icon' => $this->icon,
            'color' => $this->color,
            'type' => $this->type,
            'confirm' => $this->confirmMessage,
            'attributes' => $this->attributes,
            'size' => $this->size,
            'class' => $this->class,
            'action_method' => $this->actionMethod,
        ];
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}
