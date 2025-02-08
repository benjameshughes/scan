import axios from 'axios';
window.axios = axios;

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
