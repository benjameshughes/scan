<?php

namespace App\Tables;

use App\Models\Scan;
use App\Tables\Columns\TextColumn;
use App\Tables\Columns\BadgeColumn;
use App\Tables\Table;

class SyncsTable extends Table
{
    protected string $model = Scan::class;

    public function getSearchableColumns(): array
    {
        return ['barcode'];
    }

    public function getFilters(): array
    {
        return [
            ['key' => 'submitted', 'label' => 'Submitted',],
            ['key' => 'not_submitted', 'label' => 'Not Submitted',],
        ];
    }

    public function columns(): array
    {
        return [
            TextColumn::make('barcode')
            ->label('Barcode'),
            TextColumn::make('submitted_at')
                ->label('Submitted At'),
            TextColumn::make('created_at')
                ->label('Scanned')
            ->dateForHumans(),
        ];
    }
}