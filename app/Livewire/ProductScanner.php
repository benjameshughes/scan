<?php

namespace App\Livewire;

use App\Actions\GetProductFromScannedBarcode;
use App\DTOs\EmptyBayDTO;
use App\Jobs\EmptyBayJob;
use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use App\Rules\BarcodePrefixCheck;
use App\Services\LinnworksApiService;
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
    public ?int $barcode = null;

    #[Validate('required|integer|min:1')]
    public int $quantity = 1;

    public bool $barcodeScanned = false;

    public bool $showSuccessMessage = false;

    public string $successMessage = '';

    public bool $scanAction = false;

    public ?Product $product = null;

    // Refill bay state
    public bool $showRefillForm = false;
    
    #[Validate('required')]
    public string $selectedLocationId = '';
    
    #[Validate('required|integer|min:1')]
    public int $refillQuantity = 1;
    
    public array $availableLocations = [];
    
    public bool $isProcessingRefill = false;
    
    public string $refillError = '';
    
    public string $refillSuccess = '';

    public function mount()
    {
        // Ensure user is authenticated and has scanner permission
        if (!auth()->check()) {
            abort(401, 'Authentication required');
        }
        
        if (!auth()->user()->can('view scanner')) {
            abort(403, 'Insufficient permissions to use scanner');
        }
        
        $this->loadingCamera = false; // Start with video element visible
        $this->isScanning = false;
    }

    public function updatedBarcode()
    {
        if ($this->barcode) {
            $this->cameraError = '';

            // Validate just the barcode field with prefix check (no required rule needed here)
            try {
                $this->validateOnly('barcode');
                $this->product = (new GetProductFromScannedBarcode($this->barcode))->handle();

                if ($this->product) {
                    // Product found - stop camera and switch to product view
                    $this->barcodeScanned = true;
                    $this->isScanning = false;
                    $this->dispatch('camera-state-changed', false); // Stop camera
                    $this->successMessage = $this->product->name ?? 'Product Found';
                    $this->showSuccessMessage = true;
                } else {
                    // Valid barcode but no product found - keep scanning
                    $this->barcodeScanned = false;
                    $this->successMessage = 'No Product Found With That Barcode';
                    $this->showSuccessMessage = true;
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Invalid barcode - keep scanning, don't switch view
                $this->barcodeScanned = false;
                $this->product = null;
                $this->showSuccessMessage = false;
                $this->successMessage = '';
            }
        } else {
            // Barcode was cleared - reset the scan state
            $this->barcodeScanned = false;
            $this->product = null;
            $this->showSuccessMessage = false;
            $this->successMessage = '';
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

        if ($this->validate()) {
            $this->product = new GetProductFromScannedBarcode($this->barcode)->handle();
            $this->successMessage = $this->product ? ($this->product->name ?? 'Product Found') : 'No Product Found With That Barcode';
            $this->showSuccessMessage = true;
        }
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
        $this->showSuccessMessage = false;
        $this->successMessage = '';
        $this->product = null;
        $this->quantity = 1;
        $this->cameraError = '';
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

        $this->showSuccessMessage = true;
        $this->successMessage = 'Empty bay notification sent';
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

        // Then show success message for next scan
        $this->successMessage = 'Scan saved successfully! Ready for next item.';
        $this->showSuccessMessage = true;

        // Auto-resume scanning for next item
        $this->isScanning = true;
        $this->dispatch('camera-state-changed', true); // Start camera
    }

    public function clearError()
    {
        $this->cameraError = '';
    }

    /**
     * Show the refill bay form for the current product
     */
    public function showRefillBayForm(): void
    {
        // Check permission
        if (!auth()->user()->can('refill bays')) {
            $this->refillError = 'You do not have permission to refill bays.';
            return;
        }

        if (!$this->product) {
            $this->refillError = 'No product selected for refill.';
            return;
        }

        try {
            $this->isProcessingRefill = true;
            $this->refillError = '';
            
            $linnworksService = app(LinnworksApiService::class);
            $locations = $linnworksService->getStockLocationsByProduct($this->product->sku);
            
            // Debug: Log the actual structure we're getting
            Log::channel('inventory')->info('Raw locations data structure', [
                'product_sku' => $this->product->sku,
                'locations_count' => count($locations),
                'sample_location' => !empty($locations) ? $locations[0] : 'no locations',
                'all_locations' => $locations
            ]);
            
            if (empty($locations)) {
                $this->refillError = 'No locations with stock found for this product.';
                $this->isProcessingRefill = false;
                return;
            }

            $this->availableLocations = $locations;
            
            // Auto-select if only one location besides default
            $defaultLocationId = config('linnworks.default_location_id');
            $nonDefaultLocations = array_filter($locations, function($location) use ($defaultLocationId) {
                $locationId = $location['Location']['StockLocationId'] ?? null;
                return $locationId !== $defaultLocationId;
            });
            
            if (count($nonDefaultLocations) === 1) {
                $singleLocation = array_values($nonDefaultLocations)[0];
                $this->selectedLocationId = $singleLocation['Location']['StockLocationId'] ?? '';
                
                Log::channel('inventory')->info('Auto-selected single location', [
                    'product_sku' => $this->product->sku,
                    'auto_selected_location' => $this->selectedLocationId,
                    'location_name' => $singleLocation['Location']['LocationName'] ?? 'Unknown'
                ]);
            }
            
            $this->showRefillForm = true;
            $this->isProcessingRefill = false;
            
            Log::channel('inventory')->info('Refill form opened', [
                'product_sku' => $this->product->sku,
                'locations_count' => count($locations),
                'non_default_locations' => count($nonDefaultLocations),
                'auto_selected' => count($nonDefaultLocations) === 1,
                'user_id' => auth()->id()
            ]);
            
        } catch (\Exception $e) {
            $this->refillError = "Failed to load locations: {$e->getMessage()}";
            $this->isProcessingRefill = false;
            
            Log::channel('inventory')->error('Failed to load refill locations', [
                'product_sku' => $this->product?->sku,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
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
            // Validate form data
            $this->validate([
                'selectedLocationId' => 'required',
                'refillQuantity' => 'required|integer|min:1'
            ]);

            // Check permission again
            if (!auth()->user()->can('refill bays')) {
                throw new ValidationException(validator([], []), 'You do not have permission to refill bays.');
            }

            $this->isProcessingRefill = true;
            $this->refillError = '';

            // Find the selected location details
            $selectedLocation = collect($this->availableLocations)->first(function($location, $index) {
                $locationId = $location['Location']['StockLocationId'] ?? $location['LocationId'] ?? $location['locationId'] ?? $location['id'] ?? $index;
                return $locationId == $this->selectedLocationId;
            });
            
            if (!$selectedLocation) {
                throw new \Exception('Selected location not found.');
            }

            $currentStock = $selectedLocation['StockLevel'] ?? $selectedLocation['stockLevel'] ?? $selectedLocation['stock'] ?? 0;
            
            // Validate we have enough stock
            if ($this->refillQuantity > $currentStock) {
                throw new \Exception("Cannot refill {$this->refillQuantity} units. Only {$currentStock} available at this location.");
            }

            // Show confirmation details before processing
            $locationId = $selectedLocation['Location']['StockLocationId'] ?? $selectedLocation['LocationId'] ?? $selectedLocation['locationId'] ?? $selectedLocation['id'] ?? 'Unknown';
            $locationName = $selectedLocation['Location']['LocationName'] ?? $selectedLocation['LocationName'] ?? $selectedLocation['locationName'] ?? $selectedLocation['name'] ?? "Location {$locationId}";
            $confirmationMessage = "Transfer {$this->refillQuantity} units of {$this->product->sku} from {$locationName} to main bay?";
            
            // For now, we'll auto-confirm. In the future, you could add a confirmation step
            $this->processRefillConfirmation($selectedLocation, $confirmationMessage);
            
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
                'user_id' => auth()->id()
            ]);
        }
    }

    /**
     * Process the confirmed refill operation
     */
    private function processRefillConfirmation(array $selectedLocation, string $confirmationMessage): void
    {
        try {
            $linnworksService = app(LinnworksApiService::class);
            
            // Log what we're about to send to the API
            Log::channel('inventory')->info('About to transfer stock to main bay', [
                'product_sku' => $this->product->sku,
                'source_location_id' => $this->selectedLocationId,
                'transfer_quantity' => $this->refillQuantity,
                'selected_location_data' => $selectedLocation
            ]);

            // Transfer stock from source location to default location (bay refill)
            $response = $linnworksService->transferStockToDefaultLocation(
                $this->product->sku,
                $this->selectedLocationId,
                $this->refillQuantity
            );

            $locationId = $selectedLocation['Location']['StockLocationId'] ?? $selectedLocation['LocationId'] ?? $selectedLocation['locationId'] ?? $selectedLocation['id'] ?? 'Unknown';
            $locationName = $selectedLocation['Location']['LocationName'] ?? $selectedLocation['LocationName'] ?? $selectedLocation['locationName'] ?? $selectedLocation['name'] ?? "Location {$locationId}";
            
            $this->refillSuccess = "Successfully transferred {$this->refillQuantity} units from {$locationName} to the main bay.";
            
            Log::channel('inventory')->info('Bay refill transfer completed', [
                'product_sku' => $this->product->sku,
                'source_location_id' => $this->selectedLocationId,
                'source_location_name' => $locationName,
                'quantity_transferred' => $this->refillQuantity,
                'source_stock_before' => $selectedLocation['StockLevel'] ?? $selectedLocation['stockLevel'] ?? $selectedLocation['stock'] ?? 0,
                'user_id' => auth()->id(),
                'linnworks_response' => $response
            ]);

            // Reset everything back to scanner after successful transfer
            $this->resetScan();
            
            // Show success message and restart scanning
            $this->showSuccessMessage = true;
            $this->successMessage = $this->refillSuccess;
            
            // Auto-resume scanning for next item
            $this->isScanning = true;
            $this->dispatch('camera-state-changed', true);
            
        } catch (\Exception $e) {
            $this->refillError = "Refill failed: {$e->getMessage()}";
            $this->isProcessingRefill = false;
            
            Log::channel('inventory')->error('Stock transfer failed', [
                'product_sku' => $this->product->sku,
                'source_location_id' => $this->selectedLocationId,
                'transfer_quantity' => $this->refillQuantity,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
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
        $selectedLocation = collect($this->availableLocations)->first(function($location, $index) {
            $locationId = $location['Location']['StockLocationId'] ?? $location['LocationId'] ?? $location['locationId'] ?? $location['id'] ?? $index;
            return $locationId == $this->selectedLocationId;
        });
        
        if (!$selectedLocation) {
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

    public function render()
    {
        return view('livewire.product-scanner');
    }
}
