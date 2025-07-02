import { BrowserMultiFormatReader, NotFoundException } from "@zxing/library";

/**
 * Improved Barcode Scanner - backward compatible version
 */
class BarcodeScanner {
    constructor() {
        this.codeReader = null;
        this.videoElement = null;
        this.currentStream = null;
        this.cameraIsActive = false;
        this.isInitialized = false;
        this.selectedDeviceId = null;
        
        this.init();
    }

    async init() {
        if (this.isInitialized) return;
        
        Livewire.dispatch("loadingCamera", [true]);
        
        try {
            // Wait for video element
            this.videoElement = await this.waitForVideoElement();
            this.codeReader = new BrowserMultiFormatReader();
            
            // Set up Livewire listeners
            this.setupLivewireListeners();
            
            // Check permissions and initialize
            await this.checkPermissionsAndInit();
            
            this.isInitialized = true;
            
        } catch (error) {
            console.error('Scanner initialization failed:', error);
            Livewire.dispatch("loadingCamera", [false]);
        }
    }

    async waitForVideoElement(timeout = 5000) {
        return new Promise((resolve, reject) => {
            const checkForVideo = () => {
                const video = document.getElementById('video');
                if (video) {
                    resolve(video);
                } else {
                    setTimeout(checkForVideo, 50);
                }
            };
            
            checkForVideo();
            setTimeout(() => reject(new Error('Video element not found')), timeout);
        });
    }

    setupLivewireListeners() {
        // Legacy camera toggle (for compatibility)
        Livewire.on("camera", () => {
            this.stopScanning();
        });

        // New camera state management
        Livewire.on("camera-state-changed", (isScanning) => {
            if (isScanning) {
                this.startScanning();
            } else {
                this.stopScanning();
            }
        });

        // Resume scanning event
        Livewire.on("resume-scanning", () => {
            this.startScanning();
        });

        // Stop scan event  
        Livewire.on("stop-scan", () => {
            this.stopScanning();
        });

        // Torch toggle events
        Livewire.on("torch", () => {
            this.toggleTorch();
        });

        Livewire.on("torch-state-changed", (enabled) => {
            this.setTorchState(enabled);
        });
    }

    async checkPermissionsAndInit() {
        try {
            if (!navigator.permissions) {
                await this.requestCameraAndInit();
                return;
            }

            const permission = await navigator.permissions.query({ name: 'camera' });
            
            if (permission.state === 'granted') {
                await this.initializeCamera();
            } else if (permission.state === 'prompt') {
                await this.requestCameraAndInit();
            } else {
                throw new Error('Camera permission denied');
            }
            
            permission.onchange = () => {
                if (permission.state === 'granted' && !this.cameraIsActive) {
                    this.initializeCamera();
                }
            };
            
        } catch (error) {
            console.error('Permission check failed:', error);
            alert("Camera access denied. Please enable camera permissions to use this feature.");
            Livewire.dispatch("loadingCamera", [false]);
        }
    }

    async requestCameraAndInit() {
        try {
            const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
            tempStream.getTracks().forEach(track => track.stop());
            await this.initializeCamera();
        } catch (error) {
            console.error("Permission denied or error:", error);
            alert("Camera access is required to scan.");
            Livewire.dispatch("loadingCamera", [false]);
        }
    }

