import { BrowserMultiFormatReader, NotFoundException } from "@zxing/library";

window.addEventListener("load", function () {
    Livewire.dispatch("loadingCamera", [true]);
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
                    Livewire.dispatch("loadingCamera", [false]);
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
                    console.warn("No back camera found, using first available camera.");
                    selectedDeviceId = videoInputDevices[0].deviceId;
                }

                // Start continuous barcode scanning from the selected video device
                startScanning(selectedDeviceId);

                // Try to select the back camera explicitly using 'facingMode' constraint
                navigator.mediaDevices
                    .getUserMedia({
                        video: {
                            deviceId: { exact: selectedDeviceId }, // Use the selected device ID
                            aspectRatio: { ideal: 16 / 9 },
                            width: { ideal: 1920 },
                            height: { ideal: 1080 },
                        },
                    })
                    .then((stream) => {
                        // Once the camera stream is obtained, we use the selected device
                        const videoTracks = stream.getVideoTracks();
                        if (videoTracks.length > 0) {
                            const videoTrack = videoTracks[0];

                            // Torch control function
                            const toggleTorch = (on) => {
                                videoTrack
                                    .applyConstraints({
                                        advanced: [{ torch: on }],
                                    })
                                    .then(() => {
                                        Livewire.dispatch(on ? "torchOn" : "torchOff");
                                    })
                                    .catch((error) => {
                                        console.error(`Error turning torch ${on ? "on" : "off"}:`, error);
                                    });
                            };

                            // Allow the user to turn the torch on or off
                            Livewire.on("torchOn", () => {
                                toggleTorch(true);
                            });

                            Livewire.on("torchOff", () => {
                                toggleTorch(false);
                            });
                        }
                    })
                    .catch((err) => {
                        console.error("Error accessing back camera:", err);
                        alert("Unable to access the back camera.");
                        Livewire.dispatch("loadingCamera", [false]);
                    });
            })
            .catch((err) => {
                console.error("Error initializing camera:", err);
                Livewire.dispatch("loadingCamera", [false]);
            });
    };

    const startScanning = (deviceId) => {
        codeReader.decodeFromVideoDevice(
            deviceId,
            "video",
            (result, err) => {
                if (result && cameraIsActive) {
                    console.log(result);
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
        navigator.vibrate(300);
        cameraIsActive = false;
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
                Livewire.dispatch("loadingCamera", [false]);
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
                    Livewire.dispatch("loadingCamera", [false]);
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

    Livewire.on("stopScan", () => {
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
});
