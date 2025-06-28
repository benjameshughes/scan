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
    #[Validate(['required', new BarcodePrefixCheck('505903')])]
    public ?int $barcode = null;
    #[Validate('required|min:1')]
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
            $this->barcodeScanned = true;
            $this->cameraError = '';

            if ($this->validate()) {
                $this->product = (new GetProductFromScannedBarcode($this->barcode))->handle();
                $this->successMessage = $this->product ? $this->product->name : "No Product Found With That Barcode";
                $this->showSuccessMessage = true;
            }
        }
    }

    // Camera controls - Livewire handles state, dispatches to JS
    public function toggleCamera()
    {
        $this->isScanning = !$this->isScanning;
        $this->dispatch('camera-state-changed', $this->isScanning);
        
        if (!$this->isScanning) {
            $this->cameraError = '';
            $this->isTorchOn = false; // Turn off torch when camera stops
        }
    }

    public function toggleTorch()
    {
        if (!$this->torchSupported) {
            $this->cameraError = 'Torch not supported on this device';
            return;
        }
        
        $this->isTorchOn = !$this->isTorchOn;
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
        
        if (!$supported) {
            $this->isTorchOn = false;
        }
    }

    #[On('onTorchStateChanged')]
    public function onTorchStateChanged($enabled)
    {
        $this->isTorchOn = $enabled;
    }

    #[On('onBarcodeDetected')]
    public function onBarcodeDetected($barcodeData)
    {
        $this->barcode = $barcodeData['text'];
        $this->barcodeScanned = true;
        $this->cameraError = '';

        // Keep camera running but pause scanning
        // JS handles pausing ZXing, we just update UI state
        $this->isScanning = false;

        if ($this->validate()) {
            $this->product = (new GetProductFromScannedBarcode($this->barcode))->handle();
            $this->successMessage = $this->product ? $this->product->name : "No Product Found With That Barcode";
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
        $this->dispatch('resume-scanning');
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
        $this->validate();

        $scan = Scan::create([
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'submitted' => false,
            'scanAction' => $this->scanAction,
            'sync_status' => 'pending',
            'user_id' => auth()->check() ? auth()->user()->id : '1',
        ]);

        SyncBarcode::dispatch($scan);
        Log::channel('barcode')->info("{$this->barcode} Scanned");

        // Show success message and reset for next scan
        $this->successMessage = "Scan saved successfully! Ready for next item.";
        $this->showSuccessMessage = true;
        
        // Reset form but keep success message briefly
        $this->resetScan();
        
        // Auto-resume scanning for next item
        $this->isScanning = true;
        $this->dispatch('resume-scanning');
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