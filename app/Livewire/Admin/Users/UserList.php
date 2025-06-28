<?php

namespace App\Livewire\Admin\Users;

use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteNotification;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{

    use WithPagination;

    public $search = '';
    public bool $isAdmin;
    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete(User $user)
    {
        if(!$this->isAdmin)
        {
            abort(403);
        }

        $user = User::find($user->id);
        $user->delete();
        $this->dispatch('user-deleted');
    }

    public function resendInvite(Invite $invitation)
    {
        $invitation = Invite::findOrFail($invitation);

        if(!$invitation->isAccepted() && $invitation->invited_by === auth()->user()->id())
        {
            $invitation->update(['expires_at' => now()->addDay()]);

            $invitation->notify(new InviteNotification($invitation));

            $this->dispatch('invite-resent');
        }

        $this->dispatch('invite-pending');
    }

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->isAdmin = auth()->user()->hasRole('admin');
    }

    public function render()
    {
        return view('livewire.admin.users.user-list', [
            'users' => User::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->with(['roles', 'invite'])
            ->paginate(10),
        ]);
    }
}
