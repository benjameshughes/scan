<?php

namespace App\Tables\Actions;

class ResetPasswordAction extends CustomAction
{
    protected string $label = 'Reset Password';
    protected string $icon = 'key';
    protected string $color = 'amber';
    protected string $confirmMessage = 'Are you sure you want to reset this user\'s password?';

    public function __construct(string $label = null)
    {
        parent::__construct($label ?? $this->label);
        $this->livewire('resetPassword');
    }

    protected function hasDefaultPermission($user, $record): bool
    {
        return $user->can('update', $record);
    }
}