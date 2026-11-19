import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const useTLS = (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https';
const configuredHost = import.meta.env.VITE_REVERB_HOST;
const isLocalAlias = (h) => h === 'localhost' || h === '127.0.0.1';
const wsHost = (isLocalAlias(configuredHost) && isLocalAlias(window.location.hostname))
    ? window.location.hostname
    : configuredHost;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: useTLS,
    enabledTransports: useTLS ? ['ws', 'wss'] : ['ws'],
});

window.dispatchEvent(new CustomEvent('ny:echo-ready'));

export default window.Echo;
