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
        $this->authorize('delete', $user);

        $user = User::find($user->id);

        // If user has an invite that hasn't been accepted, delete the invite too
        if ($user->invite && ! $user->invite->isAccepted()) {
            $user->invite->delete();
        }

        $user->delete();
        $this->dispatch('user-deleted');
    }

    public function resendInvite($inviteId)
    {
        $invitation = Invite::findOrFail($inviteId);

        if (! $invitation->isAccepted() && ! $invitation->isExpired()) {
            $invitation->update(['expires_at' => now()->addHours(24)]);

            $invitation->notify(new InviteNotification($invitation));

            $this->dispatch('invite-resent');
            session()->flash('message', 'Invitation resent successfully.');
        } else {
            session()->flash('error', 'Cannot resend this invitation.');
        }
    }

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->isAdmin = auth()->user()->can('delete users');
    }

    public function render()
    {
        return view('livewire.admin.users.user-list', [
            'users' => User::where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')
                ->with(['roles', 'invite.invitedBy'])
                ->paginate(10),
        ]);
    }
}
