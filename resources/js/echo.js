import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster:  'reverb',
    key:          import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:       import.meta.env.VITE_REVERB_HOST,
    wsPort:       import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort:      import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS:     (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        },
    },
});

// Connection status
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Realtime connected');
    document.querySelector('#realtime-status')?.classList.replace('bg-red-500','bg-green-500');
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('❌ Realtime disconnected');
    document.querySelector('#realtime-status')?.classList.replace('bg-green-500','bg-red-500');
});
