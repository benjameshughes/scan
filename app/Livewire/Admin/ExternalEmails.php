<?php

namespace App\Livewire\Admin;

use App\Models\ExternalEmail;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ExternalEmails extends Component
{
    public Collection $externalEmails;

    public ExternalEmail $user;

    #[Validate('required|max:255')]
    public string $name;

    #[Validate('required|max:255|unique:external_emails,email')]
    public string $email;

    public Collection $selectedItems;
    public bool $selectAll = false;

    public bool $isAdmin = false;

    public function save()
    {
        $this->validate();

        $externalEmail = ExternalEmail::updateOrCreate(
            ['email' => $this->email],
            ['name' => $this->name, 'email' => $this->email]
        );

        $externalEmail->save();

        $this->reset(['name', 'email']);

        $this->mount();

        Flux::modals()->close();
        $this->dispatch('user-updated');
    }

    public function delete(ExternalEmail $externalEmail)
    {
        if (! $this->isAdmin) {
            abort(403);
        }

        $externalEmail = ExternalEmail::findOrFail($externalEmail->id);
        $externalEmail->delete();

        $this->mount();
    }

    public function bulkDelete()
    {
        if(!$this->isAdmin)
        {
            abort(403);
        }

        if(empty($this->selectedItems))
        {
            $this->dispatch('nothing-selected');
            return;
        }

        ExternalEmail::whereIn('id', $this->selectedItems)->delete();
        $this->reset(['selectedItems', 'selectAll']);
        $this->mount();
        $this->dispatch('selected-deleted');
    }

    public function updatedSelectedItems()
    {
        $this->selectAll = count($this->selectedItems) === $this->externalEmails->count();
    }

    public function updatedSelectAll()
    {
        if($this->selectAll)
        {
            $this->selectedItems = $this->externalEmails->pluck('id')->map(fn ($item) => (string) $item);
        } else {
            $this->selectedItems = collect();
        }
    }

    public function edit($id)
    {
        if (! $this->isAdmin) {
            abort(403);
        }

        // Get the user from the id
        $this->user = ExternalEmail::findOrFail($id);

        $this->name = $this->user->name;
        $this->email = $this->user->email;
    }

    public function mount()
    {
        // Check is the logged-in user has admin role
        $this->isAdmin = (bool) auth()->user()->hasRole('admin');
        $this->externalEmails = ExternalEmail::all();
        $this->selectedItems = collect();
    }

    public function render()
    {
        return view('livewire.admin.external-emails');
    }
}
