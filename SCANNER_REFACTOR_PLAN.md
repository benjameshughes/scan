# Scanner Refactor Plan

This document outlines the complete refactoring of the monolithic `ProductScanner` component into focused services, actions, and components with maximum separation of concerns.

## Current State Analysis

The current `ProductScanner.php` (715 lines) handles:
- Camera hardware management
- Barcode processing and validation
- Product lookup and display
- Scan submission and queueing
- Refill bay operations
- User feedback (sound/vibration)
- Email workflow handling
- PWA lifecycle management
- Location selection and stock validation

## Refactored Architecture

### Services (Business Logic Layer)

#### `app/Services/Scanner/CameraManagerService.php`
**Responsibility**: Camera hardware state management and JavaScript interface
- Camera initialization and lifecycle
- JavaScript event handling (onCameraReady, onCameraError, etc.)
- Torch control and support detection
- Loading states and error management

#### `app/Services/Scanner/UserFeedbackService.php`
**Responsibility**: User feedback (sound, vibration, notifications)
- Sound playback logic
- Vibration pattern management
- User settings integration
- Success/error feedback coordination

#### `app/Services/Scanner/LocationManagerService.php`
**Responsibility**: Stock location management and formatting
- Location data transformation
- Stock level validation
- Auto-selection logic
- Smart location selector data preparation

### Actions (Single-Purpose Operations)

#### `app/Actions/Scanner/ProcessBarcodeAction.php`
**Responsibility**: Barcode validation and product lookup
- Barcode prefix validation
- Product database lookup
- Result standardization
- Error handling

#### `app/Actions/Scanner/ValidateScanDataAction.php`
**Responsibility**: Scan data validation
- Quantity validation
- User permissions check
- Business rule validation
- Data sanitization

#### `app/Actions/Scanner/CreateScanRecordAction.php`
**Responsibility**: Scan record creation and job dispatching
- Scan model creation
- Queue job dispatching
- Logging coordination
- Database transaction management

#### `app/Actions/Scanner/HandleEmailRefillAction.php`
**Responsibility**: Email refill workflow processing
- URL parameter parsing
- Product lookup from barcode
- Permission validation
- Refill form initialization

#### `app/Actions/Scanner/PrepareRefillFormAction.php`
**Responsibility**: Refill form data preparation
- Stock location fetching
- Location auto-selection
- Permission validation
- Error handling

#### `app/Actions/Scanner/ProcessRefillSubmissionAction.php`
**Responsibility**: Refill form submission processing
- Form validation
- Stock transfer execution
- Success/error handling
- Post-submission cleanup

#### `app/Actions/Scanner/ResetScanStateAction.php`
**Responsibility**: Centralized scan state reset
- State property reset
- Validation clearing
- Camera state management
- Form cleanup

### Livewire Components (UI Layer)

#### `app/Livewire/Scanner/ProductScanner.php` (Main Coordinator)
**Responsibility**: Main component coordination and state management
- Component orchestration
- Route handling
- Global state management
- Child component communication

#### `app/Livewire/Scanner/CameraDisplay.php`
**Responsibility**: Camera interface and controls
- Camera overlay states
- Camera control buttons
- Hardware feedback display
- Loading states

#### `app/Livewire/Scanner/ProductInfo.php`
**Responsibility**: Product information display
- Product details rendering
- Barcode display
- "Product not found" states
- Action buttons (Scan Another)

#### `app/Livewire/Scanner/ScanForm.php`
**Responsibility**: Scan submission form
- Quantity controls
- Action toggle (increase/decrease)
- Form validation
- Submission handling

#### `app/Livewire/Scanner/ManualEntry.php`
**Responsibility**: Manual barcode input
- Barcode input field
- Real-time validation
- Manual submission
- Input formatting

#### `app/Livewire/Scanner/RefillForm.php`
**Responsibility**: Refill bay operations
- Location selection
- Quantity controls
- Form submission
- Error handling
- Success states

#### `app/Livewire/Scanner/EmptyBayNotification.php`
**Responsibility**: Empty bay notification handling
- Notification dispatch
- Success feedback
- State reset

### Blade Templates (View Layer)

#### `resources/views/livewire/scanner/product-scanner.blade.php`
**Responsibility**: Main scanner layout and component composition
- Header layout
- Email refill banner
- Component composition
- Responsive design

#### `resources/views/livewire/scanner/camera-display.blade.php`
**Responsibility**: Camera interface rendering
- Video element
- Loading overlays
- Ready to scan state
- Camera controls
- Scanning guides

