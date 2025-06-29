<?php

namespace App\Livewire;

use App\Models\Scan;
use App\Tables\Columns\ActionsColumn;
use App\Tables\Columns\BadgeColumn;
use App\Tables\Columns\DateColumn;
use App\Tables\Columns\TextColumn;
use App\Tables\Table;
use App\Tables\TableComponent;

class SyncsTable extends TableComponent
{
    protected ?string $model = Scan::class;

    protected array $searchable = ['barcode', 'sync_status'];

    protected ?string $title = 'Scan History';

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->crud(
                createRoute: route('scans.create'),
                editRoute: null,
                viewRoute: 'scans.show',
                deleteAction: null
            )
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('barcode')->label('Barcode')->sortable()->searchable(),
                TextColumn::make('quantity')->label('Quantity')->sortable(),
                TextColumn::make('action')->label('Action')
                    ->value(function (Scan $scan) {
                        return ucfirst($scan->action ?? 'decrease');
                    }),
                BadgeColumn::make('sync_status')->label('Status')
                    ->colors([
                        'pending' => 'yellow',
                        'synced' => 'green',
                        'failed' => 'red',
                    ])
                    ->sortable()->searchable(),
                BadgeColumn::make('submitted')->label('Submitted')
                    ->colors([
                        '1' => 'green',
                        '0' => 'yellow',
                    ])
                    ->value(function (Scan $scan) {
                        return $scan->submitted ? '1' : '0';
                    }),
                DateColumn::make('created_at')->label('Scan Date')->diffForHumans()->sortable(),
                DateColumn::make('updated_at')->label('Last Updated')->diffForHumans()->sortable(),
                ActionsColumn::make('actions')->view('scans.show')->delete(),
            ])
            ->exportable(['csv', 'xlsx'])
            ->filters([
                [
                    'key' => 'sync_status',
                    'label' => 'Sync Status',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        'pending' => 'Pending',
                        'synced' => 'Synced',
                        'failed' => 'Failed',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        return $query->where('sync_status', $value);
                    },
                ],
                [
                    'key' => 'submitted',
                    'label' => 'Submission Status',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        '1' => 'Submitted',
                        '0' => 'Pending',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        return $query->where('submitted', $value);
                    },
                ],
                [
                    'key' => 'action',
                    'label' => 'Action Type',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        'increase' => 'Increase',
                        'decrease' => 'Decrease',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        return $query->where('action', $value);
                    },
                ],
                [
                    'key' => 'date_from',
                    'label' => 'From Date',
                    'type' => 'date',
                    'default' => null,
                    'apply' => function ($query, $value) {
                        return $query->whereDate('created_at', '>=', $value);
                    },
                ],
                [
                    'key' => 'date_to',
                    'label' => 'To Date',
                    'type' => 'date',
                    'default' => null,
                    'apply' => function ($query, $value) {
                        return $query->whereDate('created_at', '<=', $value);
                    },
                ],
            ])
            ->bulkActions([
                [
                    'name' => 'retry_sync',
                    'label' => 'Retry Sync',
                    'handle' => function (array $ids) {
                        // Queue sync jobs for failed scans
                        Scan::whereIn('id', $ids)->update(['sync_status' => 'pending']);
                        session()->flash('message', count($ids).' scans queued for retry.');
                    },
                ],
                [
                    'name' => 'delete',
                    'label' => 'Delete Selected',
                    'handle' => function (array $ids) {
                        Scan::whereIn('id', $ids)->delete();
                        session()->flash('message', count($ids).' scans deleted.');
                    },
                ],
            ])
            ->defaultSort('id', 'desc');
    }

    public function create(): void
    {
        $this->redirect(route('scans.create'));
    }
}
