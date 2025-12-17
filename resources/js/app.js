import './bootstrap';
import './flashmessage';
import './pwa-camera-lifecycle';

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

        // Initialize theme from user settings or localStorage
        init() {
            // Clean approach: read from data attribute on body (set by PHP/Blade)
            const userTheme = document.body.dataset.userTheme || null;
            const stored = localStorage.getItem('stockscan.theme-color');

            if (userTheme && this.colors.includes(userTheme)) {
                // Prioritize database settings for logged-in users
                this.current = userTheme;
                // Sync to localStorage to persist across page loads
                localStorage.setItem('stockscan.theme-color', userTheme);
            } else if (stored && this.colors.includes(stored)) {
                // Fall back to localStorage for guests or if no user theme
                this.current = stored;
            }

            this.apply();
            this.setupStorageListener();
        },

        // Cross-tab synchronization via storage events
        setupStorageListener() {
            window.addEventListener('storage', (event) => {
                // Only handle our theme-color key
                if (event.key !== 'stockscan.theme-color') return;

                const newColor = event.newValue;

                // Validate and apply if different
                if (newColor && this.colors.includes(newColor) && newColor !== this.current) {
                    console.log('Theme synced from another tab:', newColor);
                    this.current = newColor;
                    this.apply();

                    // Dispatch event for components that need to know
                    window.dispatchEvent(new CustomEvent('theme-color-changed', {
                        detail: { color: newColor, source: 'storage-sync' }
                    }));
                }
            });
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

// Register Service Worker for PWA (production only)
if ('serviceWorker' in navigator && import.meta.env.PROD) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registration successful:', registration.scope);

                // Check for updates periodically (every 60 seconds)
                setInterval(() => {
                    registration.update();
                }, 60 * 1000);

                // Listen for new service worker waiting
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New version available - show banner
                                showUpdateBanner(registration);
                            }
                        });
                    }
                });

                // Also check if there's already a waiting worker
                if (registration.waiting) {
                    showUpdateBanner(registration);
                }
            })
            .catch(err => {
                console.log('ServiceWorker registration failed:', err);
            });
    });

    // Handle controller change (new SW activated)
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        console.log('New service worker activated, reloading...');
        window.location.reload();
    });
}