#### `resources/views/livewire/scanner/product-info.blade.php`
**Responsibility**: Product information display
- Product details card
- Not found states
- Action buttons
- Responsive layout

#### `resources/views/livewire/scanner/scan-form.blade.php`
**Responsibility**: Scan form interface
- Quantity controls
- Action toggle
- Submit buttons
- Validation messages

#### `resources/views/livewire/scanner/manual-entry.blade.php`
**Responsibility**: Manual barcode input interface
- Input field
- Help text
- Validation feedback
- Form layout

#### `resources/views/livewire/scanner/refill-form.blade.php`
**Responsibility**: Refill form interface
- Location selector
- Quantity controls
- Form actions
- Error/success states

#### `resources/views/livewire/scanner/empty-bay-notification.blade.php`
**Responsibility**: Empty bay notification interface
- Notification button
- Success feedback
- Icon display

## Data Transfer Objects (DTOs)

#### `app/DTOs/Scanner/BarcodeResult.php`
**Responsibility**: Standardized barcode processing result
```php
readonly class BarcodeResult
{
    public function __construct(
        public string $barcode,
        public bool $isValid,
        public ?Product $product,
        public ?string $error = null,
        public bool $shouldTriggerFeedback = false,
    ) {}
}
```

#### `app/DTOs/Scanner/ScanData.php`
**Responsibility**: Validated scan submission data
```php
readonly class ScanData
{
    public function __construct(
        public string $barcode,
        public int $quantity,
        public string $action,
        public int $userId,
        public array $metadata = [],
    ) {}
}
```

#### `app/DTOs/Scanner/CameraState.php`
**Responsibility**: Camera state information
```php
readonly class CameraState
{
    public function __construct(
        public bool $isScanning,
        public bool $isLoading,
        public bool $torchSupported,
        public bool $torchEnabled,
        public ?string $error = null,
    ) {}
}
```

## Migration Strategy

### Phase 1: Extract Services (Low Risk)
1. Create `CameraManagerService` - extract camera state logic
2. Create `UserFeedbackService` - extract sound/vibration logic
3. Create `LocationManagerService` - extract location logic
4. Update `ProductScanner` to use services

### Phase 2: Extract Actions (Medium Risk)
1. Create `ProcessBarcodeAction` - extract barcode processing
2. Create `CreateScanRecordAction` - extract scan creation
3. Create `ValidateScanDataAction` - extract validation logic
4. Create refill-related actions
5. Update services to use actions

### Phase 3: Split UI Components (Higher Risk)
1. Create child components one by one
2. Move template sections to child components
3. Update parent component to use children
4. Test each component individually

### Phase 4: Create DTOs and Polish
1. Standardize data structures with DTOs
2. Add comprehensive testing
3. Performance optimization
4. Documentation updates

## Laravel & Livewire Features to Leverage

### Laravel Framework Features

#### **Collections & Data Manipulation**
```php
// Replace manual array manipulation with Collections
$this->availableLocations = collect($locations)
    ->filter(fn($location) => $location['stock'] > 0)
    ->map(fn($location) => new LocationData($location))
    ->sortBy('name')
    ->values();
```

#### **Form Requests for Validation**
```php
// app/Http/Requests/Scanner/ScanSubmissionRequest.php
class ScanSubmissionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'barcode' => ['required', new BarcodePrefixCheck('505903')],
            'quantity' => 'required|integer|min:1',
            'action' => 'required|in:increase,decrease',
        ];
    }
}

// app/Http/Requests/Scanner/RefillSubmissionRequest.php
class RefillSubmissionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'selectedLocationId' => 'required|string',
            'refillQuantity' => 'required|integer|min:1|max:' . $this->getMaxStock(),
        ];
    }
    
    private function getMaxStock(): int
    {
        // Custom validation logic
    }
}
```

#### **Config-Driven Settings**
```php
// config/scanner.php
return [
    'barcode_prefix' => env('SCANNER_BARCODE_PREFIX', '505903'),
    'default_quantity' => 1,
    'auto_submit_delay' => 2000,
    'vibration_patterns' => [
        'light' => [50, 25, 50],
        'medium' => [100, 50, 200],
        'strong' => [200, 100, 300],
    ],
    'camera_settings' => [
        'preferred_facing' => 'environment',
        'resolution' => ['width' => 1280, 'height' => 720],
    ],
];
```

#### **Service Container & Dependency Injection**
```php
// Proper constructor injection in Services
class CameraManagerService
{
    public function __construct(
        private UserFeedbackService $feedback,
        private ConfigRepository $config,
        private LoggerInterface $logger,
    ) {}
}

// Bind in AppServiceProvider
$this->app->singleton(CameraManagerService::class);
$this->app->singleton(UserFeedbackService::class);
```

