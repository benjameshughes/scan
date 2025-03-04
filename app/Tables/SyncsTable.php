<?php

namespace App\Tables;

use App\Models\Scan;
use App\Tables\Columns\TextColumn;

class SyncsTable extends Table
{
    protected string $model = Scan::class;

    public function getSearchableColumns(): array
    {
        return ['barcode'];
    }

    public function columns(): array
    {
        return [
            TextColumn::make('id'),
            TextColumn::make('barcode')
            ->label('Barcode'),
            TextColumn::make('sync_status')
                ->label('Sync Status'),
            TextColumn::make('submitted_at')
                ->label('Submitted At')
            ->dateForHumans(),
            TextColumn::make('created_at')
                ->label('Scanned')
            ->dateForHumans(),
            TextColumn::make('submitted')
                ->label('Status')
            ->value(function (Scan $scan) {
                if ($scan->submitted) {
                    return 'Submitted';
                } else {
                    return 'Pending';
                }
            }),
        ];
    }
}