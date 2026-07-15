export default {
    items: [],
    unreadCount: 0,
    toasts: [],
    nextToastId: 1,

    init(seed = [], unreadCount = 0) {
        this.items = seed;
        this.unreadCount = unreadCount;

        const userId = window.__authUserId;
        if (! userId || ! window.Echo) {
            return;
        }

        window.Echo.private(`user.${userId}`).listen('.notification.created', (payload) => {
            this.push(payload);
        });
    },

    push(payload) {
        this.items.unshift(payload);
        this.items = this.items.slice(0, 10);
        this.unreadCount = payload.unread_count ?? this.unreadCount + 1;

        const toastId = this.nextToastId++;
        this.toasts.push({ id: toastId, title: payload.title, body: payload.body, url: payload.url });

        setTimeout(() => {
            this.toasts = this.toasts.filter((t) => t.id !== toastId);
        }, 6000);
    },

    dismissToast(id) {
        this.toasts = this.toasts.filter((t) => t.id !== id);
    },

    markAllRead() {
        this.unreadCount = 0;
        this.items = this.items.map((n) => ({ ...n, read_at: n.read_at ?? new Date().toISOString() }));

        if (window.axios) {
            window.axios.post(window.__notificationsReadAllUrl).catch(() => {
                /* best-effort — the bell already reflects read state locally */
            });
        }
    },
};
