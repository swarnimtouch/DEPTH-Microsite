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

const metaValue = (name) => document.head.querySelector(`meta[name="${name}"]`)?.content?.trim();
const pageScheme = window.location.protocol === 'https:' ? 'https' : 'http';
const configuredScheme = metaValue('reverb-scheme') || import.meta.env.VITE_REVERB_SCHEME || pageScheme;
const reverbScheme = pageScheme === 'https' ? 'https' : configuredScheme;
const reverbPort = Number(metaValue('reverb-port') || import.meta.env.VITE_REVERB_PORT || window.location.port || (reverbScheme === 'https' ? 443 : 80));
const reverbPath = metaValue('reverb-path') || import.meta.env.VITE_REVERB_PATH || '';
const wsHost = metaValue('reverb-host') || import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbKey = metaValue('reverb-key') || import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    const echoOptions = {
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    };

    if (reverbPath) {
        echoOptions.wsPath = `/${reverbPath.replace(/^\/+|\/+$/g, '')}`;
    }

    window.Echo = new Echo(echoOptions);
}
