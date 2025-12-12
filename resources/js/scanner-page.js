import { BrowserMultiFormatReader, NotFoundException } from "@zxing/library";

/**
 * Alpine store for ZXing barcode scanning (Refactored Architecture)
 *
 * Flow: ZXing controls API -> Alpine store -> Livewire dispatch -> PHP
 *
 * Key improvements:
 * - Uses controls.stop() instead of reset() for clean camera release
 * - Proper event listener cleanup on navigation
 * - Promise-based async/await throughout
 * - Simplified state management
 * - Clean Livewire lifecycle handling
 */

if (window.Alpine) {
    window.Alpine.store('scanner', createScannerStore());
} else {
    document.addEventListener('alpine:init', () => {
        window.Alpine.store('scanner', createScannerStore());
    });
}

function createScannerStore() {
    // Core ZXing objects
    let codeReader = null;
    let controls = null; // NEW: ZXing controls object from decodeFromVideoDevice

    // Camera state
    let selectedDeviceId = null;
    let isScanning = false;
    let isTorchEnabled = false;
    let isInitialized = false;

    // User interaction tracking
    let vibrationSupported = null;
    let hasUserInteraction = false;

    // Event listener references for cleanup
    const eventListeners = new Map();

    /**
     * Register an event listener with cleanup tracking
     */
    function addEventListener(target, event, handler, options) {
        const key = `${target === window ? 'window' : target === document ? 'document' : 'other'}-${event}`;

        // Remove existing listener if present
        if (eventListeners.has(key)) {
            const { target: oldTarget, event: oldEvent, handler: oldHandler } = eventListeners.get(key);
            oldTarget.removeEventListener(oldEvent, oldHandler);
        }

        // Add new listener
        target.addEventListener(event, handler, options);
        eventListeners.set(key, { target, event, handler });
    }

    /**
     * Remove all tracked event listeners
     */
    function removeAllEventListeners() {
        for (const { target, event, handler } of eventListeners.values()) {
            target.removeEventListener(event, handler);
        }
        eventListeners.clear();
    }

    /**
     * Setup Livewire event listeners for camera control
     */
    function setupLivewireListeners() {
        if (!window.Livewire) return;

        window.Livewire.on('camera-state-changed', async (data) => {
            const shouldScan = Array.isArray(data) ? data[0] : data;
            console.log('Livewire camera-state-changed:', shouldScan);

            if (shouldScan) {
                await startScanning();
            } else {
                await stopScanning();
            }
        });

        window.Livewire.on('torch-state-changed', async (enabled) => {
            await setTorchState(enabled);
        });

        window.Livewire.on('resume-scanning', async () => {
            await startScanning();
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
        });
    }

    /**
     * Initialize the scanner - called from Alpine x-init
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

            // Initialize ZXing reader
            codeReader = new BrowserMultiFormatReader();

            // Setup listeners
            setupLivewireListeners();
            setupUserInteractionTracking();
            testVibrationSupport();
            setupLifecycleListeners();

            console.log('Scanner initialization complete');

            // Initialize camera
            await initializeCamera();

        } catch (e) {
            console.error('Scanner init failed:', e);
            window.Livewire?.dispatch('onCameraError', [e.message || 'Scanner initialization failed']);
        }
    }

    /**
     * Setup lifecycle event listeners for PWA support
     */
    function setupLifecycleListeners() {
        // Page visibility (tab switching, app backgrounding)
        addEventListener(document, 'visibilitychange', handleVisibilityChange);

        // Window focus/blur (for PWA window management)
        addEventListener(window, 'focus', handleWindowFocus);
        addEventListener(window, 'blur', handleWindowBlur);

        // Orientation changes (mobile device rotation)
        if (screen.orientation) {
            addEventListener(screen.orientation, 'change', handleOrientationChange);
        }

        // Page unload/hide (traditional navigation)
        addEventListener(window, 'beforeunload', handlePageUnload);
        addEventListener(window, 'pagehide', handlePageHide);

        // Livewire navigation (SPA-style navigation)
        addEventListener(document, 'livewire:navigating', handleLivewireNavigating);
        addEventListener(document, 'livewire:navigated', handleLivewireNavigated);

        console.log('Lifecycle event listeners registered');
    }

    /**
     * Initialize camera - get devices, select back camera, start scanning
     */
    async function initializeCamera() {
        try {
            window.Livewire?.dispatch('onCameraInitializing');

            // Check and request permission
            await checkAndRequestPermission();

            // Get available video devices
            const devices = await codeReader.listVideoInputDevices();

            if (devices.length === 0) {
                throw new Error('No camera devices found');
            }

            // Log available cameras
            console.log('Available cameras:', devices.map(d => ({
                id: d.deviceId,
                label: d.label || 'Unknown device'
            })));

            // Prefer back camera
            selectedDeviceId = findBackCamera(devices) || devices[0].deviceId;
            const selectedCamera = devices.find(d => d.deviceId === selectedDeviceId);

            console.log('Selected camera:', {
                id: selectedDeviceId,
                label: selectedCamera?.label || 'Unknown device'
            });

            // Auto-start scanning
            await startScanning();

        } catch (e) {
            console.error('Camera initialization failed:', e);

            // Provide user-friendly error messages
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
     * Check and request camera permission with optimal constraints
     */
    async function checkAndRequestPermission() {
        // Check existing permission status
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
                if (e.name === 'NotAllowedError') {
                    throw e;
                }
                // Permissions API not fully supported, continue
            }
        }

        // Request permission with optimal constraints for barcode scanning
        try {
            const tempStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280, min: 640, max: 1920 },
                    height: { ideal: 720, min: 480, max: 1080 },
                    frameRate: { ideal: 30, max: 60 }
                }
            });

            // Stop the temp stream immediately
            tempStream.getTracks().forEach(t => t.stop());
            console.log('Camera permission granted with optimal constraints');

        } catch (e) {
            if (e.name === 'NotAllowedError') {
                const error = new Error('Camera access denied. Please allow camera access to use the scanner.');
                error.name = 'NotAllowedError';
                throw error;
            }
            throw e;
        }
    }

    /**
     * Start scanning using ZXing controls API
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

            // Ensure we have a fresh codeReader instance (required after controls.stop())
            if (!codeReader) {
                console.log('Creating new BrowserMultiFormatReader instance');
                codeReader = new BrowserMultiFormatReader();
            }

            // Use ZXing's decodeFromVideoDevice which returns controls object
            controls = await codeReader.decodeFromVideoDevice(
                selectedDeviceId,
                'video',
                (result, err) => {
                    // Check scanning flag - ignore callbacks during shutdown
                    if (!isScanning) {
                        return;
                    }

                    if (result) {
                        handleBarcodeResult(result);
                    }

                    // Only log unexpected errors (NotFoundException is normal)
                    if (err && !(err instanceof NotFoundException)) {
                        console.warn('Decode error:', err.message || err);
                    }
                }
            );

            // Update state after successful start
            isScanning = true;

            // Check torch support after video stream is ready
            setTimeout(() => {
                if (isScanning) {
                    checkTorchSupport();
                }
            }, 500);

            window.Livewire?.dispatch('onCameraReady');
            console.log('Scanner started successfully');

        } catch (e) {
            console.error('Failed to start scanning:', e);

            let userMessage = e.message || 'Failed to start camera';
            if (e.name === 'NotReadableError') {
                userMessage = 'Camera is in use by another application.';
            }

            window.Livewire?.dispatch('onCameraError', [userMessage]);
        }
    }

    /**
     * Stop scanning using ZXing controls API - clean, no exceptions
     */
    async function stopScanning() {
        const wasScanning = isScanning;

        // Update state first to prevent callback processing
        isScanning = false;
        isTorchEnabled = false;

        if (!wasScanning) {
            console.log('Scanner was not running, nothing to stop');
            return;
        }

        console.log('Stopping scanner and releasing camera hardware...');

        try {
            // Use controls.stop() - clean API, no exceptions
            if (controls) {
                controls.stop();
                controls = null;
                console.log('Camera stopped via controls.stop()');
            }

            // Clear video element
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

            // Reset codeReader for fresh start next time
            codeReader = null;

            console.log('Camera hardware fully released');

        } catch (e) {
            console.warn('Error during camera stop:', e);
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

        const videoTrack = video.srcObject.getVideoTracks?.()?.[0];
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

            isTorchEnabled = !!enabled;
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
    async function handleBarcodeResult(result) {
        console.log('Barcode detected:', result.text);

        // Immediate haptic feedback
        triggerVibration();

        // Stop scanning FIRST and wait for hardware release
        await stopScanning();
        console.log('Camera hardware released after barcode detection');

        // Only dispatch to Livewire after camera is fully stopped
        window.Livewire?.dispatch('onBarcodeDetected', [result.text]);
    }

    /**
     * Track user interaction for vibration API
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
     * Test if vibration is supported
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
     * Lifecycle: Handle page visibility change
     */
    async function handleVisibilityChange() {
        if (!isInitialized) return;

        if (document.hidden) {
            console.log('Page hidden - stopping camera to release hardware');
            await stopScanning();
            // Notify Livewire that camera has stopped so UI updates
            window.Livewire?.dispatch('onCameraStopped');
        } else {
            console.log('Page visible - restarting camera');
            // Restart camera when user returns to the app
            await startScanning();
        }
    }

    /**
     * Lifecycle: Handle window focus
     */
    function handleWindowFocus() {
        console.log('Window focused');
    }

    /**
     * Lifecycle: Handle window blur
     */
    function handleWindowBlur() {
        console.log('Window blurred');
    }

    /**
     * Lifecycle: Handle orientation changes
     */
    function handleOrientationChange() {
        if (!isInitialized || !isScanning) return;

        console.log('Orientation changed');
        const orientation = screen.orientation?.type || 'unknown';
        console.log('New orientation:', orientation);
    }

    /**
     * Lifecycle: Handle page unload
     */
    function handlePageUnload() {
        console.log('Page unloading - cleaning up camera resources');
        cleanup();
    }

    /**
     * Lifecycle: Handle page hide
     */
    function handlePageHide() {
        console.log('Page hiding - cleaning up camera resources');
        cleanup();
    }

    /**
     * Lifecycle: Handle Livewire navigating away
     */
    function handleLivewireNavigating() {
        if (isInitialized) {
            console.log('Livewire navigating away - cleaning up scanner');
            cleanup();
        }
    }

    /**
     * Lifecycle: Handle Livewire navigated to page
     */
    function handleLivewireNavigated() {
        const video = document.getElementById('video');
        if (video && !isInitialized) {
            console.log('Livewire navigated to scanner page - initializing');
            init();
        }
    }

    /**
     * Handle user toggle request
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
     * Cleanup function - releases camera and removes event listeners
     */
    function cleanup() {
        console.log('Scanner cleanup triggered');

        // Stop camera if running
        if (isScanning) {
            stopScanning();
        }

        // Remove all tracked event listeners
        removeAllEventListeners();

        // Reset initialization flag for re-initialization
        isInitialized = false;

        // Clear controls reference
        controls = null;

        console.log('Scanner cleanup complete, ready for re-initialization');
    }

    // Public API exposed to Alpine
    return {
        init,
        handleVisibilityChange,
        handleWindowFocus,
        handleWindowBlur,
        handleUserToggle,
        cleanup,
        // Expose state for debugging
        get isScanning() { return isScanning; },
        get isTorchEnabled() { return isTorchEnabled; },
        get isInitialized() { return isInitialized; },
    };
}