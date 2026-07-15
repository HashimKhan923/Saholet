// Web Push subscribe/unsubscribe — self-contained VAPID push, no third-party
// account needed. Exposes window.pushNotifications for the notification-bell
// UI to call.

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = window.atob(base64);

    return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
}

function isSupported() {
    return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
}

async function currentSubscription() {
    const registration = await navigator.serviceWorker.ready;
    return registration.pushManager.getSubscription();
}

async function subscribe() {
    if (! isSupported()) {
        throw new Error('Push notifications are not supported in this browser.');
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        throw new Error('Notification permission was not granted.');
    }

    const registration = await navigator.serviceWorker.ready;
    const applicationServerKey = urlBase64ToUint8Array(window.__vapidPublicKey);

    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey,
    });

    await window.axios.post('/push-subscriptions', subscription.toJSON());

    return subscription;
}

async function unsubscribe() {
    const subscription = await currentSubscription();
    if (! subscription) return;

    await window.axios.delete('/push-subscriptions', { data: { endpoint: subscription.endpoint } });
    await subscription.unsubscribe();
}

window.pushNotifications = {
    isSupported,
    isSubscribed: async () => Boolean(await currentSubscription().catch(() => null)),
    subscribe,
    unsubscribe,
};
