<?php

namespace App\Livewire\Scanner\Contracts;

/**
 * Contract defining the core public interface for scanner components
 *
 * This interface ensures that scanner components implement the essential
 * methods required for camera control, barcode handling, and child component
 * event communication.
 */
interface ScannerComponentContract
{
    /**
     * Handle camera toggle request from UI
     */
    public function onCameraToggleRequested(): void;

    /**
     * Handle torch toggle request from UI
     */
    public function onTorchToggleRequested(): void;

    /**
     * Handle barcode detection from camera
     */
    public function handleBarcodeDetected(string $barcode): void;

    /**
     * Handle scan submission from child component
     */
    public function onScanSubmitted(): void;

    /**
     * Handle refill form request from child component
     */
    public function onRefillFormRequested(): void;

    /**
     * Handle new scan request from child component
     */
    public function onNewScanRequested(): void;
}
