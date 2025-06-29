<?php

namespace App\Livewire;

use App\Models\User;
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
                    'label' => 'Verify Selected',
                    'handle' => function (array $ids) {
                        User::whereIn('id', $ids)->update(['email_verified_at' => now()]);
                        session()->flash('message', count($ids).' users verified.');
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
                [
                    'name' => 'send_invites',
                    'label' => 'Send Invitations',
                    'handle' => function (array $ids) {
                        $users = User::whereIn('id', $ids)->get();
                        $invitesSent = 0;
                        
                        foreach ($users as $user) {
                            // Create invitation for users who don't have one
                            $existingInvite = \App\Models\Invite::where('user_id', $user->id)->first();
                            if (!$existingInvite) {
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
            ])
            ->filters([
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
        User::destroy($id);
        session()->flash('message', 'User deleted.');
    }
}
