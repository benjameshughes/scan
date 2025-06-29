<?php

namespace App\Livewire;

use App\Actions\GetProductFromScannedBarcode;
use App\DTOs\EmptyBayDTO;
use App\Jobs\EmptyBayJob;
use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use App\Rules\BarcodePrefixCheck;
use Illuminate\Support\Facades\Log;
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

    public function mount()
    {
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
        $this->resetValidation();
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
            'user_id' => auth()->check() ? auth()->user()->id : '1',
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

    public function render()
    {
        return view('livewire.product-scanner');
    }
}
