<?php

namespace App\Livewire;

use App\Models\Invite;
use App\Notifications\InviteNotification;
use App\Tables\Table;
use App\Tables\TableComponent;

class InvitesTable extends TableComponent
{
    protected ?string $model = Invite::class;

    protected array $searchable = ['email', 'name'];

    protected ?string $title = 'Invitations Management';

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->query(fn () => Invite::with(['user', 'invitedBy'])->latest())
            ->crud(
                createRoute: route('admin.invites.create'),
                editRoute: null,
                viewRoute: null,
                deleteAction: null
            )
            ->exportable(['csv'])
            ->filters([
                [
                    'key' => 'status',
                    'label' => 'Status',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'expired' => 'Expired',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        if ($value === 'pending') {
                            return $query->whereNull('accepted_at')->where('expires_at', '>', now());
                        } elseif ($value === 'accepted') {
                            return $query->whereNotNull('accepted_at');
                        } elseif ($value === 'expired') {
                            return $query->whereNull('accepted_at')->where('expires_at', '<=', now());
                        }

                        return $query;
                    },
                ],
            ])
            ->bulkActions([
                [
                    'name' => 'resend',
                    'label' => 'Resend Selected',
                    'handle' => function (array $ids) {
                        $invites = Invite::whereIn('id', $ids)
                            ->whereNull('accepted_at')
                            ->where('expires_at', '>', now())
                            ->get();

                        foreach ($invites as $invite) {
                            $invite->update(['expires_at' => now()->addHours(24)]);
                            $invite->notify(new InviteNotification($invite));
                        }

                        session()->flash('message', count($invites).' invitations resent.');
                    },
                ],
                [
                    'name' => 'revoke',
                    'label' => 'Revoke Selected',
                    'handle' => function (array $ids) {
                        $invites = Invite::whereIn('id', $ids)
                            ->whereNull('accepted_at')
                            ->with('user')
                            ->get();

                        foreach ($invites as $invite) {
                            if ($invite->user) {
                                $invite->user->delete();
                            }
                            $invite->delete();
                        }

                        session()->flash('message', count($invites).' invitations revoked.');
                    },
                ],
            ]);
    }

    public function create(): void
    {
        $this->redirect(route('admin.invites.create'));
    }

    public function resendInvite($inviteId)
    {
        $invite = Invite::findOrFail($inviteId);

        if (! $invite->isAccepted() && ! $invite->isExpired()) {
            $invite->update(['expires_at' => now()->addHours(24)]);
            $invite->notify(new InviteNotification($invite));
            session()->flash('message', 'Invitation resent successfully.');
        }
    }

    public function revokeInvite($inviteId)
    {
        $invite = Invite::findOrFail($inviteId);

        if (! $invite->isAccepted()) {
            if ($invite->user) {
                $invite->user->delete();
            }
            $invite->delete();
            session()->flash('message', 'Invitation revoked successfully.');
        }
    }
}
