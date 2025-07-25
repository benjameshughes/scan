<?php

namespace App\Livewire;

use App\Actions\GetProductFromScannedBarcode;
use App\Actions\Stock\ExecuteStockTransferAction;
use App\Actions\Stock\GetProductStockLocationsAction;
use App\DTOs\EmptyBayDTO;
use App\Enums\VibrationPattern;
use App\Jobs\EmptyBayJob;
use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use App\Rules\BarcodePrefixCheck;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProductScanner extends Component
{
    // Camera state
    public bool $isScanning = false;

    public bool $isTorchOn = false;

    public bool $torchSupported = false;

    public bool $loadingCamera = true;

    public string $cameraError = '';

    // Scan state
    #[Validate([new BarcodePrefixCheck('505903')])]
    public ?string $barcode = null;

    #[Validate('required|integer|min:1')]
    public ?int $quantity = 1;

    public bool $barcodeScanned = false;

    public bool $playSuccessSound = false;

    public bool $triggerVibration = false;

    public bool $scanAction = false;
    
    public bool $autoSubmitEnabled = false;

    public ?Product $product = null;

    // Refill bay state
    public bool $showRefillForm = false;

    #[Validate('required')]
    public string $selectedLocationId = '';

    #[Validate('required|integer|min:1')]
    public ?int $refillQuantity = 1;

    public array $availableLocations = [];

    public bool $isProcessingRefill = false;

    public string $refillError = '';

    public string $refillSuccess = '';

    // Email workflow state
    public bool $isEmailRefill = false;

    public function messages(): array
    {
        return [
            'quantity.integer' => 'Quantity must be an integer.',
        ];
    }

    public function mount()
    {
        // Ensure user is authenticated and has scanner permission
        if (! auth()->check()) {
            abort(401, 'Authentication required');
        }

        if (! auth()->user()->can('view scanner')) {
            abort(403, 'Insufficient permissions to use scanner');
        }

        $this->loadingCamera = false; // Start with video element visible
        $this->isScanning = false;
        
        // Initialize user settings
        $userSettings = auth()->user()->settings;
        $this->autoSubmitEnabled = $userSettings['auto_submit'] ?? false;

        // Handle direct email navigation to refill bay
        $action = request('action');
        $barcodeParam = request('barcode');

        if ($action === 'refill' && $barcodeParam) {
            $this->handleEmailRefillRequest($barcodeParam);
        }
    }

    /**
     * Handle direct refill request from email notification
     */
    private function handleEmailRefillRequest(string $barcodeParam): void
    {
        try {
            // Set email refill mode
            $this->isEmailRefill = true;

            // Set the barcode and trigger product lookup
            $this->barcode = $barcodeParam;

            // Validate barcode and find product
            $this->validateOnly('barcode');
            $this->product = new GetProductFromScannedBarcode($this->barcode)->handle();

            if ($this->product) {
                // Product found - set up for refill workflow
                $this->barcodeScanned = true;

                // Check if user has refill permission before showing form
                if (auth()->user()->can('refill bays')) {
                    // Auto-trigger refill form
                    $this->showRefillBayForm();
                } else {
                    $this->refillError = 'You do not have permission to refill bays.';
                }
            } else {
                // Product not found
                $this->isEmailRefill = false; // Reset email mode
            }

        } catch (\Exception $e) {
            // Handle validation or lookup errors
            $this->cameraError = "Invalid barcode from email: {$e->getMessage()}";
            $this->isEmailRefill = false; // Reset email mode
        }
    }

    public function updatedBarcode()
    {
        if ($this->barcode) {
            $this->cameraError = '';

            // Validate just the barcode field with prefix check (no required rule needed here)
            try {
                $this->validateOnly('barcode');
                $this->product = new GetProductFromScannedBarcode($this->barcode)->handle();

                // Valid barcode - allow submission regardless of whether product is found
                $this->barcodeScanned = true;
                $this->isScanning = false;
                $this->dispatch('camera-state-changed', false); // Stop camera

                // Set sound and vibration flags for manual entry if product found (check user settings)
                $userSettings = auth()->user()->settings;
                $this->playSuccessSound = ($userSettings['scan_sound'] ?? true) && !!$this->product;
                
                $vibrationPattern = VibrationPattern::fromValue($userSettings['vibration_pattern'] ?? 'medium');
                $this->triggerVibration = $vibrationPattern->isEnabled() && !!$this->product;
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Invalid barcode - keep scanning, don't switch view
                $this->barcodeScanned = false;
                $this->product = null;
            }
        } else {
            // Barcode was cleared - reset the scan state
            $this->barcodeScanned = false;
            $this->product = null;
            $this->resetValidation('barcode');
        }
    }

    // Camera controls - Livewire handles state, dispatches to JS
    public function toggleCamera()
    {
        $this->isScanning = ! $this->isScanning;
        $this->dispatch('camera-state-changed', $this->isScanning);

        if (! $this->isScanning) {
            $this->cameraError = '';
            $this->isTorchOn = false; // Turn off torch when camera stops
        }
    }

    public function toggleTorch()
    {
        if (! $this->torchSupported) {
            $this->cameraError = 'Torch not supported on this device';

            return;
        }

        $this->isTorchOn = ! $this->isTorchOn;
        $this->dispatch('torch-state-changed', $this->isTorchOn);
    }

    // JS callbacks - JS reports back to Livewire
    #[On('onCameraReady')]
    public function onCameraReady()
    {
        $this->loadingCamera = false;
        $this->isScanning = true;
        $this->cameraError = '';
    }

    #[On('onCameraError')]
    public function onCameraError($error)
    {
        $this->loadingCamera = false;
        $this->isScanning = false;
        $this->cameraError = $error;
    }

    #[On('onTorchSupportDetected')]
    public function onTorchSupportDetected($supported)
    {
        $this->torchSupported = $supported;

        if (! $supported) {
            $this->isTorchOn = false;
        }
    }

    #[On('onTorchStateChanged')]
    public function onTorchStateChanged($enabled)
    {
        $this->isTorchOn = (bool) $enabled;
    }

    #[On('onBarcodeDetected')]
    public function onBarcodeDetected($barcodeData)
    {
        $this->barcode = $barcodeData;
        $this->barcodeScanned = true;
        $this->cameraError = '';

        // Keep camera running but pause scanning
        // JS handles pausing ZXing, we just update UI state
        $this->isScanning = false;

        try {
            $this->validateOnly('barcode');
            $this->product = new GetProductFromScannedBarcode($this->barcode)->handle();
            
            // Play success sound and trigger vibration when product is found (check user settings)
            $userSettings = auth()->user()->settings;
            $this->playSuccessSound = ($userSettings['scan_sound'] ?? true) && !!$this->product;
            
            $vibrationPattern = VibrationPattern::fromValue($userSettings['vibration_pattern'] ?? 'medium');
            $this->triggerVibration = $vibrationPattern->isEnabled() && !!$this->product;
            
            // Auto-submit if enabled and product found (future feature)
            if ($this->autoSubmitEnabled && $this->product) {
                $this->handleAutoSubmit();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Invalid barcode - keep scanning, don't switch view
            $this->barcodeScanned = false;
            $this->product = null;
            $this->playSuccessSound = false;
            $this->triggerVibration = false;
        }
    }

    /**
     * Handle location selection from smart location selector
     */
    #[On('locationChanged')]
    public function onLocationChanged($locationId): void
    {
        $this->selectedLocationId = $locationId;
        $this->resetValidation(['selectedLocationId']);

        // Reset quantity to safe value when location changes
        $this->refillQuantity = 1;
        $this->resetValidation(['refillQuantity']);
    }

    /**
     * Validate refill quantity when updated
     */
    public function updatedRefillQuantity()
    {
        if (! $this->selectedLocationId || empty($this->availableLocations)) {
            return;
        }

        // Find the selected location and get max stock
        $selectedLocation = collect($this->availableLocations)->first(function ($location, $index) {
            $locationId = $location['Location']['StockLocationId'] ?? $location['LocationId'] ?? $location['locationId'] ?? $location['id'] ?? $index;

            return $locationId == $this->selectedLocationId;
        });

        if (! $selectedLocation) {
            return;
        }

        $maxStock = $selectedLocation['StockLevel'] ?? $selectedLocation['stockLevel'] ?? $selectedLocation['stock'] ?? 0;

        // Validate against max stock
        if ($this->refillQuantity > $maxStock) {
            $this->refillQuantity = $maxStock;
            $this->addError('refillQuantity', "Maximum available quantity is {$maxStock} units.");
        } elseif ($this->refillQuantity < 1) {
            $this->addError('refillQuantity', 'Minimum quantity is 1 unit.');
        } else {
            $this->resetValidation(['refillQuantity']);
        }
    }

    /**
     * Get the maximum stock available for the selected location
     */
    public function getMaxRefillStockProperty(): int
    {
        if (! $this->selectedLocationId || empty($this->availableLocations)) {
            return 0;
        }

        $selectedLocation = collect($this->availableLocations)->first(function ($location, $index) {
            $locationId = $location['Location']['StockLocationId'] ?? $location['LocationId'] ?? $location['locationId'] ?? $location['id'] ?? $index;

            return $locationId == $this->selectedLocationId;
        });

        if (! $selectedLocation) {
            return 0;
        }

        return $selectedLocation['StockLevel'] ?? $selectedLocation['stockLevel'] ?? $selectedLocation['stock'] ?? 0;
    }

    /**
     * Get formatted locations for the smart location selector
     */
    public function getSmartLocationSelectorDataProperty(): array
    {
        if (empty($this->availableLocations)) {
            return [];
        }

        return collect($this->availableLocations)->map(function ($location) {
            // Handle different API response structures
            $locationData = $location['Location'] ?? $location;

            return [
                'StockLocationId' => $locationData['StockLocationId'] ?? $locationData['LocationId'] ?? $locationData['id'],
                'LocationName' => $locationData['LocationName'] ?? $locationData['Name'] ?? 'Unknown Location',
                'Quantity' => $location['Quantity'] ?? $location['Available'] ?? $location['Stock'] ?? 0,
            ];
        })->filter(function ($location) {
            // Only include locations with stock and valid ID
            return ! empty($location['StockLocationId']) && $location['Quantity'] > 0;
        })->values()->toArray();
    }

    // Form controls
    public function incrementQuantity()
    {
        $this->quantity++;
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function resetScan()
    {
        $this->barcode = null;
        $this->barcodeScanned = false;
        $this->product = null;
        $this->quantity = 1;
        $this->cameraError = '';
        $this->isEmailRefill = false;
        $this->playSuccessSound = false;
        $this->triggerVibration = false;
        $this->resetRefillForm();
        $this->resetValidation();
    }

    /**
     * Reset refill form state
     */
    public function resetRefillForm(): void
    {
        $this->showRefillForm = false;
        $this->selectedLocationId = '';
        $this->refillQuantity = 1;
        $this->availableLocations = [];
        $this->isProcessingRefill = false;
        $this->refillError = '';
        $this->refillSuccess = '';
        $this->resetValidation(['selectedLocationId', 'refillQuantity']);
    }

    public function startNewScan()
    {
        $this->resetScan();
        $this->isScanning = true;
        $this->dispatch('camera-state-changed', true); // Start camera
    }

    public function emptyBayNotification()
    {
        $emptyBayDTO = new EmptyBayDTO($this->barcode);
        EmptyBayJob::dispatch($emptyBayDTO);
    }

    public function save()
    {
        $this->validate([
            'barcode' => ['required', new BarcodePrefixCheck('505903')],
            'quantity' => 'required|integer|min:1',
        ]);

        $scan = Scan::create([
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'submitted' => false,
            'action' => $this->scanAction ? 'increase' : 'decrease',
            'sync_status' => 'pending',
            'user_id' => auth()->user()->id,
        ]);

        SyncBarcode::dispatch($scan);
        Log::channel('barcode')->info("{$this->barcode} Scanned");

        // Reset form first
        $this->resetScan();

        // Auto-resume scanning for next item
        $this->isScanning = true;
        $this->dispatch('camera-state-changed', true); // Start camera
    }

    public function clearError()
    {
        $this->cameraError = '';
    }

    /**
     * Reset sound flag after playing (called via JS timeout)
     */
    #[On('reset-sound-flag')]
    public function resetSoundFlag()
    {
        // Reset after a brief delay to allow sound to play
        $this->playSuccessSound = false;
    }

    #[On('reset-vibration-flag')]
    public function resetVibrationFlag()
    {
        // Reset after a brief delay to allow vibration to trigger
        $this->triggerVibration = false;
    }

    /**
     * Show the refill bay form for the current product
     */
    public function showRefillBayForm(): void
    {
        // Check permission
        if (! auth()->user()->can('refill bays')) {
            $this->refillError = 'You do not have permission to refill bays.';

            return;
        }

        if (! $this->product) {
            $this->refillError = 'No product selected for refill.';

            return;
        }

        try {
            $this->isProcessingRefill = true;
            $this->refillError = '';

            // Use the new action to get formatted stock locations
            $locations = app(GetProductStockLocationsAction::class)->handle($this->product);

            if (empty($locations)) {
                $this->refillError = 'No locations with stock found for this product.';
                $this->isProcessingRefill = false;

                return;
            }

            // Convert back to old format for backward compatibility with the view
            $this->availableLocations = collect($locations)->map(function ($location) {
                return [
                    'Location' => [
                        'StockLocationId' => $location['id'],
                        'LocationName' => $location['name'],
                    ],
                    'StockLevel' => $location['stock_level'],
                    'Available' => $location['available'],
                    'Allocated' => $location['allocated'],
                    'OnOrder' => $location['on_order'],
                    'MinimumLevel' => $location['minimum_level'],
                ];
            })->toArray();

            // Auto-select source location using the new action
            $defaultLocationId = config('linnworks.default_location_id');
            $preferredLocationId = config('linnworks.floor_location_id');

            $autoSelected = app(\App\Actions\Stock\AutoSelectLocationAction::class)->handle(
                $locations,
                $defaultLocationId,
                $preferredLocationId,
                $this->refillQuantity
            );

            if ($autoSelected) {
                $this->selectedLocationId = $autoSelected['id'];

                Log::channel('inventory')->info('Auto-selected location for refill', [
                    'product_sku' => $this->product->sku,
                    'auto_selected_location' => $this->selectedLocationId,
                    'location_name' => $autoSelected['name'],
                    'stock_level' => $autoSelected['stock_level'],
                ]);
            }

            $this->showRefillForm = true;
            $this->isProcessingRefill = false;

        } catch (\Exception $e) {
            $this->refillError = "Failed to load locations: {$e->getMessage()}";
            $this->isProcessingRefill = false;

            Log::channel('inventory')->error('Failed to load refill locations', [
                'product_sku' => $this->product?->sku,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Cancel the refill operation and return to scanner
     */
    public function cancelRefill(): void
    {
        $this->resetRefillForm();
    }

    /**
     * Submit the refill operation
     */
    public function submitRefill(): void
    {
        try {
            // Basic validation
            $this->validate([
                'selectedLocationId' => 'required',
                'refillQuantity' => 'required|integer|min:1',
            ]);

            $this->isProcessingRefill = true;
            $this->refillError = '';

            // Execute the stock transfer using the consolidated action
            $result = app(ExecuteStockTransferAction::class)->handle(
                user: auth()->user(),
                product: $this->product,
                quantity: $this->refillQuantity,
                operationType: 'refill',
                fromLocationId: $this->selectedLocationId,
                autoSelectSource: false, // User already selected source
                additionalMetadata: [
                    'refilled_via_scanner' => true,
                    'scanner_session_id' => session()->getId(),
                ]
            );

            $this->refillSuccess = $result['message'];

            // Reset everything back to scanner after successful transfer
            $this->resetScan();

            // Auto-resume scanning for next item
            $this->isScanning = true;
            $this->dispatch('camera-state-changed', true);

        } catch (ValidationException $e) {
            $this->isProcessingRefill = false;
            throw $e;
        } catch (\Exception $e) {
            $this->refillError = $e->getMessage();
            $this->isProcessingRefill = false;

            Log::channel('inventory')->error('Refill submission failed', [
                'product_sku' => $this->product?->sku,
                'location_id' => $this->selectedLocationId,
                'quantity' => $this->refillQuantity,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Clear refill error message
     */
    public function clearRefillError(): void
    {
        $this->refillError = '';
    }

    /**
     * Increment refill quantity
     */
    public function incrementRefillQuantity(): void
    {
        $selectedLocation = collect($this->availableLocations)->first(function ($location, $index) {
            $locationId = $location['Location']['StockLocationId'] ?? $location['LocationId'] ?? $location['locationId'] ?? $location['id'] ?? $index;

            return $locationId == $this->selectedLocationId;
        });

        if (! $selectedLocation) {
            return;
        }

        $maxStock = $selectedLocation['StockLevel'] ?? $selectedLocation['stockLevel'] ?? $selectedLocation['stock'] ?? 0;

        if ($this->refillQuantity < $maxStock) {
            $this->refillQuantity++;
        }
    }

    /**
     * Decrement refill quantity
     */
    public function decrementRefillQuantity(): void
    {
        if ($this->refillQuantity > 1) {
            $this->refillQuantity--;
        }
    }

    /**
     * Handle auto-submit functionality (future implementation)
     * This method will automatically submit scans when auto-submit is enabled
     */
    private function handleAutoSubmit(): void
    {
        // TODO: Implement auto-submit logic
        // This should:
        // 1. Validate the current scan data
        // 2. Use default quantity (1) and action (decrease)
        // 3. Call the save() method automatically
        // 4. Show a brief confirmation message
        // 5. Auto-resume scanning for next item
        
        // For now, this is just a placeholder structure
        // When implementing, consider:
        // - User feedback (brief notification that scan was auto-submitted)
        // - Error handling for failed auto-submissions
        // - Option to undo last auto-submission
        // - Integration with sound feedback
        
        \Log::info('Auto-submit triggered', [
            'barcode' => $this->barcode,
            'product_found' => !!$this->product,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.product-scanner');
    }
}