#### **Events & Listeners**
```php
// app/Events/Scanner/BarcodeScanned.php
class BarcodeScanned
{
    public function __construct(
        public readonly string $barcode,
        public readonly ?Product $product,
        public readonly User $user,
    ) {}
}

// app/Listeners/Scanner/TriggerUserFeedback.php
class TriggerUserFeedback
{
    public function handle(BarcodeScanned $event): void
    {
        if ($event->product) {
            $this->userFeedback->playSuccessSound($event->user);
            $this->userFeedback->triggerVibration($event->user);
        }
    }
}
```

#### **Eloquent Relationships & Scopes**
```php
// Product model enhancements
class Product extends Model
{
    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class, 'barcode', 'barcode');
    }
    
    public function recentScans(): HasMany
    {
        return $this->scans()->where('created_at', '>=', now()->subHours(24));
    }
    
    public function scopeByBarcode(Builder $query, string $barcode): Builder
    {
        return $query->where('barcode', $barcode)
                    ->orWhere('barcode2', $barcode)
                    ->orWhere('barcode3', $barcode);
    }
}
```

#### **Resource Classes for API Responses**
```php
// app/Http/Resources/Scanner/ProductResource.php
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sku' => $this->sku,
            'name' => $this->name,
            'barcode' => $this->barcode,
            'recent_scans_count' => $this->whenLoaded('recentScans', fn() => $this->recentScans->count()),
            'stock_locations' => LocationResource::collection($this->whenLoaded('stockLocations')),
        ];
    }
}
```

#### **Custom Validation Rules**
```php
// app/Rules/Scanner/ValidStockLocation.php
class ValidStockLocation implements Rule
{
    public function __construct(private Product $product) {}
    
    public function passes($attribute, $value): bool
    {
        return $this->product->stockLocations()
            ->where('location_id', $value)
            ->where('stock_level', '>', 0)
            ->exists();
    }
}
```

#### **Cache Integration**
```php
// In LocationManagerService
public function getAvailableLocations(Product $product): Collection
{
    return Cache::remember(
        "product_locations_{$product->sku}",
        now()->addMinutes(5),
        fn() => $this->fetchLocationsFromAPI($product)
    );
}
```

#### **Queue Jobs with Better Structure**
```php
// app/Jobs/Scanner/ProcessScanSubmission.php
class ProcessScanSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        private ScanData $scanData,
        private array $metadata = [],
    ) {}
    
    public function handle(SyncBarcodeAction $syncAction): void
    {
        $syncAction->handle($this->scanData, $this->metadata);
    }
}
```

### Livewire-Specific Features

#### **Computed Properties**
```php
// In components, replace methods with computed properties
#[Computed]
public function maxRefillStock(): int
{
    return $this->selectedLocation?->stock_level ?? 0;
}

#[Computed]
public function smartLocationSelectorData(): Collection
{
    return collect($this->availableLocations)
        ->map(fn($location) => new LocationSelectorData($location))
        ->filter(fn($data) => $data->hasStock());
}
```

#### **Form Objects**
```php
// app/Livewire/Forms/Scanner/ScanForm.php
class ScanForm extends Form
{
    #[Validate('required|min:1')]
    public int $quantity = 1;
    
    #[Validate(['required', BarcodePrefixCheck::class])]
    public string $barcode = '';
    
    #[Validate('required|boolean')]
    public bool $scanAction = false;
    
    public function submit(): Scan
    {
        $this->validate();
        
        return app(CreateScanRecordAction::class)->handle(
            new ScanData(
                barcode: $this->barcode,
                quantity: $this->quantity,
                action: $this->scanAction ? 'increase' : 'decrease',
                userId: auth()->id(),
            )
        );
    }
}
```

#### **Lazy Loading**
```php
// Lazy load heavy components
#[Lazy]
class RefillForm extends Component
{
    public function placeholder(): View
    {
        return view('livewire.scanner.refill-form-placeholder');
    }
}
```

#### **Real-time Validation**
```php
// Use Livewire's built-in real-time validation
#[Validate('required|integer|min:1')]
public int $quantity = 1;

// Custom validation with dependencies
public function updatedRefillQuantity(): void
{
    $this->validateOnly('refillQuantity', [
        'refillQuantity' => [
            'required',
            'integer',
            'min:1',
            new ValidStockLocation($this->product),
            'max:' . $this->maxRefillStock,
        ],
    ]);
}
```

