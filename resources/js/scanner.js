import { BrowserMultiFormatReader } from '@zxing/library';

class BarcodeScanner {
    constructor() {
        // Create instance of BrowserMultiFormatReader
        this.reader = new BrowserMultiFormatReader();
        // Set up constraints to specifically request the environment-facing camera
        this.constraints = {
            video: {
                facingMode: 'environment' // This specifically requests the back camera
            }
        };
    }

    /**
     * Initialize the scanner with environment-facing camera
     */
    async init() {
        try {
            // First check if we have permission to access the camera
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Browser API navigator.mediaDevices.getUserMedia not available');
            }

            // Get video stream using our environment-facing constraints
            const stream = await navigator.mediaDevices.getUserMedia(this.constraints);
            console.log('Camera access granted');

            return stream;
        } catch (err) {
            console.error('Failed to initialize scanner:', err);
            throw err;
        }
    }

    /**
     * Start continuous scanning
     * @param {string} videoElementId - ID of the video element to display the feed
     * @param {Function} onResult - Callback function for successful scans
     * @param {Function} onError - Callback function for errors
     */
    startScanning(videoElementId, onResult, onError) {
        let lastLoggedFormat = null; // To avoid logging same barcode format repeatedly

        try {
            this.reader.decodeFromConstraints(
                this.constraints,
                videoElementId,
                (result, error) => {
                    if (result) {
                        const currentFormat = result.getBarcodeFormat();
                        if (currentFormat !== lastLoggedFormat) {
                            // Only log if the format has changed
                            console.log('Scanned barcode:', result.getText());
                            console.log('Barcode format:', currentFormat);
                            lastLoggedFormat = currentFormat;
                        }

                        onResult({
                            text: result.getText(),
                            format: currentFormat,
                            timestamp: new Date().getTime()
                        });
                    }
                    if (error && onError) {
                        onError(error);
                    }
                }
            );
        } catch (err) {
            console.error('Failed to start scanning:', err);
            if (onError) onError(err);
        }
    }

    /**
     * Scan a single barcode
     * @param {string} videoElementId - ID of the video element to display the feed
     * @returns {Promise} Resolution contains the scan result
     */
    async scanOnce(videoElementId) {
        try {
            const result = await this.reader.decodeOnceFromConstraints(
                this.constraints,
                videoElementId
            );

            return {
                text: result.getText(),
                format: result.getBarcodeFormat(),
                timestamp: new Date().getTime()
            };
        } catch (err) {
            console.error('Failed to scan:', err);
            throw err;
        }
    }

    /**
     * Stop scanning and stop the camera stream
     */
    stopScanning() {
        this.reader.reset();

        // Stop the video stream (if any)
        const videoElement = document.querySelector('video');
        if (videoElement && videoElement.srcObject) {
            const stream = videoElement.srcObject;
            const tracks = stream.getTracks();
            tracks.forEach(track => track.stop());
        }
    }
}

const scanner = new BarcodeScanner();

/**
 * Scan once and log the result
 */
async function scanOnce() {
    try {
        const result = await scanner.scanOnce('video');
        console.log('Scanned barcode:', result.text);
        scanner.stopScanning()
    } catch (error) {
        console.error('Scanning error:', error);
    }
}

/**
 * Start continuous barcode scanning
 */
async function startBarcodeScanning() {
    try {
        await scanner.init();
        scanner.startScanning(
            'video',
            (result) => {
                // You can handle the result here, for example:
                console.log('Scanned barcode:', result.text);
                console.log('Barcode format:', result.format);
            },
            (error) => {
                console.error('Scanning error:', error);
            }
        )

        await scanner.stopScanning()
    } catch (error) {
        console.error('Failed to initialize scanner:', error);
    }
}

/**
 * Stop continuous barcode scanning
 */
function stopScanning() {
    scanner.stopScanning();
}

// Trigger scanning actions from Livewire events
Livewire.on('startScan', () => scanOnce());
Livewire.on('stopScan', () => stopScanning());