    async initializeCamera() {
        try {
            const devices = await this.codeReader.listVideoInputDevices();
            
            if (devices.length === 0) {
                console.error("No video input devices found.");
                Livewire.dispatch("loadingCamera", [false]);
                return;
            }

            // Find back camera or use first available
            this.selectedDeviceId = this.findBackCamera(devices) || devices[0].deviceId;
            
            // Start scanning
            await this.startScanning();
            
        } catch (error) {
            console.error('Camera initialization failed:', error);
            Livewire.dispatch("loadingCamera", [false]);
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

    async startScanning() {
        if (this.cameraIsActive) return;
        
        // If no device selected yet, initialize camera first
        if (!this.selectedDeviceId) {
            await this.initializeCamera();
            return;
        }
        
        try {
            // Get optimized camera stream
            this.currentStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    deviceId: { exact: this.selectedDeviceId },
                    facingMode: { ideal: "environment" },
                    aspectRatio: { ideal: 4 / 3 },
                    width: { min: 640, ideal: 1280, max: 1920 },
                    height: { min: 480, ideal: 720, max: 1080 },
                    frameRate: { ideal: 30, min: 1, max: 60 }
                }
            });

            // Set video source
            this.videoElement.srcObject = this.currentStream;
            
            // Check torch support
            this.checkTorchSupport();
            
            // Start ZXing decoding
            this.codeReader.decodeFromVideoDevice(
                this.selectedDeviceId,
                "video",
                (result, err) => {
                    if (result && this.cameraIsActive) {
                        this.handleBarcodeResult(result);
                    }
                    if (err && !(err instanceof NotFoundException)) {
                        console.error(err);
                    }
                }
            );
            
            this.cameraIsActive = true;
            Livewire.dispatch('camera', [true]);
            Livewire.dispatch("loadingCamera", [false]);
            
            console.log(`Started continuous decode from camera with id ${this.selectedDeviceId}`);
            
        } catch (error) {
            console.error('Failed to start scanning:', error);
            Livewire.dispatch("loadingCamera", [false]);
        }
    }

    stopScanning() {
        if (!this.cameraIsActive) return;
        
        console.log('Stopping barcode scanning...');
        
        // Reset ZXing reader
        if (this.codeReader) {
            this.codeReader.reset();
        }
        
        // Stop video stream
        if (this.currentStream) {
            this.currentStream.getTracks().forEach(track => track.stop());
            this.currentStream = null;
        }
        
        // Clear video element
        if (this.videoElement && this.videoElement.srcObject) {
            this.videoElement.srcObject = null;
        }
        
        this.cameraIsActive = false;
    }

    checkTorchSupport() {
        if (!this.currentStream) return;
        
        const videoTrack = this.currentStream.getVideoTracks()[0];
        if (!videoTrack) return;
        
        const capabilities = videoTrack.getCapabilities();
        const torchSupported = !!(capabilities && capabilities.torch);
        
        Livewire.dispatch("torchStatusUpdated", [false, torchSupported]);
        
        console.log('Torch supported:', torchSupported);
    }

    async toggleTorch() {
        if (!this.currentStream) {
            console.warn('No camera stream available');
            return;
        }

        const videoTrack = this.currentStream.getVideoTracks()[0];
        if (!videoTrack) return;

        const capabilities = videoTrack.getCapabilities();
        if (!(capabilities && capabilities.torch)) {
            console.warn("Torch not supported on this device/browser.");
            Livewire.dispatch("torchStatusUpdated", [false, false]);
            return;
        }

        try {
            const constraints = videoTrack.getConstraints();
            const currentTorchState = constraints.advanced?.[0]?.torch || false;
            const newTorchState = !currentTorchState;
            
            await videoTrack.applyConstraints({
                advanced: [{ torch: newTorchState }]
            });
            
            console.log('Torch', newTorchState ? 'enabled' : 'disabled');
            Livewire.dispatch("torchStatus", [newTorchState]);
            
        } catch (error) {
            console.error("Error toggling torch:", error);
            Livewire.dispatch("torchStatus", [false]);
        }
    }

    async setTorchState(enabled) {
        if (!this.currentStream) {
            console.warn('No camera stream available for torch control');
            return;
        }

        const videoTrack = this.currentStream.getVideoTracks()[0];
        if (!videoTrack) return;

        const capabilities = videoTrack.getCapabilities();
        if (!(capabilities && capabilities.torch)) {
            console.warn("Torch not supported on this device/browser.");
            return;
        }

        try {
            await videoTrack.applyConstraints({
                advanced: [{ torch: enabled }]
            });
            
            console.log('Torch set to:', enabled ? 'enabled' : 'disabled');
            
        } catch (error) {
            console.error("Error setting torch state:", error);
        }
    }

    handleBarcodeResult(result) {
        console.log('Barcode detected:', result.text);
        
        // Vibrate if supported
        if (navigator.vibrate) {
            navigator.vibrate(300);
        }
        
        // Dispatch result to Livewire
        Livewire.dispatch("onBarcodeDetected", [result.text]);
        Livewire.dispatch("barcodeScanned");
        
        // Stop scanning after successful read
        this.cameraIsActive = false;
        Livewire.dispatch('camera');
    }

    destroy() {
        this.stopScanning();
        this.isInitialized = false;
    }
}

// Initialize when Livewire is ready
window.addEventListener("livewire:initialized", function () {
    // Prevent multiple initializations
    if (window.barcodeScanner) {
        window.barcodeScanner.destroy();
    }
    
    // Initialize scanner
    window.barcodeScanner = new BarcodeScanner();
});

// Cleanup on page unload
window.addEventListener("beforeunload", function () {
    if (window.barcodeScanner) {
        window.barcodeScanner.destroy();
    }
});