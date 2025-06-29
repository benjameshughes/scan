<?php

namespace App\Tables;

use App\Models\User;

class UsersTable extends TableComponent
{
    protected function table(): Table
    {
        return (new Table)
            ->query(fn () => User::query())
            ->schema([
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sortable' => true,
                ],
                [
                    'key' => 'name',
                    'label' => 'Name',
                    'sortable' => true,
                ],
                [
                    'key' => 'email',
                    'label' => 'Email',
                    'sortable' => true,
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Created At',
                    'sortable' => true,
                    'format' => fn ($value) => $value->format('Y-m-d H:i'),
                ],
            ])
            ->searchable(['name', 'email'])
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
                        return $query->where('role', $value);
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
            ])
            ->actions([
                [
                    'name' => 'edit',
                    'label' => 'Edit',
                    'icon' => 'pencil',
                    'url' => fn ($row) => route('users.edit', $row),
                ],
                [
                    'name' => 'delete',
                    'label' => 'Delete',
                    'icon' => 'trash',
                    'action' => fn ($row) => $this->deleteUser($row->id),
                ],
            ])
            ->bulkActions([
                [
                    'name' => 'delete',
                    'label' => 'Delete Selected',
                    'handle' => function (array $ids) {
                        User::whereIn('id', $ids)->delete();
                        session()->flash('message', count($ids).' users deleted.');
                    },
                ],
                [
                    'name' => 'verify',
                    'label' => 'Verify Selected',
                    'handle' => function (array $ids) {
                        User::whereIn('id', $ids)->update(['email_verified_at' => now()]);
                        session()->flash('message', count($ids).' users verified.');
                    },
                ],
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function deleteUser($id)
    {
        User::destroy($id);
        session()->flash('message', 'User deleted.');
    }
}
