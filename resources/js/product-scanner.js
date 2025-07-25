import { BrowserMultiFormatReader, NotFoundException } from "@zxing/library";

/**
 * Clean Product Scanner - Livewire controls all state, JS just handles hardware
 */
class ProductScanner {
    constructor() {
        console.log('ProductScanner constructor called');
        this.codeReader = null;
        this.videoElement = null;
        this.currentStream = null;
        this.isScanning = false;
        this.torchEnabled = false;
        this.selectedDeviceId = null;
        this.hasUserInteraction = false;
        this.vibrationSupported = null;
        
        this.init();
    }

    async init() {
        try {
            // Wait for video element
            this.videoElement = await this.waitForVideoElement();
            this.codeReader = new BrowserMultiFormatReader();
            
            // Set up Livewire listeners
            this.setupLivewireListeners();
            
            // Set up user interaction tracking
            this.setupUserInteractionTracking();
            
            // Test vibration support
            this.testVibrationSupport();
            
            // Initialize camera
            await this.initializeCamera();
            
        } catch (error) {
            console.error('Scanner initialization failed:', error);
            Livewire.dispatch('onCameraError', [error.message]);
        }
    }

    async waitForVideoElement(timeout = 10000) {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const maxAttempts = timeout / 100;
            
            const checkForVideo = () => {
                const video = document.getElementById('video');
                attempts++;
                
                if (video) {
                    console.log(`Video element found after ${attempts} attempts`);
                    resolve(video);
                } else if (attempts >= maxAttempts) {
                    reject(new Error('Video element not found after timeout'));
                } else {
                    setTimeout(checkForVideo, 100);
                }
            };
            
            console.log('Waiting for video element...');
            checkForVideo();
        });
    }

    setupLivewireListeners() {
        // Listen for Livewire camera state changes
        Livewire.on('camera-state-changed', (data) => {
            console.log('Camera state changed:', data);
            const isScanning = Array.isArray(data) ? data[0] : data;
            
            if (isScanning) {
                this.startScanning();
            } else {
                console.log('Stopping camera and releasing hardware...');
                this.stopScanning(); // Fully stops and releases camera
            }
        });

        // Listen for Livewire torch state changes
        Livewire.on('torch-state-changed', (enabled) => {
            this.setTorchState(enabled);
        });

        // Listen for resume scanning
        Livewire.on('resume-scanning', () => {
            this.resumeScanning();
        });

        // Listen for vibration triggers from Livewire
        Livewire.on('trigger-vibration', (data) => {
            console.log('📳 Livewire triggered vibration event');
            
            if (data && data.pattern) {
                console.log('🎵 Using custom pattern:', data.pattern, `(${data.label || 'unknown'})`);
                this.triggerVibrationWithPattern(data.pattern, data.label);
            } else {
                console.log('🎵 Using default pattern');
                this.triggerVibration();
            }
        });
    }

    async initializeCamera() {
        try {
            // Check and request camera permission if needed
            await this.checkAndRequestPermission();
            
            // Get available cameras
            const devices = await this.codeReader.listVideoInputDevices();
            
            if (devices.length === 0) {
                throw new Error('No camera devices found');
            }

            // Find back camera or use first available
            this.selectedDeviceId = this.findBackCamera(devices) || devices[0].deviceId;
            
            // Auto-start scanning
            await this.startScanning();
            
        } catch (error) {
            console.error('Camera initialization failed:', error);
            Livewire.dispatch('onCameraError', [error.message]);
        }
    }

    findBackCamera(devices) {
        const backCamera = devices.find(device => 
            device.label.toLowerCase().includes('back') ||
            device.label.toLowerCase().includes('rear') ||
            device.label.toLowerCase().includes('environment')
        );
        return backCamera?.deviceId;
    }

    async checkAndRequestPermission() {
        try {
            // Check if Permissions API is available
            if (navigator.permissions) {
                const permission = await navigator.permissions.query({ name: 'camera' });
                
                if (permission.state === 'granted') {
                    console.log('Camera permission already granted');
                    return; // Already have permission, no need to request
                } else if (permission.state === 'denied') {
                    throw new Error('Camera access denied. Please enable camera permissions in your browser settings.');
                }
                // If 'prompt', fall through to request permission
            }
            
            // Request camera access (for 'prompt' state or when Permissions API not available)
            await this.requestCameraPermission();
            
        } catch (error) {
            console.error('Permission check failed:', error);
            throw error;
        }
    }

    async requestCameraPermission() {
        try {
            console.log('Requesting camera permission...');
            
            // Request camera access which will trigger browser permission prompt
            const tempStream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: { ideal: "environment" }
                } 
            });
            
            // Immediately stop the stream - we just needed to get permission
            tempStream.getTracks().forEach(track => track.stop());
            
            console.log('Camera permission granted');
            
        } catch (error) {
            console.error('Camera permission denied:', error);
            throw new Error('Camera access denied. Please allow camera access to use the scanner.');
        }
    }

    async startScanning() {
        if (this.isScanning || !this.selectedDeviceId) return;
        
        try {
            // Get camera stream
            this.currentStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    deviceId: { exact: this.selectedDeviceId },
                    facingMode: { ideal: "environment" },
                    width: { ideal: 1280, min: 640, max: 1920 },
                    height: { ideal: 720, min: 480, max: 1080 },
                    frameRate: { ideal: 30, max: 60 }
                }
            });

            // Set video source
            this.videoElement.srcObject = this.currentStream;
            
            // Check torch support
            this.checkTorchSupport();
            
            // Start ZXing decoding
            await this.codeReader.decodeFromVideoDevice(
                this.selectedDeviceId,
                'video',
                (result, err) => {
                    if (result && this.isScanning) {
                        this.handleBarcodeResult(result);
                    }
                    if (err && !(err instanceof NotFoundException)) {
                        console.warn('Decode error:', err);
                    }
                }
            );
            
            this.isScanning = true;
            Livewire.dispatch('onCameraReady');
            
        } catch (error) {
            console.error('Failed to start scanning:', error);
            Livewire.dispatch('onCameraError', [error.message]);
        }
    }

    stopScanning() {
        console.log('Stopping scanner and releasing camera hardware...');
        
        // Reset ZXing reader
        if (this.codeReader) {
            this.codeReader.reset();
        }
        
        // Stop and release all video tracks
        if (this.currentStream) {
            console.log('Stopping video tracks...');
            this.currentStream.getTracks().forEach(track => {
                console.log('Stopping track:', track.kind, track.label);
                track.stop();
            });
            this.currentStream = null;
        }
        
        // Clear video element completely
        if (this.videoElement) {
            this.videoElement.srcObject = null;
            this.videoElement.load(); // Force video element to release
        }
        
        this.isScanning = false;
        this.torchEnabled = false;
        
        console.log('Camera hardware fully released');
    }

    checkTorchSupport() {
        if (!this.currentStream) return;
        
        const videoTrack = this.currentStream.getVideoTracks()[0];
        if (!videoTrack) return;
        
        const capabilities = videoTrack.getCapabilities();
        const torchSupported = !!(capabilities && capabilities.torch);
        
        // Report to Livewire
        Livewire.dispatch('onTorchSupportDetected', [torchSupported]);
        
        console.log('Torch supported:', torchSupported);
    }

    async setTorchState(enabled) {
        if (!this.currentStream) return;

        try {
            const videoTrack = this.currentStream.getVideoTracks()[0];
            
            await videoTrack.applyConstraints({
                advanced: [{ torch: enabled }]
            });
            
            this.torchEnabled = enabled;
            console.log('Torch', enabled ? 'enabled' : 'disabled');
            
            // Report success to Livewire
            Livewire.dispatch('onTorchStateChanged', [enabled]);
            
        } catch (error) {
            console.error('Torch toggle failed:', error);
            // Report failure to Livewire
            Livewire.dispatch('onTorchStateChanged', [false]);
        }
    }

    handleBarcodeResult(result) {
        console.log('🔍 Barcode detected:', result.text);
        
        // Trigger vibration immediately (automatic scan detection)
        console.log('📳 Triggering vibration from barcode detection...');
        this.triggerVibration();
        
        // Pause scanning but keep camera stream active
        this.pauseScanning();
        
        // Report to Livewire
        Livewire.dispatch('onBarcodeDetected', [result.text]);
    }

    pauseScanning() {
        // Stop ZXing decoding but keep camera stream running
        if (this.codeReader) {
            this.codeReader.reset();
        }
        this.isScanning = false;
        console.log('Scanning paused - camera stream remains active');
    }

    resumeScanning() {
        if (!this.currentStream || this.isScanning) return;
        
        // Resume ZXing decoding with existing stream
        this.codeReader.decodeFromVideoDevice(
            this.selectedDeviceId,
            'video',
            (result, err) => {
                if (result && this.isScanning) {
                    this.handleBarcodeResult(result);
                }
                if (err && !(err instanceof NotFoundException)) {
                    console.warn('Decode error:', err);
                }
            }
        );
        
        this.isScanning = true;
        console.log('Scanning resumed');
    }

    setupUserInteractionTracking() {
        // Track user interactions to enable vibration API
        const events = ['click', 'touchstart', 'keydown', 'pointerdown'];
        
        const handleInteraction = () => {
            this.hasUserInteraction = true;
            console.log('👆 User interaction detected - vibration API enabled');
            console.log('🔧 Vibration API status:', {
                supported: this.vibrationSupported,
                userInteraction: this.hasUserInteraction
            });
            
            // Remove listeners after first interaction
            events.forEach(event => {
                document.removeEventListener(event, handleInteraction, { passive: true });
            });
        };
        
        events.forEach(event => {
            document.addEventListener(event, handleInteraction, { passive: true });
        });
    }

    testVibrationSupport() {
        // Check if vibration API exists
        const apiExists = !!navigator.vibrate;
        
        // Check if we're on a device that actually has vibration hardware
        const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        
        // More conservative detection - API must exist AND device should have vibration hardware
        this.vibrationSupported = apiExists && (isMobile || isTouchDevice);
        
        console.log('🔍 Vibration API detection:', {
            apiExists,
            isMobile,
            isTouchDevice,
            userAgent: navigator.userAgent.substring(0, 50),
            finalSupported: this.vibrationSupported
        });
        
        if (this.vibrationSupported) {
            console.log('✅ Vibration API supported (mobile device with vibration hardware)');
        } else if (apiExists && !isMobile) {
            console.log('⚠️ Vibration API exists but this appears to be a desktop device (no vibration hardware)');
        } else {
            console.log('❌ Vibration API not supported');
        }
        
        // Report support to Livewire
        Livewire.dispatch('onVibrationSupportDetected', [this.vibrationSupported]);
    }

    triggerVibration() {
        // Default fallback pattern
        this.triggerVibrationWithPattern([100, 50, 200], 'Medium (Default)');
    }

    triggerVibrationWithPattern(pattern, label = 'Custom') {
        console.log('📳 triggerVibrationWithPattern() called');
        console.log('🔧 Current vibration state:', {
            supported: this.vibrationSupported,
            userInteraction: this.hasUserInteraction,
            navigatorVibrate: !!navigator.vibrate,
            userAgent: navigator.userAgent.substring(0, 100),
            pattern: pattern,
            label: label
        });
        
        // Check all requirements for vibration
        if (!this.vibrationSupported) {
            console.log('❌ Vibration skipped: API not supported');
            return;
        }
        
        if (!this.hasUserInteraction) {
            console.log('❌ Vibration skipped: No user interaction yet');
            console.log('💡 Try tapping the screen first to enable vibration');
            return;
        }

        // Skip if pattern is empty (off setting)
        if (!pattern || pattern.length === 0) {
            console.log('🔇 Vibration skipped: Pattern is "off"');
            return;
        }
        
        try {
            console.log(`📳 Attempting ${label} vibration with pattern:`, pattern);
            
            const success = navigator.vibrate(pattern);
            
            if (success) {
                console.log(`✅ ${label} vibration triggered successfully!`);
                
                // Log pattern description
                const description = pattern.map((duration, index) => {
                    if (index % 2 === 0) {
                        return `${duration}ms buzz`;
                    } else {
                        return `${duration}ms pause`;
                    }
                }).join(' → ');
                console.log('📱 Pattern:', description);
            } else {
                console.log('❌ navigator.vibrate() returned false');
            }
        } catch (error) {
            console.error('💥 Vibration error:', error);
        }
    }

    destroy() {
        this.stopScanning();
    }
}

// Initialize when Livewire is ready
window.addEventListener("livewire:initialized", function () {
    console.log('Livewire initialized, starting ProductScanner...');
    
    if (window.productScanner) {
        window.productScanner.destroy();
    }
    
    window.productScanner = new ProductScanner();
});

// Cleanup on page unload
window.addEventListener("beforeunload", function () {
    if (window.productScanner) {
        window.productScanner.destroy();
    }
});