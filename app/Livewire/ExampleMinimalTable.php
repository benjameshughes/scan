<?php

namespace App\Livewire;

use App\Models\Product;
use App\Tables\TableComponent;

class ExampleMinimalTable extends TableComponent
{
    // This is all you need! Everything else is auto-discovered:
    // - Columns from model fillable fields
    // - Appropriate column types (dates, badges for status fields)
    // - CRUD actions
    // - Search functionality
    // - Sorting
    // - Pagination

    protected ?string $model = Product::class;

    // That's it!
    // The system will:
    // 1. Auto-discover columns from the model's fillable fields
    // 2. Auto-detect appropriate column types (TextColumn, DateColumn, BadgeColumn)
    // 3. Add an ActionsColumn with edit/delete buttons
    // 4. Enable search on text fields
    // 5. Enable sorting on most fields
    // 6. Set up pagination
    // 7. Create a fully functional table with minimal code
}
