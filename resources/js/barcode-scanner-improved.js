import { BrowserMultiFormatReader, NotFoundException } from "@zxing/library";

/**
 * Improved Barcode Scanner with proper state management and controls
 */
class BarcodeScanner {
    constructor() {
        this.codeReader = null;
        this.videoElement = null;
        this.currentStream = null;
        this.isScanning = false;
        this.isInitialized = false;
        this.selectedDeviceId = null;
        this.torchSupported = false;
        this.torchEnabled = false;
        
        this.init();
    }

    async init() {
        if (this.isInitialized) return;
        
        try {
            // Wait for video element to be available
            this.videoElement = await this.waitForVideoElement();
            this.codeReader = new BrowserMultiFormatReader();
            
            // Set up Livewire event listeners
            this.setupLivewireListeners();
            
            // Check camera permissions and initialize
            await this.checkPermissionsAndInit();
            
            this.isInitialized = true;
            Livewire.dispatch("loadingCamera", [false]);
            
        } catch (error) {
            console.error('Scanner initialization failed:', error);
            Livewire.dispatch("loadingCamera", [false]);
            this.showError('Failed to initialize scanner: ' + error.message);
        }
    }

    async waitForVideoElement(timeout = 5000) {
        return new Promise((resolve, reject) => {
            const checkForVideo = () => {
                const video = document.getElementById('video');
                if (video) {
                    resolve(video);
                } else {
                    setTimeout(checkForVideo, 100);
                }
            };
            
            checkForVideo();
            
            // Timeout after 5 seconds
            setTimeout(() => reject(new Error('Video element not found')), timeout);
        });
    }

    setupLivewireListeners() {
        // Camera start/stop toggle
        Livewire.on("camera", () => {
            this.toggleScanning();
        });

        // Torch toggle
        Livewire.on("torch", () => {
            this.toggleTorch();
        });

        // Listen for stop-scan event
        Livewire.on("stop-scan", () => {
            this.stopScanning();
        });
    }

    async checkPermissionsAndInit() {
        try {
            if (!navigator.permissions) {
                // Fallback for browsers without Permissions API
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
            
            // Listen for permission changes
            permission.onchange = () => {
                if (permission.state === 'granted' && !this.isScanning) {
                    this.initializeCamera();
                }
            };
            
        } catch (error) {
            console.error('Permission check failed:', error);
            throw error;
        }
    }

    async requestCameraAndInit() {
        try {
            // Request basic camera access to trigger permission prompt
            const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
            tempStream.getTracks().forEach(track => track.stop());
            
            // Now initialize properly
            await this.initializeCamera();
        } catch (error) {
            throw new Error('Camera access denied');
        }
    }

    async initializeCamera() {
        try {
            const devices = await this.codeReader.listVideoInputDevices();
            
            if (devices.length === 0) {
                throw new Error('No camera devices found');
            }

            // Find back camera or use first available
            this.selectedDeviceId = this.findBackCamera(devices) || devices[0].deviceId;
            
            console.log(`Camera initialized with device: ${this.selectedDeviceId}`);
            
            // Auto-start scanning
            await this.startScanning();
            
        } catch (error) {
            console.error('Camera initialization failed:', error);
            throw error;
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
        if (this.isScanning || !this.selectedDeviceId) return;
        
        try {
            console.log('Starting barcode scanning...');
            
            // Get camera stream with optimized constraints
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
            Livewire.dispatch('camera', [true]);
            
        } catch (error) {
            console.error('Failed to start scanning:', error);
            this.showError('Failed to start camera: ' + error.message);
        }
    }

    stopScanning() {
        if (!this.isScanning) return;
        
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
        if (this.videoElement) {
            this.videoElement.srcObject = null;
        }
        
        this.isScanning = false;
        this.torchEnabled = false;
        
        Livewire.dispatch('camera', [false]);
    }

    async toggleScanning() {
        if (this.isScanning) {
            this.stopScanning();
        } else {
            await this.startScanning();
        }
    }

    checkTorchSupport() {
        if (!this.currentStream) return;
        
        const videoTrack = this.currentStream.getVideoTracks()[0];
        if (!videoTrack) return;
        
        const capabilities = videoTrack.getCapabilities();
        this.torchSupported = !!(capabilities && capabilities.torch);
        
        // Send torch support info to Livewire
        Livewire.dispatch("torchStatusUpdated", [false, this.torchSupported]);
        
        console.log('Torch supported:', this.torchSupported);
    }

    async toggleTorch() {
        if (!this.torchSupported || !this.currentStream) {
            console.warn('Torch not supported on this device');
            Livewire.dispatch("torchStatusUpdated", [false, false]);
            return;
        }

        try {
            const videoTrack = this.currentStream.getVideoTracks()[0];
            this.torchEnabled = !this.torchEnabled;
            
            await videoTrack.applyConstraints({
                advanced: [{ torch: this.torchEnabled }]
            });
            
            console.log('Torch', this.torchEnabled ? 'enabled' : 'disabled');
            
            Livewire.dispatch("torchStatus", [this.torchEnabled]);
            
        } catch (error) {
            console.error('Torch toggle failed:', error);
            this.torchEnabled = false;
            
            Livewire.dispatch("torchStatus", [false]);
        }
    }

    handleBarcodeResult(result) {
        console.log('Barcode detected:', result.text);
        
        // Vibrate if supported
        if (navigator.vibrate) {
            navigator.vibrate(300);
        }
        
        // Stop scanning after successful read
        this.stopScanning();
        
        // Dispatch result to Livewire
        Livewire.dispatch("result", [result]);
        Livewire.dispatch("barcodeScanned");
    }

    showError(message) {
        console.error(message);
        // You could show a toast notification here
        alert(message);
    }

    // Cleanup method
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
    
    // Initialize scanner (it will handle loading state)
    window.barcodeScanner = new BarcodeScanner();
});

// Cleanup on page unload
window.addEventListener("beforeunload", function () {
    if (window.barcodeScanner) {
        window.barcodeScanner.destroy();
    }
});