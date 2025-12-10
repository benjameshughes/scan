<?php

namespace App\Livewire\Scanner;

/**
 * Scanner event constants for documentation and type safety
 */
final class ScannerEvents
{
    // Camera lifecycle events (from JS)
    public const CAMERA_INITIALIZING = 'onCameraInitializing';

    public const CAMERA_READY = 'onCameraReady';

    public const CAMERA_ERROR = 'onCameraError';

    public const BARCODE_DETECTED = 'onBarcodeDetected';

    // Torch events (from JS)
    public const TORCH_SUPPORT_DETECTED = 'onTorchSupportDetected';

    public const TORCH_STATE_CHANGED = 'onTorchStateChanged';

    // Component communication events
    public const BARCODE_PROCESSED = 'barcode-processed';

    public const SCAN_SUBMITTED = 'scan-submitted';

    public const REFILL_FORM_REQUESTED = 'refill-form-requested';

    public const REFILL_SUBMITTED = 'refill-submitted';

    public const REFILL_CANCELLED = 'refill-cancelled';

    public const EMPTY_BAY_SUBMITTED = 'empty-bay-submitted';

    public const EMPTY_BAY_CLOSED = 'empty-bay-closed';

    public const NEW_SCAN_REQUESTED = 'new-scan-requested';

    // Camera control events (to JS)
    public const CAMERA_STATE_CHANGED = 'camera-state-changed';

    public const TORCH_STATE_CHANGE_REQUESTED = 'torch-state-changed';

    public const RESUME_SCANNING = 'resume-scanning';

    // Feedback events
    public const PLAY_SUCCESS_SOUND = 'play-success-sound';

    public const TRIGGER_VIBRATION = 'trigger-vibration';
}
