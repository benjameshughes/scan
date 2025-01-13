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
        Livewire.dispatch('loadingCamera', [true])
        codeReader.listVideoInputDevices()
            .then((videoInputDevices) => {
                // Select the first device
                selectedDeviceId = videoInputDevices[0].deviceId
                // List the devices
                if (videoInputDevices.length > 1) {
                    videoInputDevices.forEach((element) => {
                        const sourceOption = document.createElement('option')
                        sourceOption.text = element.label
                        sourceOption.value = element.deviceId
                        sourceSelect.appendChild(sourceOption)
                    })
                    // Change selected device when changed
                    sourceSelect.onchange = () => {
                        selectedDeviceId = sourceSelect.value;
                    };

                    // Do I want to show the source select panel?
                    // sourceSelectPanel.style.display = 'block'
                }else{
                    sourceSelectPanel.style.display = 'none'
                }

                codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
                    if (result) {
                        console.log(result)
                        Livewire.dispatch('result', [result])
                    }
                    if (err && !(err instanceof NotFoundException)) {
                        console.error(err)
                        document.getElementById('result').textContent = err
                    }
                })
                console.log(`Started continous decode from camera with id ${selectedDeviceId}`)

                Livewire.on('stopScan', () => {
                    codeReader.reset()
                    resultDisplay.textContent = '';
                    Livewire.dispatch('loadingCamera', [false])
                    console.log('Reset.')
                })

            })
            .catch((err) => {
                console.error(err)
            })
    })
})