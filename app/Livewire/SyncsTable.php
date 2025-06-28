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
            ->query(fn() => Scan::class)
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->searchable(),
            ])
            ->defaultSort('id', 'desc');
    }
}