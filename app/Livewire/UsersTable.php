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
                createRoute: route('admin.users.add'),
                editRoute: null,
                viewRoute: null,
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
        $this->redirect(route('admin.users.add'));
    }

    public function deleteUser($id)
    {
        User::destroy($id);
        session()->flash('message', 'User deleted.');
    }
}
