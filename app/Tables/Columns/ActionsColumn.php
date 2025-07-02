<?php

namespace App\Tables\Columns;

use App\Tables\Actions\BaseAction;
use App\Tables\Actions\ViewAction;
use App\Tables\Actions\EditAction;
use App\Tables\Actions\DeleteAction;
use App\Tables\Actions\CustomAction;

class ActionsColumn extends TextColumn
{
    protected array $actions = [];
    protected ?string $componentId = null;

    public function __construct(string $name = 'actions')
    {
        parent::__construct($name);
        $this->searchable = false;
        $this->sortable = false;
        $this->label = 'Actions';
    }

    public function setComponentId(string $componentId): self
    {
        $this->componentId = $componentId;
        return $this;
    }

    public function view(?string $route = null): self
    {
        $action = new ViewAction();
        if ($route) {
            $action->route($route);
        }
        $this->actions[] = $action;
        return $this;
    }

    public function edit(?string $route = null): self
    {
        $action = new EditAction();
        if ($route) {
            $action->route($route);
        }
        $this->actions[] = $action;
        return $this;
    }

    public function delete(?string $action = null): self
    {
        $deleteAction = new DeleteAction();
        if ($action) {
            $deleteAction->action($action);
        }
        $this->actions[] = $deleteAction;
        return $this;
    }

    public function custom(string $label, $url = null, ?string $icon = null, string $color = 'blue'): self
    {
        $action = new CustomAction($label, $url);
        if ($icon) {
            $action->icon($icon);
        }
        $action->color($color);
        $this->actions[] = $action;
        return $this;
    }

    public function action(BaseAction $action): self
    {
        $this->actions[] = $action;
        return $this;
    }

    public function actions(array $actions): self
    {
        foreach ($actions as $key => $config) {
            $this->addActionFromConfig($key, $config);
        }
        return $this;
    }

    protected function addActionFromConfig(string $key, array $config): void
    {
        // Handle dynamic labels (closures)
        $label = $config['label'] ?? ucfirst($key);
        if ($label instanceof \Closure) {
            // Store the closure to be evaluated later with the record
            $labelCallback = $label;
            $label = ucfirst($key); // Fallback for constructor
        } else {
            $labelCallback = null;
        }
        
        $action = new CustomAction($label, null);
        
        // Store the dynamic label callback if provided
        if ($labelCallback) {
            $action->dynamicLabel($labelCallback);
        }
        
        // Handle dynamic icons (closures)
        if (isset($config['icon'])) {
            if ($config['icon'] instanceof \Closure) {
                $action->dynamicIcon($config['icon']);
            } else {
                $action->icon($config['icon']);
            }
        }
        
        if (isset($config['variant'])) {
            $action->color($config['variant']);
        }
        
        if (isset($config['size'])) {
            $action->size($config['size']);
        }
        
        if (isset($config['class'])) {
            $action->class($config['class']);
        }
        
        if (isset($config['confirm'])) {
            $action->confirm($config['confirm']);
        }
        
        // Set the action method for secure Livewire calls
        $action->livewire($key);
        
        $this->actions[] = $action;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    // Convenience methods for common actions
    public function email(string $field = 'email', string $label = 'Send Email'): self
    {
        $action = new \App\Tables\Actions\EmailAction($label);
        $action->mailto($field);
        $this->actions[] = $action;
        return $this;
    }

    public function resetPassword(string $label = 'Reset Password'): self
    {
        $this->actions[] = new \App\Tables\Actions\ResetPasswordAction($label);
        return $this;
    }

    public function export(string $format = null, string $route = null): self
    {
        $action = new \App\Tables\Actions\ExportAction();
        if ($format) {
            $action->format($format);
        }
        if ($route) {
            $action->route($route);
        }
        $this->actions[] = $action;
        return $this;
    }

    public function import(string $route = null): self
    {
        $action = new \App\Tables\Actions\ImportAction();
        if ($route) {
            $action->route($route);
        }
        $this->actions[] = $action;
        return $this;
    }

    public function getValue($record)
    {
        $actionData = [];

        foreach ($this->actions as $action) {
            if ($action->canExecute($record)) {
                $data = $action->toArray($record);
                
                // Handle Livewire component ID replacement for JavaScript URLs
                if (isset($data['url']) && str_contains($data['url'], '{component_id}') && $this->componentId) {
                    $data['url'] = str_replace('{component_id}', $this->componentId, $data['url']);
                }
                
                $actionData[] = $data;
            }
        }

        return view('components.tables.table-actions', [
            'actions' => $actionData,
            'record' => $record,
        ])->render();
    }
}
