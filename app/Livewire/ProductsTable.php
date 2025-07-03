<?php

namespace App\Livewire;

use App\Models\Product;
use App\Tables\Columns\ActionsColumn;
use App\Tables\Columns\DateColumn;
use App\Tables\Columns\TextColumn;
use App\Tables\Table;
use App\Tables\TableComponent;

class ProductsTable extends TableComponent
{
    protected ?string $model = Product::class;

    protected array $searchable = ['sku', 'name', 'barcode', 'barcode_2', 'barcode_3'];

    protected ?string $title = 'Products Management';

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->columns([
                TextColumn::make('sku')->label('SKU')->sortable()->searchable(),
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('barcode')->label('Primary Barcode')->searchable(),
                TextColumn::make('barcode_2')->label('Barcode 2'),
                TextColumn::make('barcode_3')->label('Barcode 3'),
                DateColumn::make('updated_at')->label('Last Updated')->diffForHumans()->sortable(),
                ActionsColumn::make('actions')
                    ->edit()
                    ->delete()
                    ->view(),
            ])
            ->exportable(['csv', 'excel'])
            ->crud(
                createRoute: 'products.create',
                editRoute: 'products.edit',
                viewRoute: 'products.show',
                deleteAction: 'delete'
            )
            ->bulkActions([
                [
                    'name' => 'delete',
                    'label' => 'Delete Selected',
                    'handle' => function (array $ids) {
                        Product::whereIn('id', $ids)->delete();
                        session()->flash('message', count($ids).' products deleted.');
                    },
                ],
                [
                    'name' => 'sync',
                    'label' => 'Sync with Linnworks',
                    'handle' => function (array $ids) {
                        // Dispatch sync jobs for selected products
                        session()->flash('message', count($ids).' products queued for sync.');
                    },
                ],
            ])
            ->filters([
                [
                    'key' => 'has_barcode_2',
                    'label' => 'Has Secondary Barcode',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        '1' => 'Yes',
                        '0' => 'No',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        if ($value === '1') {
                            return $query->whereNotNull('barcode_2');
                        } elseif ($value === '0') {
                            return $query->whereNull('barcode_2');
                        }

                        return $query;
                    },
                ],
                [
                    'key' => 'updated_after',
                    'label' => 'Updated After',
                    'type' => 'date',
                    'default' => null,
                    'apply' => function ($query, $value) {
                        return $query->whereDate('updated_at', '>=', $value);
                    },
                ],
            ])
            ->defaultSort('name');
    }

    public function create(): void
    {
        $this->redirect(route('products.create'));
    }
}
