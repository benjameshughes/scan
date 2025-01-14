import {BrowserMultiFormatReader, NotFoundException} from "@zxing/library";

window.addEventListener('livewire:initialized', function () {
    let selectedDeviceId;
    let scannedCode;

    const codeReader = new BrowserMultiFormatReader()
    const exceptionHandler = new NotFoundException()

    const sourceSelect = document.getElementById('sourceSelect')
    const sourceSelectPanel = document.getElementById('sourceSelectPanel')
    const resultDisplay = document.getElementById('result')

    console.log('Boobies')
    Livewire.on('startScan', () => {
        Livewire.dispatch('loadingCamera', [true]);

        // Function to initialize the camera and start barcode scanning
        const initializeCamera = () => {
            // Request the list of available video input devices
            codeReader.listVideoInputDevices()
                .then((videoInputDevices) => {
                    if (videoInputDevices.length === 0) {
                        console.error('No video input devices found.');
                        Livewire.dispatch('loadingCamera', [false]);
                        return;
                    }

                    // Find the back camera (rear-facing)
                    let selectedDevice = null;
                    for (let i = 0; i < videoInputDevices.length; i++) {
                        const device = videoInputDevices[i];
                        // Check if this device is a back camera (not front-facing)
                        if (device.label.toLowerCase().includes('back') || device.kind === 'videoinput') {
                            selectedDevice = device;
                            break; // Found the back camera, no need to continue searching
                        }
                    }

                    // If no back camera is found, default to the first available camera
                    if (!selectedDevice) {
                        selectedDevice = videoInputDevices[0]; // fallback to the first device
                    }

                    selectedDeviceId = selectedDevice.deviceId;

                    // If there are multiple devices, show a dropdown to let the user choose the camera
                    if (videoInputDevices.length > 1) {
                        videoInputDevices.forEach((element) => {
                            const sourceOption = document.createElement('option');
                            sourceOption.text = element.label;
                            sourceOption.value = element.deviceId;
                            sourceSelect.appendChild(sourceOption);
                        });

                        // Change the selected device when the user picks another camera
                        sourceSelect.onchange = () => {
                            selectedDeviceId = sourceSelect.value;
                        };

                        // Optionally show the source select panel (you can modify this part based on your needs)
                        // sourceSelectPanel.style.display = 'block';
                    } else {
                        sourceSelectPanel.style.display = 'none';
                    }

                    // Start continuous barcode scanning from the selected video device
                    codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
                        if (result) {
                            console.log(result);
                            Livewire.dispatch('result', [result]);
                            cardReader.reset();
                        }
                        if (err && !(err instanceof NotFoundException)) {
                            console.error(err);
                            document.getElementById('result').textContent = err;
                        }
                    });

                    console.log(`Started continuous decode from camera with id ${selectedDeviceId}`);

                    Livewire.on('stopScan', () => {
                        codeReader.reset();
                        resultDisplay.textContent = '';
                        Livewire.dispatch('loadingCamera', [false]);
                        console.log('Camera reset.');
                    });

                })
                .catch((err) => {
                    console.error('Error initializing camera:', err);
                    Livewire.dispatch('loadingCamera', [false]);
                });
        };

        // Function to ask for camera permissions and initialize the camera
        const askForPermissionAndInitializeCamera = () => {
            navigator.mediaDevices.getUserMedia({ video: true })
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
            navigator.permissions.query({ name: 'camera' }).then(permissionStatus => {
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
    });



})