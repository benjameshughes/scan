<?php

namespace App\Tables;

use App\Models\Product;
use App\Tables\Columns\TextColumn;
use App\Tables\Table;

class ProductsTable extends Table
{

    protected string $model = Product::class;

    public function getSearchableColumns(): array
    {
        return ['sku', 'name', 'barcode'];
    }

    public function getFilters(): array
    {
        return [
            ['key' => 'submitted', 'label' => 'Submitted',],
            ['key' => 'not_submitted', 'label' => 'Not Submitted',],
        ];
    }

    public function getPerPageOptions(): array
    {
        return [10, 25, 50, 100];
    }

    public function columns(): array
    {
        return [
            TextColumn::make('sku')
                ->label('SKU'),
            TextColumn::make('name')
                ->label('Name'),
            TextColumn::make('barcode')
                ->label('Barcode'),
        ];
    }

}