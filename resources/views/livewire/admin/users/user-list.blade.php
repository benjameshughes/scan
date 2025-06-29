<div>
    <div>
        <flux:button href="{{route('admin.users.add')}}">Add User</flux:button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Invite Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Invite Expires</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Roles</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
            @foreach($users as $user)
                <tr wire:key="{{$user->id}}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $user->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        @if($user->invite)
                            @if($user->invite->isAccepted())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Accepted
                                </span>
                            @elseif($user->invite->isExpired())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Expired
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                    Pending
                                </span>
                            @endif
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                No Invite
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($user->invite && !$user->invite->isAccepted())
                            {{ $user->invite->expires_at->format('M j, Y') }}
                            @if($user->invite->isExpired())
                                <span class="text-red-500">(Expired)</span>
                            @endif
                        @elseif($user->invite && $user->invite->isAccepted())
                            <span class="text-green-500">Accepted {{ $user->invite->accepted_at->diffForHumans() }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        @foreach($user->roles as $role)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        <flux:button href="{{route('admin.users.edit', $user->id)}}">Edit</flux:button>
                        @role('admin')
                        @if($user->invite && !$user->invite->isAccepted() && !$user->invite->isExpired())
                            <flux:button variant="primary" wire:click="resendInvite({{ $user->invite->id }})">Resend Invite</flux:button>
                        @endif
                        @if($user->invite && !$user->invite->isAccepted())
                            <flux:button variant="danger" wire:click="delete('{{$user->id}}')">Revoke Invite</flux:button>
                        @else
                            <flux:button variant="danger" wire:click="delete('{{$user->id}}')">Delete User</flux:button>
                        @endif
                        @endrole
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <x-action-message on="user-deleted">
        User action completed successfully
    </x-action-message>
    
    <x-action-message on="invite-resent">
        Invitation resent successfully
    </x-action-message>
</div>
