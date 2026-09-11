export default {
    items: [],
    unreadCount: 0,
    toasts: [],
    nextToastId: 1,
    lastSeenId: 0,

    init(seed = [], unreadCount = 0) {
        this.items = seed;
        this.unreadCount = unreadCount;
        this.lastSeenId = seed.reduce((max, n) => Math.max(max, n.id ?? 0), 0);

        const userId = window.__authUserId;
        if (! userId) {
            return;
        }

        if (window.Echo) {
            window.Echo.private(`user.${userId}`).listen('.notification.created', (payload) => {
                this.push(payload);
            });
        }

        // Fallback for when the websocket connection never came up (blocked by a
        // network/firewall, or the broadcast server hiccuped) — poll every minute
        // so the dashboard still reflects new activity without a manual refresh.
        setInterval(() => this.poll(), 60000);
    },

    async poll() {
        if (! window.axios || ! window.__notificationsPollUrl) {
            return;
        }

        try {
            const { data } = await window.axios.get(window.__notificationsPollUrl);
            const fresh = (data.latest ?? []).filter((n) => n.id > this.lastSeenId).reverse();

            fresh.forEach((n) => this.push({ ...n, unread_count: data.unread_count }));

            if (fresh.length === 0) {
                this.unreadCount = data.unread_count ?? this.unreadCount;
            }
        } catch {
            /* best-effort — try again next tick */
        }
    },

    push(payload) {
        if (payload.id && payload.id <= this.lastSeenId) {
            return;
        }
        this.lastSeenId = Math.max(this.lastSeenId, payload.id ?? 0);

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
