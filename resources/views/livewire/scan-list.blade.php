@php
    use App\Models\Scan;$columns = [
        ['key' => 'barcode', 'label' => 'Barcode'],
        ['key' => 'created_at', 'label' => 'Scan Date'],
        ['key' => 'submitted', 'label' => 'Submitted'],
        ['key' => 'submitted_at', 'label' => 'Submit Date'],
        ['key' => 'quantity', 'label' => 'Quantity'],
    ];

    $actions = [
        ['url' => route('scan.show', '1'), 'label' => 'View', 'button-colour' => 'blue'],
        ['url' => route('scan.edit', '1'), 'label' => 'Edit', 'button-colour' => 'green'],
    ];

    $rows = $scans; // or whatever your data source is
@endphp

<x-table :columns="$columns" :rows="$rows" :actions="$actions" :perPageOptions="$perPageOptions"
         :sortDirection="$sortDirection"/>

