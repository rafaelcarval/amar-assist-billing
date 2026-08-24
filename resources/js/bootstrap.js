import api from './lib/http';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = api;
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',

    key: import.meta.env.VITE_PUSHER_APP_KEY,

    cluster:
        import.meta.env.VITE_PUSHER_APP_CLUSTER
        ?? 'mt1',

    wsHost:
        import.meta.env.VITE_PUSHER_HOST
        ?? window.location.hostname,

    wsPort: Number(
        import.meta.env.VITE_PUSHER_PORT
        ?? 6001
    ),

    wssPort: Number(
        import.meta.env.VITE_PUSHER_PORT
        ?? 6001
    ),

    forceTLS:
        import.meta.env.VITE_PUSHER_SCHEME
        === 'https',

    encrypted: false,

    disableStats: true,

    enabledTransports: [
        'ws',
        'wss',
    ],

    authEndpoint: '/broadcasting/auth',
});