#### **Component Lifecycle Hooks**
```php
// Use lifecycle hooks for cleanup
public function dehydrate(): void
{
    // Clear sensitive data before component serialization
    if (!$this->isScanning) {
        $this->cameraError = '';
    }
}

public function hydrate(): void
{
    // Restore state after component rehydration
    $this->restoreCameraState();
}
```

#### **Synthetic Events**
```php
// Better event handling with synthetic events
#[On('camera-state-changed')]
public function handleCameraStateChange(bool $isScanning): void
{
    $this->isScanning = $isScanning;
    
    if ($isScanning) {
        $this->dispatch('scanner-activated', userId: auth()->id());
    }
}
```

#### **Attributes for Better DX**
```php
// Use Livewire attributes for cleaner code
#[Title('Product Scanner')]
#[Layout('layouts.scanner')]
class ProductScanner extends Component
{
    #[Url(as: 'action')]
    public string $emailAction = '';
    
    #[Url(as: 'barcode')]
    public string $emailBarcode = '';
    
    #[Locked]
    public ?Product $product = null;
}
```

### Additional Laravel Patterns

#### **Policies for Authorization**
```php
// app/Policies/Scanner/ScannerPolicy.php
class ScannerPolicy
{
    public function useTorch(User $user): bool
    {
        return $user->can('use scanner') && $user->hasVerifiedEmail();
    }
    
    public function submitRefill(User $user, Product $product): bool
    {
        return $user->can('refill bays') && $product->isActive();
    }
}
```

#### **Notifications for Better UX**
```php
// app/Notifications/Scanner/RefillCompleted.php
class RefillCompleted extends Notification
{
    public function __construct(
        private Product $product,
        private int $quantity,
        private string $location,
    ) {}
    
    public function via(): array
    {
        return ['database', 'broadcast'];
    }
    
    public function toArray(): array
    {
        return [
            'message' => "Refilled {$this->quantity} units of {$this->product->name}",
            'location' => $this->location,
            'product_sku' => $this->product->sku,
        ];
    }
}
```

#### **Artisan Commands for Maintenance**
```php
// app/Console/Commands/Scanner/ClearScannerCache.php
class ClearScannerCache extends Command
{
    protected $signature = 'scanner:clear-cache {--all : Clear all scanner-related cache}';
    
    public function handle(): int
    {
        $tags = $this->option('all') 
            ? ['scanner', 'products', 'locations']
            : ['scanner'];
            
        Cache::tags($tags)->flush();
        
        $this->info('Scanner cache cleared successfully!');
        return Command::SUCCESS;
    }
}
```

## Benefits After Refactoring

### Maintainability
- Single responsibility per class
- Clear separation of concerns
- Easier to debug and modify
- Better code organization

### Testability
- Unit test services independently
- Mock dependencies easily
- Test business logic separately from UI
- Integration tests for workflows

### Performance
- Smaller components re-render less
- Services are singletons
- Better caching opportunities
- Lazy loading of child components

### Developer Experience
- Multiple developers can work simultaneously
- Clear interfaces between components
- Self-documenting code structure
- Easier onboarding for new developers

## Component Communication

### Parent → Child
- Properties for data passing
- Events for actions
- Services for shared state

### Child → Parent
- Livewire events for notifications
- Service methods for state updates
- DTOs for structured data

### Cross-Component
- Alpine.js stores for client-side state
- Livewire events for loose coupling
- Services for shared business logic

## File Structure Summary

```
app/
├── Services/Scanner/
│   ├── CameraManagerService.php
│   ├── UserFeedbackService.php
│   └── LocationManagerService.php
├── Actions/Scanner/
│   ├── ProcessBarcodeAction.php
│   ├── ValidateScanDataAction.php
│   ├── CreateScanRecordAction.php
│   ├── HandleEmailRefillAction.php
│   ├── PrepareRefillFormAction.php
│   ├── ProcessRefillSubmissionAction.php
│   └── ResetScanStateAction.php
├── DTOs/Scanner/
│   ├── BarcodeResult.php
│   ├── ScanData.php
│   └── CameraState.php
└── Livewire/Scanner/
    ├── ProductScanner.php
    ├── CameraDisplay.php
    ├── ProductInfo.php
    ├── ScanForm.php
    ├── ManualEntry.php
    ├── RefillForm.php
    └── EmptyBayNotification.php

resources/views/livewire/scanner/
├── product-scanner.blade.php
├── camera-display.blade.php
├── product-info.blade.php
├── scan-form.blade.php
├── manual-entry.blade.php
├── refill-form.blade.php
└── empty-bay-notification.blade.php
```

This refactoring will transform the 715-line monolith into a well-organized, maintainable, and testable architecture while preserving all existing functionality.