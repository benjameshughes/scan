<?php

namespace App\Livewire\Scanner;

use App\Actions\Scanner\ResetContext;
use App\Livewire\Scanner\Concerns\HasAutoSubmit;
use App\Livewire\Scanner\Concerns\HasCameraState;
use App\Livewire\Scanner\Concerns\HasChildComponentEvents;
use App\Livewire\Scanner\Concerns\HasScanState;
use App\Livewire\Scanner\Concerns\InteractsWithScannerServices;
use App\Livewire\Scanner\Contracts\ScannerComponentContract;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Scanner')]
class ProductScanner extends Component implements ScannerComponentContract
{
    use HasAutoSubmit;
    use HasCameraState;
    use HasChildComponentEvents;
    use HasScanState;
    use InteractsWithScannerServices;

    public function mount(): void
    {
        // Ensure user is authenticated and has scanner permission
        if (! auth()->check()) {
            abort(401, 'Authentication required');
        }

        if (! auth()->user()->can('view scanner')) {
            abort(403, 'Insufficient permissions to use scanner');
        }

        // Initialize scanner state
        $this->initializeScannerState();
    }

    /**
     * Initialize the scanner state with default values
     */
    protected function initializeScannerState(): void
    {
        // Initialize state using the reset action
        $initialState = $this->resetScanStateAction()->reset(ResetContext::Initial);
        $this->applyStateArray($initialState);

        // Load user settings for auto-submit
        $userSettings = auth()->user()->settings;
        $this->autoSubmitEnabled = $userSettings['auto_submit'] ?? false;
    }

    public function render()
    {
        return view('livewire.scanner.product-scanner');
    }
}
