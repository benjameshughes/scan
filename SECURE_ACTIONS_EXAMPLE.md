# Secure Table Actions System

## Overview

The table actions system has been refactored to eliminate JavaScript security vulnerabilities by using secure Livewire callbacks instead of client-side JavaScript execution.

## Security Improvements

### Before (Insecure):
```php
// ❌ DANGEROUS - JavaScript execution on client side
ActionsColumn::make('actions')
    ->custom('Delete', "javascript:deleteRecord({id})")  // Vulnerable to XSS
```

### After (Secure):
```php
// ✅ SECURE - Server-side PHP callback execution
ActionsColumn::make('actions')
    ->custom('Delete', null)
    ->callback(function($record, $component) {
        // Secure server-side execution
        $record->delete();
        $component->dispatch('record-deleted');
    })
```

## Usage Examples

### 1. Basic Callback Action
```php
ActionsColumn::make('actions')
    ->custom('Archive', null)
    ->callback(function($record, $component) {
        $record->update(['archived_at' => now()]);
        session()->flash('message', "Record archived successfully");
    })
```

### 2. Action with Confirmation
```php
ActionsColumn::make('actions')
    ->custom('Delete', null)
    ->callback(function($record, $component) {
        $record->delete();
        $component->dispatch('record-deleted');
    })
    ->confirm('Are you sure you want to delete this record?')
```

### 3. Dynamic Labels and Icons
```php
ActionsColumn::make('actions')
    ->custom('Toggle Status', null)
    ->dynamicLabel(fn($record) => $record->is_active ? 'Deactivate' : 'Activate')
    ->dynamicIcon(fn($record) => $record->is_active ? 'eye-slash' : 'eye')
    ->callback(function($record, $component) {
        $record->update(['is_active' => !$record->is_active]);
        $status = $record->is_active ? 'activated' : 'deactivated';
        session()->flash('message', "Record {$status} successfully");
    })
```

### 4. Complex Business Logic
```php
ActionsColumn::make('actions')
    ->custom('Process Order', null)
    ->callback(function($record, $component) {
        // Complex server-side logic
        if ($record->status !== 'pending') {
            session()->flash('error', 'Order cannot be processed');
            return;
        }
        
        DB::transaction(function() use ($record) {
            $record->update(['status' => 'processing']);
            $record->orderItems()->update(['processed_at' => now()]);
            // Send notifications, update inventory, etc.
        });
        
        session()->flash('message', 'Order processed successfully');
    })
    ->confirm('Process this order? This action cannot be undone.')
```

## Migration Guide

### Old JavaScript Actions
```php
// ❌ Remove JavaScript actions
->custom('Export', "javascript:exportRecord({id})")
->custom('Print', "javascript:window.print()")
```

### New Secure Actions
```php
// ✅ Replace with secure callbacks
->custom('Export', null)
->callback(function($record, $component) {
    return response()->download($record->generateExport());
})

->custom('Print', null)
->callback(function($record, $component) {
    $component->dispatch('print-record', ['id' => $record->id]);
    // Handle printing via Livewire events or server-side PDF generation
})
```

## Benefits

1. **Security**: No client-side JavaScript execution eliminates XSS vulnerabilities
2. **Server-side validation**: All actions go through proper Laravel validation and authorization
3. **Debugging**: PHP errors are properly logged and handled
4. **Testing**: Actions can be unit tested like any other PHP code
5. **Performance**: No JavaScript parsing or execution overhead
6. **Consistency**: All actions follow the same Livewire pattern

## Action Types Supported

1. **Livewire Methods**: Direct component method calls (`->livewire('methodName')`)
2. **PHP Callbacks**: Closure execution (`->callback(function(...) {...})`)
3. **URL Links**: Safe external links only (`->url('https://example.com')`)

JavaScript actions have been completely removed for security reasons.