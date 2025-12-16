<div class="space-y-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" dismissible>
            {{ session('message') }}
        </flux:callout>
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="x-circle" dismissible>
            {{ session('error') }}
        </flux:callout>
    @endif

    {{-- Header with Search and Actions --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1 max-w-sm">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search name or email..."
                icon="magnifying-glass"
                clearable
            />
        </div>

        <div class="flex items-center gap-2">
            {{-- Bulk Actions Dropdown --}}
            @if (count($selected) > 0)
                <flux:dropdown>
                    <flux:button icon="chevron-down" iconTrailing>
                        {{ count($selected) }} selected
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item wire:click="verifySelected" icon="check-badge">
                            Verify Email
                        </flux:menu.item>
                        <flux:menu.item wire:click="activateSelected" icon="check-circle">
                            Activate
                        </flux:menu.item>
                        <flux:menu.item wire:click="deactivateSelected" icon="x-circle">
                            Deactivate
                        </flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item wire:click="makeAdminSelected" icon="shield-check">
                            Make Admin
                        </flux:menu.item>
                        <flux:menu.item wire:click="makeUserSelected" icon="user">
                            Make User
                        </flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item wire:click="sendInvitesSelected" icon="envelope">
                            Send Invitations
                        </flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item wire:click="deleteSelected" wire:confirm="Delete selected users?" icon="trash" variant="danger">
                            Delete Selected
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            @endif

            {{-- Filter Toggle --}}
            <flux:modal.trigger name="filters">
                <flux:button icon="funnel" variant="ghost">
                    Filters
                    @if ($role || $status !== '' || $verified || $invitationStatus || $createdAfter)
                        <flux:badge size="sm" color="blue" class="ml-1">Active</flux:badge>
                    @endif
                </flux:button>
            </flux:modal.trigger>

            <flux:button icon="plus" variant="primary" href="{{ route('users.create') }}">
                New User
            </flux:button>
        </div>
    </div>

    {{-- Filters Modal --}}
    <flux:modal name="filters" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">Filters</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model.live="role" label="Role" placeholder="All roles">
                    <flux:select.option value="">All Roles</flux:select.option>
                    <flux:select.option value="admin">Admin</flux:select.option>
                    <flux:select.option value="user">User</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="status" label="Status" placeholder="All">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="1">Active</flux:select.option>
                    <flux:select.option value="0">Inactive</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="verified" label="Email Verified" placeholder="All">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="1">Verified</flux:select.option>
                    <flux:select.option value="0">Unverified</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="invitationStatus" label="Invitation Status" placeholder="All">
                    <flux:select.option value="">All</flux:select.option>
                    <flux:select.option value="accepted">Accepted</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="expired">Expired</flux:select.option>
                    <flux:select.option value="none">No Invitation</flux:select.option>
                </flux:select>

                <flux:input wire:model.live="createdAfter" type="date" label="Created After" class="sm:col-span-2" />
            </div>

            <div class="flex justify-between pt-4">
                <flux:button wire:click="clearFilters" variant="ghost">
                    Clear All
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="primary">Done</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Table --}}
    <flux:table :paginate="$users">
        <flux:table.columns>
            <flux:table.column class="w-12">
                <flux:checkbox wire:model.live="selectAll" />
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortDirection" wire:click="sort('name')">
                User
            </flux:table.column>
            <flux:table.column>Invitation</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">
                Created
            </flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($users as $user)
                @php
                    $role = $user->roles->first();
                    $invite = $user->invite;
                    $isVerified = !is_null($user->email_verified_at);
                @endphp
                <flux:table.row :key="$user->id">
                    <flux:table.cell>
                        <flux:checkbox wire:model.live="selected" value="{{ $user->id }}" />
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar name="{{ $user->name }}" size="sm" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</span>
                                    <flux:badge size="sm" :color="$role && $role->name === 'admin' ? 'red' : 'blue'">
                                        {{ $role ? ucfirst($role->name) : 'No Role' }}
                                    </flux:badge>
                                    <flux:badge size="sm" :color="$user->status ? 'green' : 'zinc'">
                                        {{ $user->status ? 'Active' : 'Inactive' }}
                                    </flux:badge>
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 truncate">{{ $user->email }}</div>
                            </div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="space-y-1">
                            <div class="flex items-center gap-1 flex-wrap">
                                @if ($invite)
                                    @if ($user->accepted_at)
                                        <flux:badge size="sm" color="green" icon="check-circle">Accepted</flux:badge>
                                    @elseif ($invite->expires_at < now())
                                        <flux:badge size="sm" color="red" icon="x-circle">Expired</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="amber" icon="clock">Pending</flux:badge>
                                    @endif
                                @else
                                    <flux:badge size="sm" color="zinc">No Invitation</flux:badge>
                                @endif
                                <flux:badge size="sm" :color="$isVerified ? 'green' : 'amber'">
                                    {{ $isVerified ? 'Verified' : 'Unverified' }}
                                </flux:badge>
                            </div>
                            @if ($invite)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Invited by {{ $invite->invitedBy?->name ?? 'Unknown' }} {{ $invite->created_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                        {{ $user->created_at->diffForHumans() }}
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" href="{{ route('users.show', $user) }}" />
                            <flux:button size="sm" variant="ghost" icon="pencil" href="{{ route('users.edit', $user) }}" />
                            @if ($user->id !== auth()->id())
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $user->id }})" wire:confirm="Delete this user?" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-8">
                        <div class="flex flex-col items-center gap-2 text-zinc-500">
                            <flux:icon.inbox class="size-8" />
                            <span>No users found</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
