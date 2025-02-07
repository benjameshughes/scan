import {BrowserMultiFormatReader, NotFoundException} from "@zxing/library";

window.addEventListener('load', function () {

    // Livewire.on('startScan', () => {
    //
    // });

    Livewire.dispatch('loadingCamera', [true]);
    let cameraIsActive = true;

    // Function to initialize the camera and start barcode scanning
    const initializeCamera = () => {

        const codeReader = new BrowserMultiFormatReader()

        // Request the list of available video input devices
        codeReader.listVideoInputDevices()
            .then((videoInputDevices) => {
                if (videoInputDevices.length === 0) {
                    console.error('No video input devices found.');
                    Livewire.dispatch('loadingCamera', [false]);
                    return;
                }

                // Find the back camera (rear-facing) using facingMode constraint
                let selectedDevice = null;

                // Try to select the back camera explicitly using 'facingMode' constraint
                navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment', // This selects the back camera
                        aspectRatio: 1,
                        width: {ideal: 1920},
                        height: {ideal: 1080},
                    }
                })
                    .then(stream => {
                        // Once the camera stream is obtained, we use the selected device
                        const videoTracks = stream.getVideoTracks();
                        if (videoTracks.length > 0) {
                            selectedDevice = videoTracks[0].getSettings().deviceId;

                            // Allow the user to turn the torch on or off
                            Livewire.on('torchOn', () => {
                                selectedDevice.applyConstraints({
                                    advanced: [{torch: true}]
                                }).then(r => Livewire.dispatch('torchOn'));
                            });

                            Livewire.on('torchOff', () => {
                                selectedDevice.applyConstraints({
                                    advanced: [{torch: false}]
                                }).then(r => {
                                        Livewire.dispatch('torchOff')
                                    }
                                );
                            });
                        }


                        // Start continuous barcode scanning from the selected video device
                        codeReader.decodeFromVideoDevice(selectedDevice, 'video', (result, err) => {
                            if (result) {
                                console.log(result);
                                Livewire.dispatch('result', [result]);
                                Livewire.dispatch('barcodeScanned');
                                cardReader.reset();
                                navigator.vibrate(500);
                            }
                            if (err && !(err instanceof NotFoundException)) {
                                console.error(err);
                                document.getElementById('result').textContent = err;
                                cardReader.reset();
                            }
                        });

                        console.log(`Started continuous decode from camera with id ${selectedDevice}`);

                        stream.getTracks().forEach(track => track.stop()); // Stop the stream once we get the device ID

                    })
                    .catch((err) => {
                        console.error('Error accessing back camera:', err);
                        alert('Unable to access the back camera.');
                        Livewire.dispatch('loadingCamera', [false]);
                    });
            })
            .catch((err) => {
                console.error('Error initializing camera:', err);
                Livewire.dispatch('loadingCamera', [false]);
            });
    };

    // Function to ask for camera permissions and initialize the camera
    const askForPermissionAndInitializeCamera = () => {
        navigator.mediaDevices.getUserMedia({video: true})
            .then(() => {
                // Once permission is granted, initialize the camera
                initializeCamera();
            })
            .catch((err) => {
                console.error('Permission denied or error:', err);
                alert('Camera access is required to scan.');
                Livewire.dispatch('loadingCamera', [false]);
            });
    };

    // Check if the Permissions API is available and if camera permission has already been granted
    if (navigator.permissions) {
        navigator.permissions.query({name: 'camera'}).then(permissionStatus => {
            if (permissionStatus.state === 'granted') {
                // Permission already granted, initialize camera
                initializeCamera();
            } else if (permissionStatus.state === 'prompt') {
                // Permission not granted yet, ask for permission
                askForPermissionAndInitializeCamera();
            } else {
                // Permission denied
                alert('Camera access denied. Please enable camera permissions to use this feature.');
                Livewire.dispatch('loadingCamera', [false]);
            }

            // Listen for permission changes and initialize the camera if granted
            permissionStatus.onchange = () => {
                if (permissionStatus.state === 'granted') {
                    initializeCamera();
                }
            };
        }).catch((err) => {
            console.error('Error checking camera permission:', err);
            Livewire.dispatch('loadingCamera', [false]);
        });
    } else {
        // If Permissions API is not supported, fall back to directly requesting the camera
        askForPermissionAndInitializeCamera();
    }

    // Flash the camera
    Livewire.on('flashOn', () => {
        const video = document.getElementById('video');
        if (video.srcObject && video.srcObject.active) {
            video.srcObject.getVideoTracks()[0].applyConstraints({
                advanced: [{torch: true}]
            });
            $wire.dispatch('flashOn');
        } else {
            $wire.dispatch('flashOff');
        }
    });

    Livewire.on('stopScan', () => {
        let cameraIsActive = false;
        const video = document.getElementById('video');

        if (video.srcObject) {
            const tracks = video.srcObject.getTracks();
            tracks.forEach(track => track.stop());
            video.srcObject = null;
        }
    });


})