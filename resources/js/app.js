import './bootstrap';
import './echo';
import './charts';
import './geolocation';
import './address-map';
import './pk-format';
import './file-drop';
import './ui';
import './push';
import './register-otp';

import Alpine from 'alpinejs';
import notifications from './stores/notifications';

window.Alpine = Alpine;
Alpine.store('notifications', notifications);
Alpine.start();

Alpine.store('notifications').init(window.__notifSeed ?? [], window.__notifUnreadCount ?? 0);

// Register the PWA service worker (best-effort; never blocks the app).
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            /* PWA unavailable — app still works normally */
        });
    });
}