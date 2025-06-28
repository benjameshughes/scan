<?php

namespace App\Tables;

use App\Models\Product;
use App\Tables\Columns\TextColumn;

class ProductsTable extends TableComponent
{
    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => Product::query())
            ->columns([
                TextColumn::make('sku')->label('SKU')->sortable(),
                TextColumn::make('name')->label('Name')->sortable(),
                TextColumn::make('barcode')->label('Barcode'),
                TextColumn::make('updated_at')->label('Last Updated')->dateForHumans(),
            ])
            ->searchable(['sku', 'name', 'barcode'])
            ->defaultSort('name', 'asc');
    }
}