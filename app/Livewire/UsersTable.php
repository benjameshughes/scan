<?php

namespace App\Livewire;

use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteNotification;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortField = 'name';

    #[Url]
    public string $sortDirection = 'asc';

    // Filters
    #[Url]
    public string $role = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $verified = '';

    #[Url]
    public string $invitationStatus = '';

    #[Url]
    public ?string $createdAfter = null;

    // Bulk selection
    public array $selected = [];

    public bool $selectAll = false;

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selected = $this->getQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'role', 'status', 'verified', 'invitationStatus', 'createdAfter']);
        $this->resetPage();
    }

    // Bulk Actions
    public function verifySelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        User::whereIn('id', $this->selected)->update(['email_verified_at' => now()]);
        session()->flash('message', count($this->selected).' users verified.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function activateSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        User::whereIn('id', $this->selected)->update(['status' => true]);
        session()->flash('message', count($this->selected).' users activated.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function deactivateSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        User::whereIn('id', $this->selected)->update(['status' => false]);
        session()->flash('message', count($this->selected).' users deactivated.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function makeAdminSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $users = User::whereIn('id', $this->selected)->get();
        foreach ($users as $user) {
            $user->syncRoles(['admin']);
        }
        session()->flash('message', count($users).' users assigned admin role.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function makeUserSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $users = User::whereIn('id', $this->selected)->get();
        foreach ($users as $user) {
            $user->syncRoles(['user']);
        }
        session()->flash('message', count($users).' users assigned user role.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function sendInvitesSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $users = User::whereIn('id', $this->selected)->get();
        $invitesSent = 0;

        foreach ($users as $user) {
            $existingInvite = Invite::where('user_id', $user->id)->first();
            if (! $existingInvite) {
                $invitation = Invite::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'token' => Str::random(64),
                    'user_id' => $user->id,
                    'invited_by' => auth()->id(),
                    'expires_at' => now()->addHours(24),
                ]);

                $invitation->notify(new InviteNotification($invitation));
                $invitesSent++;
            }
        }

        session()->flash('message', $invitesSent.' invitations sent.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Filter out current user
        $idsToDelete = array_filter($this->selected, fn ($id) => (int) $id !== auth()->id());

        User::whereIn('id', $idsToDelete)->delete();
        session()->flash('message', count($idsToDelete).' users deleted.');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');

            return;
        }

        $user = User::find($id);
        if ($user) {
            if ($user->invite) {
                $user->invite->delete();
            }
            $user->delete();
            session()->flash('message', 'User deleted successfully.');
        }
    }

    protected function getQuery()
    {
        return User::query()
            ->with(['roles', 'invite.invitedBy'])
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->role, fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $this->role)))
            ->when($this->status !== '', fn ($q) => $q->where('status', (bool) $this->status))
            ->when($this->verified === '1', fn ($q) => $q->whereNotNull('email_verified_at'))
            ->when($this->verified === '0', fn ($q) => $q->whereNull('email_verified_at'))
            ->when($this->invitationStatus === 'accepted', fn ($q) => $q->whereNotNull('accepted_at'))
            ->when($this->invitationStatus === 'pending', fn ($q) => $q->whereNull('accepted_at')->whereHas('invite', fn ($r) => $r->where('expires_at', '>', now())))
            ->when($this->invitationStatus === 'expired', fn ($q) => $q->whereNull('accepted_at')->whereHas('invite', fn ($r) => $r->where('expires_at', '<', now())))
            ->when($this->invitationStatus === 'none', fn ($q) => $q->whereDoesntHave('invite'))
            ->when($this->createdAfter, fn ($q) => $q->whereDate('created_at', '>=', $this->createdAfter))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $users = $this->getQuery()->paginate(15);

        return view('livewire.users-table', [
            'users' => $users,
        ]);
    }
}
