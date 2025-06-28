import { BrowserMultiFormatReader, NotFoundException } from "@zxing/library";

window.addEventListener("livewire:initialized", function () {
    // Wait for DOM to be ready and video element to exist
    document.addEventListener('DOMContentLoaded', function() {
        initializeBarcodeScanner();
    });
    
    // Also try to initialize if DOM is already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeBarcodeScanner();
        });
    } else {
        // DOM is already loaded, check if video element exists
        setTimeout(() => {
            if (document.getElementById('video')) {
                initializeBarcodeScanner();
            }
        }, 100);
    }
});

function initializeBarcodeScanner() {
    // Prevent multiple initializations
    if (window.barcodeScannerInitialized) return;
    window.barcodeScannerInitialized = true;
    
    Livewire.dispatch("loadingCamera", {loadingCamera: true});
    let cameraIsActive = true;
    let codeReader; // Declare codeReader outside the function

    // Function to initialize the camera and start barcode scanning
    const initializeCamera = () => {
        codeReader = new BrowserMultiFormatReader(); // Initialize here

        // Request the list of available video input devices
        codeReader
            .listVideoInputDevices()
            .then((videoInputDevices) => {
                if (videoInputDevices.length === 0) {
                    console.error("No video input devices found.");
                    Livewire.dispatch("loadingCamera", {loadingCamera: false});
                    return;
                }

                // Find the back camera (rear-facing)
                let selectedDeviceId = null;
                for (const device of videoInputDevices) {
                    if (device.label.toLowerCase().includes("back")) {
                        selectedDeviceId = device.deviceId;
                        break;
                    }
                }

                if (!selectedDeviceId) {
                    // console.warn("No back camera found, using first available camera.");
                    selectedDeviceId = videoInputDevices[0].deviceId;
                }

                // Start continuous barcode scanning from the selected video device
                startScanning(selectedDeviceId);
                Livewire.dispatch('camera', [true]);

                // Try to select the back camera explicitly using 'facingMode' constraint
                navigator.mediaDevices
                    .getUserMedia({
                        video: {
                            deviceId: { exact: selectedDeviceId }, // Use the selected device ID
                            facingMode: { exact: "environment" },
                            aspectRatio: { ideal: 4 / 3 },
                            width: { min: 640, ideal: 1280, max: 1920 },
                            height: { min: 480, ideal: 720, max: 1080 },
                            audio: false,
                            frameRate: { ideal: 30, min:1, max:60},
                            resizeMode: 'crop-and-scale',
                        },
                    })
                    .then((stream) => {
                        // Once the camera stream is obtained, we use the selected device
                        const videoTracks = stream.getVideoTracks();
                        if (videoTracks.length > 0) {
                            const videoTrack = videoTracks[0];
                            Livewire.on("torch", () => { // Keep the existing name for now if it works with Livewire
                                const videoTrack = stream.getVideoTracks()[0]; // Make sure stream and videoTrack are accessible here
                                if (videoTrack && typeof videoTrack.applyConstraints === 'function') { // Check if supported
                                    // Assume torch will be on, update UI immediately for better UX
                                    Livewire.dispatch("torchStatus", {torchStatus: true});

                                    videoTrack
                                        .applyConstraints({
                                            advanced: [{ torch: true }],
                                        })
                                        .catch((error) => {
                                            console.error("Error turning torch on:", error);
                                            // If failed, revert UI state
                                            Livewire.dispatch("torchStatus", { torchStatus: false, on: false, error: true });
                                            // Optionally show an alert to the user that torch isn't supported/failed
                                        });
                                } else {
                                    console.warn("Torch not supported on this device/browser.");
                                    Livewire.dispatch("torchStatusUpdated", { on: false, supported: false });
                                }
                            });
                        }
                    })
                    .catch((err) => {
                        // console.error("Error accessing back camera:", err);
                        // alert("Unable to access the back camera.");
                        Livewire.dispatch("loadingCamera", {loadingCamera: false});
                    });
            })
            .catch((err) => {
                console.error("Error initializing camera:", err);
                Livewire.dispatch("loadingCamera", {loadingCamera: false});
            });
    };

    const startScanning = (deviceId) => {
        codeReader.decodeFromVideoDevice(
            deviceId,
            "video",
            (result, err) => {
                if (result && cameraIsActive) {
                    // Vibrate on success, it needs to be done here, not in the barcodeUpdated function due to strict user activation... Silly API's
                    navigator.vibrate(300);
                    barcodeUpdated(result);
                    codeReader.reset();
                }
                if (err && !(err instanceof NotFoundException)) {
                    console.error(err);
                    document.getElementById("result").textContent = err;
                    codeReader.reset();
                }
            }
        );

        console.log(`Started continuous decode from camera with id ${deviceId}`);
    };

    const barcodeUpdated = (result) => {
        Livewire.dispatch("result", [result]);
        Livewire.dispatch("barcodeScanned");
        cameraIsActive = false;
        Livewire.dispatch('camera');
    }

    // Function to ask for camera permissions and initialize the camera
    const askForPermissionAndInitializeCamera = () => {
        navigator.mediaDevices
            .getUserMedia({ video: true })
            .then(() => {
                // Once permission is granted, initialize the camera
                initializeCamera();
            })
            .catch((err) => {
                console.error("Permission denied or error:", err);
                alert("Camera access is required to scan.");
                Livewire.dispatch("loadingCamera", {loadingCamera: false
                });
            });
    };

    // Check if the Permissions API is available and if camera permission has already been granted
    if (navigator.permissions) {
        navigator.permissions
            .query({ name: "camera" })
            .then((permissionStatus) => {
                if (permissionStatus.state === "granted") {
                    // Permission already granted, initialize camera
                    initializeCamera();
                } else if (permissionStatus.state === "prompt") {
                    // Permission not granted yet, ask for permission
                    askForPermissionAndInitializeCamera();
                } else {
                    // Permission denied
                    alert(
                        "Camera access denied. Please enable camera permissions to use this feature."
                    );
                    Livewire.dispatch("loadingCamera", {loadingCamera: false
                    });
                }

                // Listen for permission changes and initialize the camera if granted
                permissionStatus.onchange = () => {
                    if (permissionStatus.state === "granted") {
                        initializeCamera();
                    }
                };
            })
            .catch((err) => {
                console.error("Error checking camera permission:", err);
                Livewire.dispatch("loadingCamera", [false]);
            });
    } else {
        // If Permissions API is not supported, fall back to directly requesting the camera
        askForPermissionAndInitializeCamera();
    }

    Livewire.on("camera", () => {
        Livewire.isScanning = false;
        cameraIsActive = false;
        const video = document.getElementById("video");

        if (video.srcObject) {
            const tracks = video.srcObject.getTracks();
            tracks.forEach((track) => track.stop());
            video.srcObject = null;
        }
        if (codeReader) {
            codeReader.reset();
        }
    });
}
