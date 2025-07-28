/**
 * PWA Camera Lifecycle Manager - Alpine.js Store
 * 
 * Automatically manages camera state based on PWA lifecycle events
 * Uses existing Livewire camera controls - no changes to core scanner needed
 */

document.addEventListener('alpine:init', () => {
    Alpine.store('camera', {
        wasScanning: false,
        userStopped: false,
        autoStarted: false,
        isInitialized: false,

        // Initialize camera lifecycle management (only on scanner pages)
        init() {
            // Only run on scanner pages
            if (!document.getElementById('video')) {
                console.log('PWA Camera Store: Not a scanner page, skipping');
                return;
            }

            console.log('PWA Camera Store: Initializing on scanner page');
            this.isInitialized = true;

            // Auto-start camera after Livewire loads
            this.scheduleAutoStart();
        },

        scheduleAutoStart() {
            if (this.autoStarted || this.userStopped) return;

            console.log('PWA Camera Store: Scheduling auto-start');
            
            // Wait for Livewire to be ready
            const attemptAutoStart = () => {
                if (window.Livewire && !this.autoStarted && !this.userStopped) {
                    console.log('PWA Camera Store: Auto-starting camera');
                    this.autoStarted = true;
                    Livewire.dispatch('camera-toggle');
                } else if (!window.Livewire) {
                    // Retry if Livewire not ready yet
                    setTimeout(attemptAutoStart, 200);
                }
            };

            setTimeout(attemptAutoStart, 1000);
        },

        // Handle PWA visibility changes (background/foreground)
        handleVisibilityChange() {
            if (!this.isInitialized) return;

            if (document.hidden) {
                console.log('PWA Camera Store: App going to background');
                
                // Check if camera is currently running
                this.wasScanning = window.productScanner?.isScanning || false;
                
                if (this.wasScanning) {
                    console.log('PWA Camera Store: Stopping camera for background');
                    Livewire.dispatch('camera-toggle');
                }
                
            } else {
                console.log('PWA Camera Store: App coming to foreground');
                
                // Only restart if it was scanning before AND user hasn't manually stopped it
                if (this.wasScanning && !this.userStopped) {
                    console.log('PWA Camera Store: Restarting camera from background');
                    
                    // Small delay to ensure app is fully visible
                    setTimeout(() => {
                        Livewire.dispatch('camera-toggle');
                    }, 500);
                }
            }
        },

        // Handle window focus loss (when PWA loses focus)
        handleWindowBlur() {
            if (!this.isInitialized) return;

            console.log('PWA Camera Store: Window lost focus');
            
            this.wasScanning = window.productScanner?.isScanning || false;
            if (this.wasScanning) {
                console.log('PWA Camera Store: Stopping camera due to focus loss');
                Livewire.dispatch('camera-toggle');
            }
        },

        // Handle window focus gain
        handleWindowFocus() {
            if (!this.isInitialized) return;

            console.log('PWA Camera Store: Window gained focus');
            
            // Only restart if it was scanning before AND user hasn't manually stopped it
            if (this.wasScanning && !this.userStopped) {
                console.log('PWA Camera Store: Restarting camera from focus gain');
                
                setTimeout(() => {
                    Livewire.dispatch('camera-toggle');
                }, 300);
            }
        },

        // Track when user manually toggles camera
        handleUserToggle() {
            if (!this.isInitialized) return;

            console.log('PWA Camera Store: User manually toggled camera');
            this.userStopped = !this.userStopped;
        },

        // Reset state on navigation
        reset() {
            console.log('PWA Camera Store: Resetting state');
            this.wasScanning = false;
            this.autoStarted = false;
            this.userStopped = false;
            this.isInitialized = false;
        }
    });
});