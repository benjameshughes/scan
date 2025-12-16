<?php

namespace App\Livewire\Scanner\Concerns;

use App\Actions\Scanner\ResetContext;
use Livewire\Attributes\On;

/**
 * Manages auto-submit functionality
 *
 * This trait handles automatic scan submission when auto-submit is enabled.
 * It creates scan records with default values and provides visual feedback
 * before resetting for the next scan.
 */
trait HasAutoSubmit
{
    use InteractsWithScannerServices;

    // Auto-submit state properties
    public bool $autoSubmitEnabled = false;

    public bool $autoSubmitInProgress = false;

    /**
     * Handle auto-submit functionality
     *
     * This method automatically submits scans when auto-submit is enabled.
     * It creates a scan record with default values (quantity: 1, action: decrease)
     * and provides brief visual feedback before resetting for the next scan.
     */
    protected function handleAutoSubmit(): void
    {
        if (! $this->product || $this->autoSubmitInProgress) {
            return;
        }

        $this->autoSubmitInProgress = true;

        try {
            // Use the auto-submit action to create the scan
            $result = $this->autoSubmitScanAction()->handle(
                product: $this->product,
                barcode: $this->barcode,
                userId: auth()->id()
            );

            if ($result['success']) {
                // Dispatch success notification to frontend
                $this->dispatch('auto-submit-success', [
                    'scan_id' => $result['scan_id'],
                    'product_name' => $this->product->name,
                ]);

                // Brief delay to show product info before resetting (handled by JavaScript)
                // After 1.5 seconds, JS will trigger reset and restart camera
                $this->dispatch('schedule-auto-submit-reset', [
                    'delay' => 1500, // milliseconds
                ]);
            } else {
                // Auto-submit failed - show error and don't reset
                $this->cameraError = $result['message'];
                $this->autoSubmitInProgress = false;
            }

        } catch (\Exception $e) {
            $this->cameraError = 'Auto-submit failed: '.$e->getMessage();
            $this->autoSubmitInProgress = false;
        }
    }

    /**
     * Reset after auto-submit (called by JavaScript after delay)
     */
    #[On('auto-submit-reset-complete')]
    public function onAutoSubmitResetComplete(): void
    {
        // Reset state and restart camera for next scan
        $resetState = $this->resetScanStateAction()->reset(ResetContext::AfterSubmission);
        $this->resetAfterScanSubmission();
        $this->applyStateArray($resetState);

        // Reset auto-submit flag
        $this->autoSubmitInProgress = false;

        // Dispatch camera start event
        if (isset($resetState['dispatchEvent'])) {
            $this->dispatch(...$resetState['dispatchEvent']);
        }
    }
}
