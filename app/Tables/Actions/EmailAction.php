<?php

namespace App\Tables\Actions;

class EmailAction extends CustomAction
{
    protected string $label = 'Send Email';
    protected string $icon = 'mail';
    protected string $color = 'blue';

    public function __construct(string $label = null)
    {
        parent::__construct($label ?? $this->label);
    }

    public function mailto(string $field = 'email'): self
    {
        $this->urlCallback = function ($record) use ($field) {
            $email = data_get($record, $field);
            return $email ? "mailto:{$email}" : null;
        };
        return $this;
    }

    public function compose(string $livewireMethod = 'composeEmail'): self
    {
        return $this->livewire($livewireMethod);
    }
}