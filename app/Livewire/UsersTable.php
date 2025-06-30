<?php

namespace App\Livewire;

use App\Models\User;
use App\Tables\Columns\ActionsColumn;
use App\Tables\Columns\DateColumn;
use App\Tables\Columns\TextColumn;
use App\Tables\Table;
use App\Tables\TableComponent;

class UsersTable extends TableComponent
{
    // Minimal configuration - everything else is auto-discovered!
    protected ?string $model = User::class;

    protected array $searchable = ['name', 'email'];

    protected ?string $title = 'User Management';

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->searchable(['name', 'email'])
            ->columns([
                TextColumn::make('user_info')
                    ->label('User')
                    ->sortable(false)
                    ->searchable(false)
                    ->value(function (User $record) {
                        $initials = $record->initials();
                        $isActive = $record->status;
                        $role = $record->roles->first();
                        $roleColor = $role && $role->name === 'admin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                        $statusColor = $isActive ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';

                        return '<div class="flex items-center space-x-3">'.
                               '<div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-sm font-medium">'.
                               $initials.
                               '</div>'.
                               '<div class="flex-1 min-w-0">'.
                               '<div class="flex items-center space-x-2">'.
                               '<span class="font-medium text-gray-900 dark:text-gray-100">'.e($record->name).'</span>'.
                               '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium '.$roleColor.'">'.($role ? ucfirst($role->name) : 'No Role').'</span>'.
                               '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium '.$statusColor.'">'.($isActive ? 'Active' : 'Inactive').'</span>'.
                               '</div>'.
                               '<div class="text-sm text-gray-500 dark:text-gray-400 mt-1">'.e($record->email).'</div>'.
                               '</div>'.
                               '</div>';
                    }),

                TextColumn::make('invitation_details')
                    ->label('Invitation Details')
                    ->sortable(false)
                    ->searchable(false)
                    ->value(function (User $record) {
                        $invite = $record->invite;
                        $isVerified = ! is_null($record->email_verified_at);

                        if (! $invite) {
                            $verifiedBadge = $isVerified ?
                                '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Email Verified</span>' :
                                '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">Email Unverified</span>';

                            return '<div class="space-y-1">'.
                                   '<div class="flex items-center space-x-2">'.
                                   '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">No Invitation</span>'.
                                   $verifiedBadge.
                                   '</div>'.
                                   '<div class="text-xs text-gray-500 dark:text-gray-400">User created without invitation</div>'.
                                   '</div>';
                        }

                        $invitedBy = $invite->invitedBy ? $invite->invitedBy->name : 'Unknown';
                        $inviteDate = $invite->created_at->diffForHumans();
                        $expiresAt = $invite->expires_at->diffForHumans();

                        if ($record->accepted_at) {
                            $statusBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Accepted</span>';
                            $details = 'Accepted '.$record->accepted_at->diffForHumans();
                        } elseif ($invite->expires_at < now()) {
                            $statusBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Expired</span>';
                            $details = 'Expired '.$expiresAt;
                        } else {
                            $statusBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">Pending</span>';
                            $details = 'Expires '.$expiresAt;
                        }

                        $verifiedBadge = $isVerified ?
                            '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Email Verified</span>' :
                            '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">Email Unverified</span>';

                        return '<div class="space-y-1">'.
                               '<div class="flex items-center space-x-2">'.
                               $statusBadge.
                               $verifiedBadge.
                               '</div>'.
                               '<div class="text-xs text-gray-500 dark:text-gray-400">'.
                               'Invited by '.e($invitedBy).' '.$inviteDate.'<br>'.
                               $details.
                               '</div>'.
                               '</div>';
                    }),

                DateColumn::make('created_at')
                    ->label('Created')
                    ->sortable()
                    ->diffForHumans(),

                ActionsColumn::make('actions')
                    ->label('Actions')
                    ->edit()
                    ->delete()
                    ->view(),
            ])
            ->crud(
                createRoute: route('users.create'),
                editRoute: 'users.edit',
                viewRoute: 'users.show',
                deleteAction: 'deleteUser'
            )
            ->exportable(['csv'])
            ->bulkActions([
                [
                    'name' => 'verify',
                    'label' => 'Verify Email',
                    'handle' => function (array $ids) {
                        User::whereIn('id', $ids)->update(['email_verified_at' => now()]);
                        session()->flash('message', count($ids).' users verified.');
                    },
                ],
                [
                    'name' => 'activate',
                    'label' => 'Activate Users',
                    'handle' => function (array $ids) {
                        User::whereIn('id', $ids)->update(['status' => true]);
                        session()->flash('message', count($ids).' users activated.');
                    },
                ],
                [
                    'name' => 'deactivate',
                    'label' => 'Deactivate Users',
                    'handle' => function (array $ids) {
                        User::whereIn('id', $ids)->update(['status' => false]);
                        session()->flash('message', count($ids).' users deactivated.');
                    },
                ],
                [
                    'name' => 'assign_admin',
                    'label' => 'Make Admin',
                    'handle' => function (array $ids) {
                        $users = User::whereIn('id', $ids)->get();
                        $updated = 0;
                        foreach ($users as $user) {
                            $user->syncRoles(['admin']);
                            $updated++;
                        }
                        session()->flash('message', $updated.' users assigned admin role.');
                    },
                ],
                [
                    'name' => 'assign_user',
                    'label' => 'Make User',
                    'handle' => function (array $ids) {
                        $users = User::whereIn('id', $ids)->get();
                        $updated = 0;
                        foreach ($users as $user) {
                            $user->syncRoles(['user']);
                            $updated++;
                        }
                        session()->flash('message', $updated.' users assigned user role.');
                    },
                ],
                [
                    'name' => 'send_invites',
                    'label' => 'Send Invitations',
                    'handle' => function (array $ids) {
                        $users = User::whereIn('id', $ids)->get();
                        $invitesSent = 0;

                        foreach ($users as $user) {
                            // Create invitation for users who don't have one
                            $existingInvite = \App\Models\Invite::where('user_id', $user->id)->first();
                            if (! $existingInvite) {
                                $invitation = \App\Models\Invite::create([
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'token' => \Illuminate\Support\Str::random(64),
                                    'user_id' => $user->id,
                                    'invited_by' => auth()->id(),
                                    'expires_at' => now()->addHours(24),
                                ]);

                                $invitation->notify(new \App\Notifications\InviteNotification($invitation));
                                $invitesSent++;
                            }
                        }

                        session()->flash('message', $invitesSent.' invitations sent.');
                    },
                ],
                [
                    'name' => 'delete',
                    'label' => 'Delete Selected',
                    'handle' => function (array $ids) {
                        User::whereIn('id', $ids)->delete();
                        session()->flash('message', count($ids).' users deleted.');
                    },
                ],
            ])
            ->filters([
                [
                    'key' => 'role',
                    'label' => 'Role',
                    'type' => 'select',
                    'options' => [
                        '' => 'All Roles',
                        'admin' => 'Admin',
                        'user' => 'User',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        if ($value) {
                            return $query->whereHas('roles', function ($q) use ($value) {
                                $q->where('name', $value);
                            });
                        }

                        return $query;
                    },
                ],
                [
                    'key' => 'status',
                    'label' => 'Status',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        if ($value !== '') {
                            return $query->where('status', (bool) $value);
                        }

                        return $query;
                    },
                ],
                [
                    'key' => 'verified',
                    'label' => 'Email Verified',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        '1' => 'Verified',
                        '0' => 'Unverified',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        if ($value === '1') {
                            return $query->whereNotNull('email_verified_at');
                        } elseif ($value === '0') {
                            return $query->whereNull('email_verified_at');
                        }

                        return $query;
                    },
                ],
                [
                    'key' => 'invitation_status',
                    'label' => 'Invitation Status',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        'accepted' => 'Accepted',
                        'pending' => 'Pending',
                        'expired' => 'Expired',
                        'none' => 'No Invitation',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        switch ($value) {
                            case 'accepted':
                                return $query->whereNotNull('accepted_at');
                            case 'pending':
                                return $query->whereNull('accepted_at')->whereHas('invite', function ($q) {
                                    $q->where('expires_at', '>', now());
                                });
                            case 'expired':
                                return $query->whereNull('accepted_at')->whereHas('invite', function ($q) {
                                    $q->where('expires_at', '<', now());
                                });
                            case 'none':
                                return $query->whereDoesntHave('invite');
                            default:
                                return $query;
                        }
                    },
                ],
                [
                    'key' => 'created_after',
                    'label' => 'Created After',
                    'type' => 'date',
                    'default' => null,
                    'apply' => function ($query, $value) {
                        return $query->whereDate('created_at', '>=', $value);
                    },
                ],
            ]);
    }

    public function create(): void
    {
        $this->redirect(route('users.create'));
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Check if user can be deleted
            if ($user->id === auth()->id()) {
                session()->flash('error', 'You cannot delete your own account.');

                return;
            }

            // Delete associated invite if exists
            if ($user->invite) {
                $user->invite->delete();
            }

            // Delete the user
            $user->delete();

            session()->flash('message', 'User and associated invitation deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete user: '.$e->getMessage());
        }
    }

    protected function getQuery()
    {
        return parent::getQuery()->with(['roles', 'invite.invitedBy']);
    }
}
