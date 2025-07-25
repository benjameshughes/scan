import './bootstrap';
import './flashmessage';

// Alpine.js Theme Store
document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        // Available theme colors
        colors: [
            // Classic Blues
            'blue', 'sky', 'cyan', 'indigo', 'navy',
            // Nature Greens
            'green', 'emerald', 'teal', 'lime', 'forest',
            // Warm Colors
            'red', 'orange', 'amber', 'yellow', 'coral',
            // Cool Purples & Pinks
            'purple', 'violet', 'pink', 'rose', 'fuchsia',
            // Neutrals & Earthy
            'slate', 'gray', 'stone', 'zinc', 'neutral'
        ],

        // Current theme color
        current: 'blue',

        // Initialize theme from localStorage
        init() {
            const stored = localStorage.getItem('stockscan.theme-color');
            if (stored && this.colors.includes(stored)) {
                this.current = stored;
            }
            this.apply();
        },

        // Apply theme color to DOM
        apply() {
            // Remove all existing theme classes
            const classList = document.documentElement.classList;
            this.colors.forEach(color => {
                classList.remove(`theme-${color}`);
            });

            // Add current theme class (blue is default, no class needed)
            if (this.current !== 'blue') {
                classList.add(`theme-${this.current}`);
            }
        },

        // Set new theme color
        set(color) {
            if (this.colors.includes(color)) {
                this.current = color;
                localStorage.setItem('stockscan.theme-color', color);
                this.apply();
                
                // Dispatch event for other components
                window.dispatchEvent(new CustomEvent('theme-color-changed', {
                    detail: { color }
                }));
            }
        },

        // Get current theme
        get() {
            return this.current;
        }
    });
});

// Register Service Worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registration successful:', registration.scope);
            })
            .catch(err => {
                console.log('ServiceWorker registration failed:', err);
            });
    });
}

// PWA Install Prompt Handler
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    console.log('🎉 beforeinstallprompt Event fired - PWA is installable!');
    console.log('Event details:', e);
    
    // Don't prevent default to allow Chrome to show install option
    deferredPrompt = e;
    
    // Create install button dynamically
    const installButton = document.createElement('button');
    installButton.textContent = '📱 Install Stock Scanner';
    installButton.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: #2563eb;
        color: white;
        border: none;
        padding: 12px 16px;
        border-radius: 8px;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        cursor: pointer;
        font-size: 14px;
    `;
    
    installButton.onclick = () => {
        console.log('Install button clicked');
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                console.log('User choice:', choiceResult.outcome);
                if (choiceResult.outcome === 'accepted') {
                    console.log('✅ User accepted the install prompt');
                } else {
                    console.log('❌ User dismissed the install prompt');
                }
                deferredPrompt = null;
                installButton.remove();
            });
        }
    };
    
    document.body.appendChild(installButton);
    
    // Remove button after 15 seconds
    setTimeout(() => {
        if (installButton.parentNode) {
            installButton.remove();
            console.log('Install button auto-removed after timeout');
        }
    }, 15000);
});

window.addEventListener('appinstalled', (evt) => {
    console.log('PWA was installed successfully');
    deferredPrompt = null;
});