const CACHE = 'sahoulet-v2';
const PRECACHE = ['/offline.html', '/icons/icon.svg'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Only handle GET; never touch POST/auth so sessions stay intact.
    if (req.method !== 'GET') return;

    // Network-first for page navigations, with an offline fallback.
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // Vite build assets are content-hashed (immutable per URL) — safe to
    // cache-first and reuse across sessions without ever going stale.
    const url = new URL(req.url);
    if (url.origin === self.location.origin && url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(req).then((cached) => cached || fetch(req).then((res) => {
                if (res.ok) {
                    const clone = res.clone();
                    caches.open(CACHE).then((cache) => cache.put(req, clone));
                }
                return res;
            }))
        );
        return;
    }

    // Everything else (API calls, dynamic/authenticated HTML): pass through,
    // no caching, so nothing stale or private ever gets served offline.
});

// Web Push — display a notification when the backend sends one via PushChannel.
self.addEventListener('push', (event) => {
    if (! event.data) return;

    let payload = {};
    try {
        payload = event.data.json();
    } catch (e) {
        payload = { title: 'Sahoulet', body: event.data.text() };
    }

    const title = payload.title || 'Sahoulet';
    const options = {
        body: payload.body || '',
        icon: '/icons/icon.svg',
        badge: '/icons/icon.svg',
        data: { url: payload.url || '/' },
        tag: payload.tag || undefined,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data && event.notification.data.url ? event.notification.data.url : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url === url && 'focus' in client) return client.focus();
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});
