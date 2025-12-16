<?php

namespace App\Livewire\Scanner\Concerns;

use App\Actions\Scanner\AutoSubmitScanAction;
use App\Actions\Scanner\ProcessBarcodeAction;
use App\Actions\Scanner\ResetScanStateAction;
use App\Services\Scanner\CameraManagerService;
use App\Services\Scanner\UserFeedbackService;

trait InteractsWithScannerServices
{
    /**
     * Get the reset scan state action service
     */
    private function resetScanStateAction(): ResetScanStateAction
    {
        return app(ResetScanStateAction::class);
    }

    /**
     * Get the camera manager service
     */
    private function cameraManagerService(): CameraManagerService
    {
        return app(CameraManagerService::class);
    }

    /**
     * Get the process barcode action service
     */
    private function processBarcodeAction(): ProcessBarcodeAction
    {
        return app(ProcessBarcodeAction::class);
    }

    /**
     * Get the user feedback service
     */
    private function userFeedbackService(): UserFeedbackService
    {
        return app(UserFeedbackService::class);
    }

    /**
     * Get the auto-submit scan action service
     */
    private function autoSubmitScanAction(): AutoSubmitScanAction
    {
        return app(AutoSubmitScanAction::class);
    }
}