// Show "New version available" banner
function showUpdateBanner(registration) {
    // Don't show if banner already exists
    if (document.getElementById('update-banner')) return;

    const banner = document.createElement('div');
    banner.id = 'update-banner';
    banner.innerHTML = `
        <div style="
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 99999;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideDown 0.3s ease-out;
        ">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">🚀</span>
                <span style="font-weight: 500; font-size: 14px;">New version available</span>
            </div>
            <button id="update-refresh" style="
                background: white;
                border: none;
                color: #059669;
                padding: 8px 16px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 13px;
                cursor: pointer;
                white-space: nowrap;
            ">Refresh</button>
        </div>
    `;

    // Add animation
    const style = document.createElement('style');
    style.id = 'update-banner-style';
    style.textContent = `
        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    document.body.appendChild(banner);

    // Handle refresh button
    document.getElementById('update-refresh').onclick = () => {
        if (registration.waiting) {
            // Tell waiting SW to skip waiting and activate
            registration.waiting.postMessage({ type: 'SKIP_WAITING' });
        } else {
            window.location.reload();
        }
    };

    console.log('Update banner shown - new version waiting');
}

// PWA Background Recovery - force reload if backgrounded too long
let backgroundedAt = null;
const MAX_BACKGROUND_TIME = 5 * 60 * 1000; // 5 minutes

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        backgroundedAt = Date.now();
        console.log('App backgrounded at:', new Date().toISOString());
    } else if (backgroundedAt) {
        const elapsed = Date.now() - backgroundedAt;
        console.log('App foregrounded after:', Math.round(elapsed / 1000), 'seconds');

        if (elapsed > MAX_BACKGROUND_TIME) {
            console.log('App was backgrounded too long, reloading for fresh state...');
            window.location.reload();
        }
        backgroundedAt = null;
    }
});

// Catch unhandled errors that might cause white screen
window.addEventListener('error', (e) => {
    console.error('Unhandled error:', e.message, e.filename, e.lineno);
});

window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled promise rejection:', e.reason);
});

// PWA Install Prompt Handler
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    console.log('🎉 beforeinstallprompt Event fired - PWA is installable!');
    console.log('Event details:', e);
    
    // Only show on Android mobile devices
    const isAndroid = /Android/i.test(navigator.userAgent);
    const isMobile = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    
    if (!isAndroid || !isMobile) {
        console.log('Install prompt skipped - not Android mobile device');
        return;
    }
    
    // Check if already installed/running as PWA
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
    const isInWebApk = window.navigator.standalone === true; // iOS
    const isRunningAsPWA = isStandalone || isInWebApk || document.referrer.includes('android-app://');
    
    if (isRunningAsPWA) {
        console.log('Install prompt skipped - app already running as PWA');
        return;
    }
    
    // Additional check: if user previously dismissed or installed
    const installDismissed = localStorage.getItem('pwa-install-dismissed');
    const installDate = localStorage.getItem('pwa-install-date');
    
    // Don't show again if dismissed in last 7 days
    if (installDismissed) {
        const dismissedDate = new Date(installDismissed);
        const daysSinceDismissed = (Date.now() - dismissedDate.getTime()) / (1000 * 60 * 60 * 24);
        if (daysSinceDismissed < 7) {
            console.log('Install prompt skipped - recently dismissed');
            return;
        }
    }
    
    // Don't prevent default to allow Chrome to show install option
    deferredPrompt = e;
    
    // Create beautiful install banner
    const installBanner = document.createElement('div');
    installBanner.innerHTML = `
        <div style="
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            z-index: 9999;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            backdrop-filter: blur(10px);
            animation: slideUp 0.3s ease-out;
            max-width: 400px;
            margin: 0 auto;
        ">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="
                    width: 48px;
                    height: 48px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                ">📱</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 16px; margin-bottom: 4px;">
                        Install Stock Scanner
                    </div>
                    <div style="font-size: 14px; opacity: 0.9; line-height: 1.3;">
                        Add to your home screen for quick access and offline use
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 16px;">
                <button id="install-dismiss" style="
                    flex: 1;
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    color: white;
                    padding: 12px 20px;
                    border-radius: 8px;
                    font-weight: 500;
                    font-size: 14px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" 
                   onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                    Not Now
                </button>
                <button id="install-accept" style="
                    flex: 2;
                    background: white;
                    border: none;
                    color: #2563eb;
                    padding: 12px 20px;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 14px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                " onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'" 
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    Install App
                </button>
            </div>
        </div>
    `;
    
    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(100px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes slideDown {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(100px);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Add banner to page
    document.body.appendChild(installBanner);
    
    // Handle dismiss button
    document.getElementById('install-dismiss').onclick = () => {
        // Remember dismissal
        localStorage.setItem('pwa-install-dismissed', new Date().toISOString());
        
        installBanner.style.animation = 'slideDown 0.3s ease-out';
        setTimeout(() => {
            if (installBanner.parentNode) {
                installBanner.remove();
                style.remove();
            }
        }, 300);
        console.log('Install banner dismissed by user');
    };
    
    // Handle install button
    document.getElementById('install-accept').onclick = () => {
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
                installBanner.style.animation = 'slideDown 0.3s ease-out';
                setTimeout(() => {
                    if (installBanner.parentNode) {
                        installBanner.remove();
                        style.remove();
                    }
                }, 300);
            });
        }
    };
    
    // Auto-remove banner after 30 seconds
    setTimeout(() => {
        if (installBanner.parentNode) {
            installBanner.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                if (installBanner.parentNode) {
                    installBanner.remove();
                    style.remove();
                }
            }, 300);
            console.log('Install banner auto-removed after timeout');
        }
    }, 30000);
});

window.addEventListener('appinstalled', (evt) => {
    console.log('PWA was installed successfully');
    deferredPrompt = null;
});
