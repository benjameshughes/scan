import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const requiredEnvVars = [
    'VITE_REVERB_APP_KEY',
    'VITE_REVERB_HOST'
];

requiredEnvVars.forEach(varName => {
    if (!import.meta.env[varName]) {
        throw new Error(`Missing required environment variable: ${varName}`);
    }
});

const isDev = import.meta.env.DEV;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: isDev ? 80 : (import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: isDev ? 443 : (import.meta.env.VITE_REVERB_PORT ?? 443),
    forceTLS: isDev ? false : ((import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https'),
    enabledTransports: ['ws', 'wss'],
});
