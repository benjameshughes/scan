/**
 * Firstly, show the back camera in the video element
 */
import {BrowserMultiFormatReader} from "@zxing/library";
document.addEventListener('livewire:init', function () {

    // Initialize the ZXing Barcode Reader
    const codeReader = new BrowserMultiFormatReader();

// Get a custom video stream (e.g., from a camera)
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: 1920, height: 1080 } })
        .then(function (stream) {
            const video = document.getElementById('video'); // Assume you have a <video> element with id 'video'

            if (!video) {
                console.error("Video element not found.");
                return; // Exit if video element doesn't exist
            }

            // Attach the custom media stream to the video element
            video.srcObject = stream;

            // Wait for the video to be ready before starting playback
            video.onloadedmetadata = function () {
                if (video.paused && video.readyState >= 3) { // Play only if it's not already playing
                    video.play();
                }
            };

            // Start decoding once the video is playing
            video.onplaying = function () {
                // This will continuously decode frames from the stream
                startContinuousDecoding(stream, video);
            };

            console.log("Video stream is ready. Starting continuous decoding...");

        })
        .catch(function (err) {
            console.error("Error accessing camera:", err);
        });

// Function to start continuous decoding from the custom stream
    function startContinuousDecoding(stream, video) {
        // Use decodeFromStream to continuously decode frames from the custom video stream
        const intervalId = setInterval(() => {
            codeReader.decodeOnceFromStream(stream, video)
                .then(result => {
                    if (result) {
                        console.log("Decoded barcode:", result.text);
                        document.getElementById('result').textContent = result.text;

                        // Optionally, you can dispatch the barcode result to Livewire or any other backend
                        Livewire.dispatch('barcode', result.text);

                        // If you want to stop decoding after a successful scan, you can clear the interval
                        // clearInterval(intervalId);
                    }
                })
                .catch(err => {
                    if (!(err instanceof ZXing.NotFoundException)) {
                        console.error("Error during decoding:", err);
                        document.getElementById('result').textContent = 'Error: ' + err;
                    }
                });
        }, 100); // Adjust the interval (in ms) based on how frequently you want to scan

        // If you want to stop decoding after a set time or event
        // setTimeout(() => clearInterval(intervalId), 10000);  // Stop after 10 seconds (example)
    }

// Function to stop the stream manually if needed
    function stopStream(stream) {
        stream.getTracks().forEach(track => track.stop());
        console.log("Stream stopped");
    }


});