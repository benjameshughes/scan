<?php

namespace App\Tables\Columns;

class ActionsColumn extends TextColumn
{
    protected array $actions = [];

    protected bool $showView = false;

    protected bool $showEdit = false;

    protected bool $showDelete = false;

    protected ?string $viewRoute = null;

    protected ?string $editRoute = null;

    protected ?string $deleteAction = null;

    public function __construct(string $name = 'actions')
    {
        parent::__construct($name);
        $this->searchable = false;
        $this->sortable = false;
        $this->label = 'Actions';
    }

    public function view(?string $route = null): self
    {
        $this->showView = true;
        $this->viewRoute = $route;

        return $this;
    }

    public function edit(?string $route = null): self
    {
        $this->showEdit = true;
        $this->editRoute = $route;

        return $this;
    }

    public function delete(?string $action = null): self
    {
        $this->showDelete = true;
        $this->deleteAction = $action;

        return $this;
    }

    public function custom(string $label, \Closure $url, ?string $icon = null, string $color = 'blue'): self
    {
        $this->actions[] = [
            'label' => $label,
            'url' => $url,
            'icon' => $icon,
            'color' => $color,
            'type' => 'custom',
        ];

        return $this;
    }

    public function getValue($record)
    {
        $actions = [];
        $user = auth()->user();

        if ($this->showView && $user && $user->can('view', $record)) {
            $actions[] = [
                'label' => 'View',
                'url' => $this->getRouteUrl($this->viewRoute, $record, 'view'),
                'icon' => 'eye',
                'color' => 'blue',
                'type' => 'view',
            ];
        }

        if ($this->showEdit && $user && $user->can('update', $record)) {
            $actions[] = [
                'label' => 'Edit',
                'url' => $this->getRouteUrl($this->editRoute, $record, 'edit'),
                'icon' => 'pencil',
                'color' => 'green',
                'type' => 'edit',
            ];
        }

        if ($this->showDelete && $user && $user->can('delete', $record)) {
            $actions[] = [
                'label' => 'Delete',
                'action' => $this->getDeleteAction($record),
                'icon' => 'trash',
                'color' => 'red',
                'type' => 'delete',
            ];
        }

        // Add custom actions
        foreach ($this->actions as $action) {
            $actions[] = [
                'label' => $action['label'],
                'url' => call_user_func($action['url'], $record),
                'icon' => $action['icon'],
                'color' => $action['color'],
                'type' => $action['type'],
            ];
        }

        return view('components.tables.table-actions', [
            'actions' => $actions,
            'record' => $record,
        ])->render();
    }

    protected function getRouteUrl(?string $route, $record, string $default): ?string
    {
        if ($route) {
            return route($route, $record);
        }

        // Try to guess the route name
        $modelName = strtolower(class_basename($record));
        $routeName = "{$modelName}s.{$default}";

        if (\Route::has($routeName)) {
            return route($routeName, $record);
        }

        return null;
    }

    protected function getDeleteAction($record): string
    {
        if ($this->deleteAction) {
            return $this->deleteAction;
        }

        return 'delete('.$record->id.')';
    }
}
