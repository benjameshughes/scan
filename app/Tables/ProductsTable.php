<?php

namespace App\Tables;

use App\Models\Product;
use App\Tables\Columns\TextColumn;
use App\Tables\Concerns\HasActions;
use App\Tables\Table;
use Filament\Actions\Action;

class ProductsTable extends Table
{
    protected string $model = Product::class;

    public function getSearchableColumns(): array
    {
        return ['sku', 'name', 'barcode', 'barcode_2'. 'barcode_3'];
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
            TextColumn::make('barcode_2')
                ->label('Old Barcode'),
            TextColumn::make('barcode_3')
                ->label('Old Barcode'),
            TextColumn::make('updated_at')
                ->label('Edited'),
        ];
    }

}