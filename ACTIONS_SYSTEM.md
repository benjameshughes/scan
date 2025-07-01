# Table Actions System

This document describes the new comprehensive table actions system that provides a flexible, extensible way to add actions to table rows.

## Overview

The actions system consists of:

1. **BaseAction** - Abstract base class for all actions
2. **Predefined CRUD Actions** - ViewAction, EditAction, DeleteAction
3. **Custom Actions** - EmailAction, ResetPasswordAction, ImportAction, ExportAction, CustomAction
4. **ActionsColumn** - Enhanced column class with fluent API
5. **Icon System** - Modular SVG icons for all action types

## Basic Usage

### Standard CRUD Actions

```php
ActionsColumn::make('actions')
    ->view()              // View action with automatic route discovery
    ->edit()              // Edit action with automatic route discovery  
    ->delete()            // Delete action with confirmation dialog
```

### Custom Route Configuration

```php
ActionsColumn::make('actions')
    ->view('custom.show')         // Custom view route
    ->edit('custom.edit')         // Custom edit route
    ->delete('customDelete')      // Custom delete method
```

### Predefined Custom Actions

```php
ActionsColumn::make('actions')
    ->email()                     // Send email (mailto link)
    ->email('contact_email')      // Custom email field
    ->resetPassword()             // Reset password with confirmation
    ->export('CSV')               // Export with format
    ->import('products.import')   // Import with custom route
```

### Advanced Custom Actions

```php
ActionsColumn::make('actions')
    ->action(
        (new CustomAction('Stock History'))
            ->icon('chart-bar')
            ->color('purple')
            ->livewire('showStockHistory')  // Calls Livewire method
    )
    ->action(
        (new CustomAction('Download'))
            ->icon('download')
            ->color('indigo')
            ->javascript('downloadFile')    // JavaScript callback
    )
    ->action(
        (new EmailAction('Send Notification'))
            ->icon('mail')
            ->compose('sendNotification')   // Livewire compose method
    )
```

## Action Types

### ViewAction
- **Default Icon**: eye
- **Default Color**: blue
- **Permission**: `view` policy
- **Auto Route**: `{model}s.show`

### EditAction
- **Default Icon**: pencil
- **Default Color**: green  
- **Permission**: `update` policy
- **Auto Route**: `{model}s.edit`

### DeleteAction
- **Default Icon**: trash
- **Default Color**: red
- **Permission**: `delete` policy
- **Confirmation**: Built-in confirmation dialog
- **Method**: Livewire `delete({id})` call

### EmailAction
- **Default Icon**: mail
- **Default Color**: blue
- **Options**: 
  - `mailto(field)` - Direct mailto link
  - `compose(method)` - Livewire compose method

### ResetPasswordAction
- **Default Icon**: key
- **Default Color**: amber
- **Permission**: `update` policy
- **Confirmation**: Built-in confirmation dialog
- **Method**: Livewire `resetPassword({id})` call

### ImportAction
- **Default Icon**: upload
- **Default Color**: purple
- **No Permission Check**: Import typically doesn't relate to specific records
- **Options**:
  - `route(name)` - Route to import page
  - `modal(method)` - Livewire modal method

### ExportAction
- **Default Icon**: download
- **Default Color**: indigo
- **No Permission Check**: Export typically doesn't relate to specific records
- **Options**:
  - `route(name)` - Route to export endpoint
  - `download(method)` - Livewire download method
  - `format(type)` - Export format (changes label)

### CustomAction
- **Flexible**: Fully customizable action
- **URL Types**:
  - `livewire(method)` - Calls Livewire method with record ID
  - `javascript(callback)` - Executes JavaScript with record ID
  - Static URL or closure callback

## Permission System

All actions respect Laravel policies by default:

```php
// Automatic permission checks
->view()    // Checks: $user->can('view', $record)
->edit()    // Checks: $user->can('update', $record)  
->delete()  // Checks: $user->can('delete', $record)

// Custom permissions
->action(
    (new CustomAction('Approve'))
        ->permission('approve')  // Custom permission
)

// No permission check
->action(
    (new CustomAction('Public'))
        ->noPermissionCheck()    // Skip permission validation
)
```

## Confirmation Dialogs

Actions can include confirmation dialogs:

```php
->action(
    (new CustomAction('Archive'))
        ->confirm('Are you sure you want to archive this item?')
)

// Built-in confirmations
->delete()              // "Are you sure you want to delete this item?"
->resetPassword()       // "Are you sure you want to reset this user's password?"
```

## Styling and Icons

### Available Icons
- `eye` - View actions
- `pencil` - Edit actions  
- `trash` - Delete actions
- `mail` - Email actions
- `key` - Password/security actions
- `upload` - Import actions
- `download` - Export actions
- `chart-bar` - Analytics/reports

### Color Options
- `blue` - Primary actions
- `green` - Edit/update actions
- `red` - Destructive actions
- `amber` - Warning actions
- `purple` - Import actions
- `indigo` - Export actions

### Custom Styling

```php
->action(
    (new CustomAction('Custom'))
        ->icon('chart-bar')
        ->color('purple')
        ->attributes([
            'class' => 'extra-styling',
            'data-tooltip' => 'Custom action'
        ])
)
```

## Examples

### User Management Table

```php
ActionsColumn::make('actions')
    ->view()
    ->edit()
    ->delete()
    ->email('email', 'Send Email')
    ->resetPassword()
    ->action(
        (new CustomAction('Login As'))
            ->icon('key')
            ->color('amber')
            ->livewire('loginAs')
            ->permission('impersonate')
            ->confirm('Login as this user?')
    )
```

### Product Management Table

```php
ActionsColumn::make('actions')
    ->view()
    ->edit()
    ->delete()
    ->action(
        (new CustomAction('Stock History'))
            ->icon('chart-bar')
            ->color('purple')
            ->livewire('showStockHistory')
    )
    ->export('CSV', 'products.export')
    ->action(
        (new CustomAction('Sync'))
            ->icon('upload')
            ->color('blue')
            ->livewire('syncProduct')
            ->confirm('Sync this product with external system?')
    )
```

### Reports Table

```php
ActionsColumn::make('actions')
    ->view()
    ->action(
        (new ExportAction())
            ->format('PDF')
            ->route('reports.download')
    )
    ->action(
        (new CustomAction('Schedule'))
            ->icon('clock')
            ->color('indigo')
            ->livewire('scheduleReport')
    )
```

## Migration from Old System

### Before (Old System)
```php
->custom('Stock History', function ($record) {
    return "javascript:Livewire.find('{$this->getId()}').call('showStockHistory', {$record->id})";
}, 'chart-bar', 'purple')
```

### After (New System)
```php
->action(
    (new CustomAction('Stock History'))
        ->icon('chart-bar')
        ->color('purple')
        ->livewire('showStockHistory')
)
```

The new system provides:
- ✅ Better type safety
- ✅ Automatic permission handling
- ✅ Built-in confirmation dialogs
- ✅ Cleaner, more readable code
- ✅ Consistent icon and styling system
- ✅ Extensible architecture for new action types