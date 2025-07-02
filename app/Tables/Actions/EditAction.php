<?php

namespace App\Tables\Actions;

class EditAction extends BaseAction
{
    protected string $label = 'Edit';
    protected ?string $icon = 'pencil';
    protected ?string $color = 'green';
    protected ?string $type = 'edit';
    protected ?string $route = null;

    public function route(string $route): self
    {
        $this->route = $route;
        return $this;
    }

    public function getUrl($record): ?string
    {
        if ($this->route) {
            return route($this->route, $record);
        }

        // Try to guess the route name
        $modelName = strtolower(class_basename($record));
        $routeName = "{$modelName}s.edit";

        if (\Route::has($routeName)) {
            return route($routeName, $record);
        }

        return null;
    }

    protected function hasDefaultPermission($user, $record): bool
    {
        return $user->can('update', $record);
    }
}