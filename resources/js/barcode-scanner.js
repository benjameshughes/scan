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

        // Check for existing camera permission before starting
        if (navigator.permissions) {
            navigator.permissions.query({ name: 'camera' }).then(permissionStatus => {
                if (permissionStatus.state === 'granted') {
                    // Permission already granted
                    initializeCamera();
                } else if (permissionStatus.state === 'prompt') {
                    // Permission is not granted yet, ask for permission
                    askForPermissionAndInitializeCamera();
                } else {
                    // Permission denied
                    alert('Camera access denied. Please enable camera permissions to use this feature.');
                    Livewire.dispatch('loadingCamera', [false]);
                }

                // Listen for permission changes
                permissionStatus.onchange = () => {
                    if (permissionStatus.state === 'granted') {
                        initializeCamera();
                    }
                };
            }).catch(err => {
                console.error('Error checking camera permission:', err);
                Livewire.dispatch('loadingCamera', [false]);
            });
        } else {
            // Permissions API not supported, just initialize camera
            initializeCamera();
        }

        // Function to initialize the camera
        function initializeCamera() {
            codeReader.listVideoInputDevices()
                .then((videoInputDevices) => {
                    // Select the first device
                    selectedDeviceId = videoInputDevices[0].deviceId;

                    // List the devices in a dropdown if there are multiple
                    if (videoInputDevices.length > 1) {
                        videoInputDevices.forEach((element) => {
                            const sourceOption = document.createElement('option');
                            sourceOption.text = element.label;
                            sourceOption.value = element.deviceId;
                            sourceSelect.appendChild(sourceOption);
                        });

                        // Change selected device when selected
                        sourceSelect.onchange = () => {
                            selectedDeviceId = sourceSelect.value;
                        };

                        // Optionally show the source select panel
                        // sourceSelectPanel.style.display = 'block';
                    } else {
                        sourceSelectPanel.style.display = 'none';
                    }

                    // Start decoding from the selected video input device
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
        }

        // Function to ask for permission and initialize camera
        function askForPermissionAndInitializeCamera() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(() => {
                    // If permission granted, initialize camera
                    initializeCamera();
                })
                .catch((err) => {
                    console.error('Permission denied or error:', err);
                    alert('Camera access is required to scan.');
                    Livewire.dispatch('loadingCamera', [false]);
                });
        }
    });

})