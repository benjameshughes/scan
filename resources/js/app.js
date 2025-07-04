import './bootstrap';
import './flashmessage';

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
    console.log('beforeinstallprompt Event fired - PWA is installable!');
    // Don't prevent default to allow Chrome to show install option
    deferredPrompt = e;
    
    // Create install button dynamically if Chrome doesn't show menu option
    const installButton = document.createElement('button');
    installButton.textContent = 'Install App';
    installButton.style.cssText = 'position:fixed;top:10px;right:10px;z-index:9999;background:#2563eb;color:white;border:none;padding:10px;border-radius:5px;';
    installButton.onclick = () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                console.log('User choice:', choiceResult.outcome);
                deferredPrompt = null;
                installButton.remove();
            });
        }
    };
    document.body.appendChild(installButton);
    
    // Remove button after 10 seconds
    setTimeout(() => installButton.remove(), 10000);
});

window.addEventListener('appinstalled', (evt) => {
    console.log('PWA was installed successfully');
    deferredPrompt = null;
});