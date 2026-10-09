import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.Pusher = Pusher;

const pageScheme = window.location.protocol === 'https:' ? 'https' : 'http';
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || pageScheme;
const reverbPort = import.meta.env.VITE_REVERB_PORT || window.location.port || (reverbScheme === 'https' ? 443 : 80);
const reverbPath = import.meta.env.VITE_REVERB_PATH || '/app';
const wsHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;

if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        wsPath: reverbPath,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
