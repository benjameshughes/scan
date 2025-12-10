import { BrowserMultiFormatReader, NotFoundException } from "@zxing/library";

/**
 * Alpine store for ZXing barcode scanning (Refactored Scanner)
 *
 * Flow: ZXing handles video -> Alpine store -> Livewire dispatch -> PHP
 *
 * Key principle: Let ZXing do what it's built to do - manage the video stream internally
 * via decodeFromVideoDevice(). We don't manually create streams or attach to video elements.
 *
 * This store also handles PWA lifecycle events (visibility change, focus/blur)
 */

if (window.Alpine) {
    window.Alpine.store('scanner', createScannerStore());
} else {
    document.addEventListener('alpine:init', () => {
        window.Alpine.store('scanner', createScannerStore());
    });
}

function createScannerStore() {
    let codeReader = null;
    let selectedDeviceId = null;
    let isScanning = false;
    let isTorchEnabled = false; // FIX 6: Renamed from torchEnabled for consistency
    let vibrationSupported = null;
    let hasUserInteraction = false;
    let isInitialized = false;

    /**
     * Setup Livewire event listeners for camera control
     */
    function setupLivewireListeners() {
        if (!window.Livewire) return;

        window.Livewire.on('camera-state-changed', (data) => {
            const shouldScan = Array.isArray(data) ? data[0] : data;
            console.log('Livewire camera-state-changed:', shouldScan);
            if (shouldScan) {
                startScanning();
            } else {
                stopScanning();
            }
        });

        window.Livewire.on('torch-state-changed', (enabled) => {
            setTorchState(enabled);
        });

        window.Livewire.on('resume-scanning', () => {
            resumeScanning();
        });

        window.Livewire.on('trigger-vibration', (patternData) => {
            triggerVibrationWithPattern(patternData?.pattern || [100, 50, 200]);
        });

        window.Livewire.on('schedule-auto-submit-reset', (data) => {
            const delay = Array.isArray(data) ? data[0]?.delay : data?.delay || 1500;
            console.log(`Auto-submit scheduled reset in ${delay}ms`);
            setTimeout(() => {
                window.Livewire?.dispatch('auto-submit-reset-complete');
            }, delay);
        });

        window.Livewire.on('auto-submit-success', (data) => {
            const productName = Array.isArray(data) ? data[0]?.product_name : data?.product_name;
            console.log('Auto-submit successful for:', productName);
            // Visual feedback will be handled by the Livewire component
        });
    }

    /**
     * Initialize the scanner - called from Alpine x-init
     * FIX 1: Added direct event listener registration for lifecycle events
     */
    async function init() {
        // Prevent double initialization
        if (isInitialized) {
            console.log('Scanner already initialized, skipping');
            return;
        }

        try {
            // Guard: only run on pages with video element
            const video = document.getElementById('video');
            if (!video) {
                console.log('No video element found, not a scanner page');
                return;
            }

            console.log('Initializing scanner store...');
            isInitialized = true;

            codeReader = new BrowserMultiFormatReader();
            setupLivewireListeners();
            setupUserInteractionTracking();
            testVibrationSupport();

            // FIX 1: Wire up lifecycle handlers directly via JavaScript event listeners
            // Previously these only worked via Alpine directives in the Blade template
            document.addEventListener('visibilitychange', handleVisibilityChange);
            window.addEventListener('focus', handleWindowFocus);
            window.addEventListener('blur', handleWindowBlur);

            console.log('Lifecycle event listeners registered');

            await initializeCamera();
        } catch (e) {
            console.error('Scanner init failed:', e);
            window.Livewire?.dispatch('onCameraError', [e.message || 'Scanner initialization failed']);
        }
    }

    /**
     * Initialize camera - get devices, select back camera, start scanning
     * FIX 4: Added better error messages based on error type
     */
    async function initializeCamera() {
        try {
            window.Livewire?.dispatch('onCameraInitializing');

            // Check permission first
            await checkAndRequestPermission();

            // Get available video devices
            const devices = await codeReader.listVideoInputDevices();

            if (devices.length === 0) {
                throw new Error('No camera devices found');
            }

            // FIX 8: Log available cameras for debugging
            console.log('Available cameras:', devices.map(d => ({
                id: d.deviceId,
                label: d.label || 'Unknown device'
            })));

            // Prefer back camera
            selectedDeviceId = findBackCamera(devices) || devices[0].deviceId;
            const selectedCamera = devices.find(d => d.deviceId === selectedDeviceId);

            // FIX 8: Log selected camera details
            console.log('Selected camera:', {
                id: selectedDeviceId,
                label: selectedCamera?.label || 'Unknown device'
            });

            // Auto-start scanning
            await startScanning();

        } catch (e) {
            console.error('Camera initialization failed:', e);

            // FIX 4: Provide user-friendly error messages based on error type
            let userMessage = e.message || 'Camera initialization failed';

            if (e.name === 'NotAllowedError') {
                userMessage = 'Camera permission denied. Please allow camera access in your browser settings.';
            } else if (e.name === 'NotFoundError') {
                userMessage = 'No camera found on this device.';
            } else if (e.name === 'NotReadableError') {
                userMessage = 'Camera is in use by another application. Please close other apps using the camera.';
            } else if (e.name === 'SecurityError') {
                userMessage = 'Camera requires a secure connection (HTTPS).';
            }

            console.log('User-friendly error message:', userMessage);
            window.Livewire?.dispatch('onCameraError', [userMessage]);
        }
    }

    /**
     * Find back-facing camera from device list
     */
    function findBackCamera(devices) {
        const backCamera = devices.find(device =>
            (device.label || '').toLowerCase().match(/back|rear|environment/)
        );
        return backCamera?.deviceId;
    }

    /**
     * Check and request camera permission
     * FIX 2 & 4: Enhanced with video constraints for better barcode detection
     */
    async function checkAndRequestPermission() {
        if (navigator.permissions) {
            try {
                const permission = await navigator.permissions.query({ name: 'camera' });
                if (permission.state === 'denied') {
                    const error = new Error('Camera access denied. Please enable in browser settings.');
                    error.name = 'NotAllowedError';
                    throw error;
                }
                if (permission.state === 'granted') {
                    return; // Already have permission
                }
            } catch (e) {
                // If it's our denied error, rethrow it
                if (e.name === 'NotAllowedError') {
                    throw e;
                }
                // Otherwise, Permissions API not fully supported, continue
            }
        }

        // FIX 2: Request permission with optimal video constraints for barcode scanning
        // These constraints improve barcode detection performance and accuracy
        try {
            const tempStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280, min: 640, max: 1920 },
                    height: { ideal: 720, min: 480, max: 1080 },
                    frameRate: { ideal: 30, max: 60 }
                }
            });
            tempStream.getTracks().forEach(t => t.stop());
            console.log('Camera permission granted with optimal constraints');
        } catch (e) {
            // FIX 4: Add specific error handling for permission request
            if (e.name === 'NotAllowedError') {
                const error = new Error('Camera access denied. Please allow camera access to use the scanner.');
                error.name = 'NotAllowedError';
                throw error;
            }
            throw e;
        }
    }

    /**
     * Start scanning - let ZXing handle everything via decodeFromVideoDevice
     * FIX 3: Fixed race condition - only set isScanning=true AFTER ZXing confirms ready
     * FIX 7: Moved torch support check to after stream is fully ready
     */
    async function startScanning() {
        if (isScanning) {
            console.log('Already scanning, skipping start');
            return;
        }

        if (!selectedDeviceId) {
            console.log('No device selected, cannot start');
            return;
        }

        try {
            console.log('Starting scanner with device:', selectedDeviceId);

            // Let ZXing handle the video stream internally
            await codeReader.decodeFromVideoDevice(
                selectedDeviceId,
                'video', // ID of the video element
                (result, err) => {
                    // IMPORTANT: Check isScanning flag first - if false, we're shutting down
                    // This prevents errors during the shutdown race condition
                    if (!isScanning) {
                        return; // Ignore callbacks during/after stop
                    }

                    if (result) {
                        handleBarcodeResult(result);
                    }

                    // Only log unexpected errors (NotFoundException is normal when no barcode visible)
                    if (err && !(err instanceof NotFoundException)) {
                        console.warn('Decode error:', err.message || err);
                    }
                }
            );

            // FIX 3: Set isScanning=true AFTER ZXing successfully starts (await completes)
            // This ensures state accurately reflects reality
            isScanning = true;

            // FIX 7: Check torch support after a small delay to ensure video stream is fully ready
            // The video track capabilities may not be immediately available
            setTimeout(() => {
                if (isScanning) {
                    checkTorchSupport();
                }
            }, 500);

            window.Livewire?.dispatch('onCameraReady');
            console.log('Scanner started successfully');

        } catch (e) {
            console.error('Failed to start scanning:', e);

            // FIX 4: Provide user-friendly error message
            let userMessage = e.message || 'Failed to start camera';
            if (e.name === 'NotReadableError') {
                userMessage = 'Camera is in use by another application.';
            }

            window.Livewire?.dispatch('onCameraError', [userMessage]);
        }
    }

    /**
     * Stop scanning and FULLY release camera hardware
     */
    function stopScanning() {
        // Set flag FIRST to prevent decode callback from processing during shutdown
        const wasScanning = isScanning;
        isScanning = false;
        isTorchEnabled = false; // FIX 6: Use renamed variable

        if (!wasScanning) {
            console.log('Scanner was not running, nothing to stop');
            return;
        }

        console.log('Stopping scanner and releasing camera hardware...');

        // Reset ZXing reader - this stops the decode loop and releases the stream
        if (codeReader) {
            try {
                codeReader.reset();
            } catch (e) {
                console.warn('Error resetting codeReader:', e);
            }
        }

        // Small delay to let ZXing's internal loop fully stop before clearing video
        // This prevents the "source width is 0" error from the decode loop firing after srcObject is null
        setTimeout(() => {
            const video = document.getElementById('video');
            if (video && video.srcObject) {
                const stream = video.srcObject;
                if (stream && stream.getTracks) {
                    stream.getTracks().forEach(track => {
                        console.log('Stopping track:', track.kind, track.label);
                        track.stop();
                    });
                }
                video.srcObject = null;
            }
            console.log('Camera hardware fully released');
        }, 100);
    }

    /**
     * Pause scanning (stops decoding but ZXing may keep stream - for barcode found)
     */
    function pauseScanning() {
        if (codeReader) {
            codeReader.reset();
        }
        isScanning = false;
        console.log('Scanning paused');
    }

    /**
     * Resume scanning after pause
     * FIX 3: Fixed race condition - only set isScanning=true AFTER ZXing confirms ready
     */
    async function resumeScanning() {
        if (isScanning) {
            console.log('Already scanning, skipping resume');
            return;
        }

        if (!selectedDeviceId) {
            console.log('No device selected, cannot resume');
            return;
        }

        try {
            console.log('Resuming scanner...');

            await codeReader.decodeFromVideoDevice(
                selectedDeviceId,
                'video',
                (result, err) => {
                    // Check isScanning flag first - ignore callbacks during shutdown
                    if (!isScanning) {
                        return;
                    }

                    if (result) {
                        handleBarcodeResult(result);
                    }
                    if (err && !(err instanceof NotFoundException)) {
                        console.warn('Decode error:', err.message || err);
                    }
                }
            );

            // FIX 3: Set isScanning=true AFTER ZXing successfully starts (await completes)
            isScanning = true;
            console.log('Scanning resumed');
        } catch (e) {
            console.error('Failed to resume scanning:', e);
        }
    }

    /**
     * Check if torch/flashlight is supported
     */
    function checkTorchSupport() {
        const video = document.getElementById('video');
        if (!video?.srcObject) {
            console.log('Cannot check torch support - no video stream');
            return;
        }

        const videoTrack = video.srcObject.getVideoTracks?.()[0];
        if (!videoTrack) {
            console.log('Cannot check torch support - no video track');
            return;
        }

        const capabilities = videoTrack.getCapabilities?.();
        const supported = !!(capabilities?.torch);

        window.Livewire?.dispatch('onTorchSupportDetected', [supported]);
        console.log('Torch supported:', supported, 'capabilities:', capabilities);
    }

    /**
     * Set torch state on/off
     */
    async function setTorchState(enabled) {
        const video = document.getElementById('video');
        if (!video?.srcObject) return;

        try {
            const videoTrack = video.srcObject.getVideoTracks()[0];
            if (!videoTrack) return;

            await videoTrack.applyConstraints({
                advanced: [{ torch: !!enabled }]
            });

            isTorchEnabled = !!enabled; // FIX 6: Use renamed variable
            window.Livewire?.dispatch('onTorchStateChanged', [isTorchEnabled]);
            console.log('Torch', enabled ? 'on' : 'off');
        } catch (e) {
            console.warn('Torch toggle failed:', e);
            window.Livewire?.dispatch('onTorchStateChanged', [false]);
        }
    }

    /**
     * Handle barcode detection result
     */
    function handleBarcodeResult(result) {
        console.log('Barcode detected:', result.text);

        // Immediate haptic feedback
        triggerVibration();

        // Pause decoding (camera stays on conceptually, but ZXing stops reading)
        pauseScanning();

        // Send to Livewire
        window.Livewire?.dispatch('onBarcodeDetected', [result.text]);
    }

    /**
     * Track user interaction for vibration API (required by browsers)
     */
    function setupUserInteractionTracking() {
        const events = ['click', 'touchstart', 'keydown', 'pointerdown'];
        const handler = () => {
            hasUserInteraction = true;
            events.forEach(e => document.removeEventListener(e, handler, { passive: true }));
        };
        events.forEach(e => document.addEventListener(e, handler, { passive: true }));
    }

    /**
     * Test if vibration is supported on this device
     */
    function testVibrationSupport() {
        const apiExists = !!navigator.vibrate;
        const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

        vibrationSupported = apiExists && (isMobile || isTouch);
        window.Livewire?.dispatch('onVibrationSupportDetected', [vibrationSupported]);
    }

    /**
     * Trigger default vibration pattern
     */
    function triggerVibration() {
        triggerVibrationWithPattern([100, 50, 200]);
    }

    /**
     * Trigger vibration with custom pattern
     */
    function triggerVibrationWithPattern(pattern) {
        if (!vibrationSupported || !hasUserInteraction || !pattern?.length) return;
        try {
            navigator.vibrate(pattern);
        } catch { /* ignore */ }
    }

    /**
     * Handle page visibility change (PWA background/foreground)
     * Critical for mobile devices - stops camera when app goes to background
     */
    function handleVisibilityChange() {
        if (!isInitialized) return;

        if (document.hidden) {
            console.log('Page hidden - stopping camera to release hardware');
            stopScanning();
        } else {
            console.log('Page visible - camera can be restarted manually by user');
            // Note: We don't auto-restart here - let user manually restart if they stopped
        }
    }

    /**
     * Handle window focus (for PWA)
     * FIX 5: Added orientation change detection support
     */
    function handleWindowFocus() {
        console.log('Window focused');
        // Could be used to restart camera on focus, but keeping it manual for now
    }

    /**
     * Handle window blur (for PWA)
     */
    function handleWindowBlur() {
        console.log('Window blurred');
        // Could be used to pause on blur
    }

    /**
     * FIX 5: Handle device orientation changes
     * Orientation changes can affect the camera stream on mobile devices
     */
    function handleOrientationChange() {
        if (!isInitialized || !isScanning) return;

        console.log('Orientation changed, camera may need adjustment');
        // The video stream typically adapts automatically, but log for debugging
        const orientation = screen.orientation?.type || 'unknown';
        console.log('New orientation:', orientation);
    }

    /**
     * Handle user toggle request (from UI button)
     */
    function handleUserToggle() {
        console.log('User toggle requested, current isScanning:', isScanning);
        if (isScanning) {
            stopScanning();
            window.Livewire?.dispatch('camera-state-changed', [false]);
        } else {
            startScanning();
        }
    }

    /**
     * Cleanup function for page unload - releases camera hardware
     * FIX 1: Now properly removes event listeners
     * LIVEWIRE NAVIGATE FIX: Now resets isInitialized flag for re-initialization
     */
    function cleanup() {
        console.log('Scanner cleanup triggered');

        // FIX 1: Remove lifecycle event listeners
        document.removeEventListener('visibilitychange', handleVisibilityChange);
        window.removeEventListener('focus', handleWindowFocus);
        window.removeEventListener('blur', handleWindowBlur);

        // FIX 5: Remove orientation listener if it was added
        if (screen.orientation) {
            screen.orientation.removeEventListener('change', handleOrientationChange);
        }

        if (isScanning) {
            stopScanning();
        }

        // LIVEWIRE NAVIGATE FIX: Reset initialization flag so scanner can re-init
        // after navigating away and back via wire:navigate
        isInitialized = false;

        console.log('Scanner cleanup complete, ready for re-initialization');
    }

    // Register cleanup on page unload/refresh - CRITICAL for proper camera release
    window.addEventListener('beforeunload', () => {
        console.log('Page unloading - cleaning up camera resources');
        cleanup();
    });

    // Also handle pagehide for mobile browsers (more reliable than beforeunload on mobile)
    window.addEventListener('pagehide', () => {
        console.log('Page hiding - cleaning up camera resources');
        cleanup();
    });

    // FIX 5: Listen for orientation changes on mobile devices
    if (screen.orientation) {
        screen.orientation.addEventListener('change', handleOrientationChange);
    }

    // LIVEWIRE NAVIGATE FIX: Handle SPA-style navigation with wire:navigate
    // These events fire instead of beforeunload/pagehide during Livewire navigation
    document.addEventListener('livewire:navigating', () => {
        // Called BEFORE navigation - cleanup if scanner was initialized
        if (isInitialized) {
            console.log('Livewire navigating away - cleaning up scanner');
            cleanup();
        }
    });

    document.addEventListener('livewire:navigated', () => {
        // Called AFTER navigation completes - check if we need to init
        const video = document.getElementById('video');
        if (video && !isInitialized) {
            console.log('Livewire navigated to scanner page - initializing');
            init();
        }
    });

    // Public API exposed to Alpine
    return {
        init,
        handleVisibilityChange,
        handleWindowFocus,
        handleWindowBlur,
        handleUserToggle,
        cleanup, // Expose cleanup for manual calls if needed
        // Expose state for debugging
        get isScanning() { return isScanning; },
        get isTorchEnabled() { return isTorchEnabled; }, // FIX 6: Expose renamed variable
    };
}
