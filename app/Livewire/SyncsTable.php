<?php

namespace App\Livewire;

use App\Models\Scan;
use App\Tables\Columns\ActionsColumn;
use App\Tables\Columns\BadgeColumn;
use App\Tables\Columns\DateColumn;
use App\Tables\Columns\TextColumn;
use App\Tables\Columns\CustomColumn;
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
                CustomColumn::make('sync_status')->label('Sync Status')->sortable()->searchable()
                    ->view('tables.columns.sync-status'),
                CustomColumn::make('sync_error')->label('Error Details')->sortable(false)
                    ->view('tables.columns.sync-error'),
                TextColumn::make('sync_attempts')->label('Attempts')
                    ->value(function (Scan $scan) {
                        return $scan->sync_attempts > 0 ? $scan->sync_attempts : '-';
                    })
                    ->sortable(),
                DateColumn::make('last_sync_attempt')->label('Last Attempt')->diffForHumans()->sortable(),
                BadgeColumn::make('submitted')->label('Submitted')
                    ->colors([
                        '1' => 'green',
                        '0' => 'yellow',
                    ])
                    ->value(function (Scan $scan) {
                        return $scan->submitted ? '1' : '0';
                    }),
                DateColumn::make('created_at')->label('Scan Date')->diffForHumans()->sortable(),
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
                    'key' => 'sync_error_type',
                    'label' => 'Error Type',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        'network' => 'Network Error',
                        'auth' => 'Authentication Error',
                        'rate_limit' => 'Rate Limit',
                        'product_not_found' => 'Product Not Found',
                        'api_error' => 'API Error',
                        'timeout' => 'Request Timeout',
                        'validation' => 'Validation Error',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        return $query->where('sync_error_type', $value);
                    },
                ],
                [
                    'key' => 'multiple_failures',
                    'label' => 'Multiple Failures',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        '1' => 'Has Multiple Failures',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        if ($value === '1') {
                            return $query->where('sync_attempts', '>', 1)->where('sync_status', 'failed');
                        }
                        return $query;
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
                        $scans = Scan::whereIn('id', $ids)->get();
                        
                        // Reset sync status and increment attempts for tracking
                        foreach ($scans as $scan) {
                            $scan->update([
                                'sync_status' => 'pending',
                                'sync_error_message' => null,
                                'sync_error_type' => null,
                            ]);
                        }
                        
                        session()->flash('message', count($ids).' scans queued for retry.');
                    },
                ],
                [
                    'name' => 'retry_failed_only',
                    'label' => 'Retry Failed Only',
                    'handle' => function (array $ids) {
                        $retryCount = Scan::whereIn('id', $ids)
                            ->where('sync_status', 'failed')
                            ->update([
                                'sync_status' => 'pending',
                                'sync_error_message' => null,
                                'sync_error_type' => null,
                            ]);
                        
                        session()->flash('message', $retryCount.' failed scans queued for retry.');
                    },
                ],
                [
                    'name' => 'mark_synced',
                    'label' => 'Mark as Synced',
                    'handle' => function (array $ids) {
                        $updateCount = Scan::whereIn('id', $ids)->update([
                            'sync_status' => 'synced',
                            'synced_at' => now(),
                            'sync_error_message' => null,
                            'sync_error_type' => null,
                        ]);
                        
                        session()->flash('message', $updateCount.' scans marked as synced.');
                    },
                ],
                [
                    'name' => 'clear_errors',
                    'label' => 'Clear Error Info',
                    'handle' => function (array $ids) {
                        $clearCount = Scan::whereIn('id', $ids)->update([
                            'sync_error_message' => null,
                            'sync_error_type' => null,
                        ]);
                        
                        session()->flash('message', 'Error information cleared for '.$clearCount.' scans.');
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
