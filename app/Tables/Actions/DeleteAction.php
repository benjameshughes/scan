<?php

namespace App\Tables\Actions;

class DeleteAction extends BaseAction
{
    protected string $label = 'Delete';
    protected ?string $icon = 'trash';
    protected ?string $color = 'red';
    protected ?string $type = 'delete';
    protected ?string $action = null;
    protected ?string $confirmMessage = 'Are you sure you want to delete this item?';

    public function action(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getUrl($record): ?string
    {
        // Delete actions don't use URLs, they use wire:click actions
        return null;
    }

    public function getAction($record): string
    {
        if ($this->action) {
            return $this->action;
        }

        return 'delete('.$record->id.')';
    }

    public function toArray($record): array
    {
        $array = parent::toArray($record);
        $array['action'] = $this->getAction($record);
        unset($array['url']); // Delete actions don't use URLs
        return $array;
    }

    protected function hasDefaultPermission($user, $record): bool
    {
        return $user->can('delete', $record);
    }
}