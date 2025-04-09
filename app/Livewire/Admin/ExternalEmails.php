<?php

namespace App\Livewire\Admin;

use App\Models\ExternalEmail;
use Flux\Flux;
use Illuminate\Foundation\Auth\User;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Collection;

class ExternalEmails extends Component
{

    public Collection $externalEmails;

    #[Validate('required|max:255')]
    public string $name;
    #[Validate('required|max:255')]
    public string $email;

    public function save()
    {
        $this->validate();

        $externalEmail = ExternalEmail::create([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $externalEmail->save();

        $this->mount();

        Flux::modal('addEmail')->close();
    }

    public function delete(ExternalEmail $externalEmail)
    {
        // Check a user had admin role first
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $externalEmail->delete();
        }

    }

    public function edit($id)
    {
        // Get the user from the id
        $user = User::findOrFail($id);

        // Load the modal
        $this->modal('editUser')->show();
    }

    public function mount()
    {
        $this->externalEmails = ExternalEmail::all();
    }

    public function render()
    {
        return view('livewire.admin.external-emails');
    }
}
