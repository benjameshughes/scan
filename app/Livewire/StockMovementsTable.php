<?php

namespace App\Livewire;

use App\Models\StockMovement;
use App\Tables\Columns\ActionsColumn;
use App\Tables\Columns\BadgeColumn;
use App\Tables\Columns\DateColumn;
use App\Tables\Columns\TextColumn;
use App\Tables\Table;
use App\Tables\TableComponent;

class StockMovementsTable extends TableComponent
{
    protected ?string $model = StockMovement::class;

    protected ?string $title = 'Stock Movement History';

    protected array $searchable = ['product.sku', 'product.name', 'from_location_code', 'to_location_code'];

    public function mount()
    {
        // Check if user has permission to view stock movements
        if (! auth()->user()->can('view stock movements')) {
            abort(403, 'You do not have permission to view stock movements.');
        }
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->columns([
                DateColumn::make('moved_at')
                    ->label('Date/Time')
                    ->format('M j, Y g:i A')
                    ->sortable(),

                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->render(function ($record) {
                        $name = $record->product->name ?? 'No name available';
                        if (strlen($name) > 30) {
                            return '<span title="'.e($name).'">'.e(substr($name, 0, 30)).'...</span>';
                        }

                        return e($name);
                    }),

                TextColumn::make('movement_display')
                    ->label('Movement')
                    ->render(function ($record) {
                        $from = $record->from_location_code ?? 'Unknown';
                        $to = $record->to_location_code ?? 'Unknown';

                        return '<div class="text-xs text-zinc-600 dark:text-zinc-400">
                                    <div class="flex items-center gap-1">
                                        <span class="font-mono">'.e($from).'</span>
                                        <flux:icon.arrow-right class="size-3" />
                                        <span class="font-mono">'.e($to).'</span>
                                    </div>
                                </div>';
                    }),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->sortable()
                    ->render(function ($record) {
                        return '<div class="text-center font-medium">'.number_format($record->quantity).'</div>';
                    }),

                BadgeColumn::make('formatted_type')
                    ->label('Type')
                    ->colors([
                        'success' => StockMovement::TYPE_BAY_REFILL,
                        'info' => StockMovement::TYPE_MANUAL_TRANSFER,
                        'warning' => StockMovement::TYPE_SCAN_ADJUSTMENT,
                    ])
                    ->render(function ($record) {
                        return $record->formatted_type;
                    }),

                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                ActionsColumn::make('actions')
                    ->actions([
                        'view' => [
                            'icon' => 'eye',
                            'label' => 'View',
                            'variant' => 'ghost',
                            'size' => 'xs',
                            'show' => function () {
                                return auth()->user()->can('view stock movements');
                            },
                        ],
                        'edit' => [
                            'icon' => 'pencil',
                            'label' => 'Edit',
                            'variant' => 'ghost',
                            'size' => 'xs',
                            'show' => function () {
                                return auth()->user()->can('edit stock movements');
                            },
                        ],
                    ]),
            ])
            ->defaultSort('moved_at', 'desc')
            ->perPage(20)
            ->crud(
                createRoute: 'locations.movements.create',
                editRoute: 'locations.movements.edit',
                viewRoute: 'locations.movements.show'
            )
            ->filters([
                [
                    'key' => 'date_from',
                    'label' => 'From Date',
                    'type' => 'date',
                    'default' => now()->subDays(30)->format('Y-m-d'),
                    'apply' => function ($query, $value) {
                        return $query->whereDate('moved_at', '>=', $value);
                    },
                ],
                [
                    'key' => 'date_to',
                    'label' => 'To Date',
                    'type' => 'date',
                    'default' => now()->format('Y-m-d'),
                    'apply' => function ($query, $value) {
                        return $query->whereDate('moved_at', '<=', $value);
                    },
                ],
                [
                    'key' => 'movement_type',
                    'label' => 'Movement Type',
                    'type' => 'select',
                    'options' => [
                        '' => 'All Types',
                        StockMovement::TYPE_BAY_REFILL => 'Bay Refill',
                        StockMovement::TYPE_MANUAL_TRANSFER => 'Manual Transfer',
                        StockMovement::TYPE_SCAN_ADJUSTMENT => 'Scan Adjustment',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        if ($value) {
                            return $query->where('type', $value);
                        }

                        return $query;
                    },
                ],
                [
                    'key' => 'location_filter',
                    'label' => 'Location',
                    'type' => 'text',
                    'default' => '',
                    'apply' => function ($query, $value) {
                        if ($value) {
                            return $query->where(function ($q) use ($value) {
                                $q->where('from_location_code', 'like', '%'.$value.'%')
                                    ->orWhere('to_location_code', 'like', '%'.$value.'%');
                            });
                        }

                        return $query;
                    },
                ],
            ]);
    }

    public function getQuery()
    {
        return parent::getQuery()
            ->with(['product', 'user', 'fromLocation', 'toLocation']);
    }

    public function create(): void
    {
        if (! auth()->user()->can('create stock movements')) {
            abort(403, 'You do not have permission to create stock movements.');
        }

        $this->redirect(route('locations.movements.create'), navigate: true);
    }

    public function view(int $id): void
    {
        if (! auth()->user()->can('view stock movements')) {
            abort(403, 'You do not have permission to view stock movements.');
        }

        $this->redirect(route('locations.movements.show', $id), navigate: true);
    }

    public function edit(int $id): void
    {
        if (! auth()->user()->can('edit stock movements')) {
            abort(403, 'You do not have permission to edit stock movements.');
        }

        $this->redirect(route('locations.movements.edit', $id), navigate: true);
    }
}
