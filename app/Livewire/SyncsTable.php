<?php

namespace App\Livewire;

use App\Models\Scan;
use App\Tables\Columns\TextColumn;
use App\Tables\Table;
use App\Tables\TableComponent;

class SyncsTable extends TableComponent
{
    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => Scan::query())
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->searchable(),
                TextColumn::make('barcode')->label('Barcode')->sortable()->searchable(),
                TextColumn::make('quantity')->label('quantity'),
                TextColumn::make('sync_status')->label('Status')->sortable()->searchable(),
                TextColumn::make('created_at')->label('Scan Date')->sortable()->searchable()->dateForHumans(),
                TextColumn::make('updated_at')->label('Updated Date')->sortable()->searchable()->dateForHumans(),
            ])
            ->defaultSort('id', 'desc');
    }
}