@auth
    @php
        $isRtl = (bool) (config('locales.supported.' . app()->getLocale() . '.rtl') ?? false);
    @endphp
    <script>
        window.__authUserId = {{ auth()->id() }};
        window.__notifUnreadCount = {{ (int) ($unreadNotifications ?? 0) }};
        window.__notifSeed = {!! ($recentNotifications ?? collect())->toJson() !!};
        window.__notificationsReadAllUrl = @json(route('notifications.read-all'));
        window.__vapidPublicKey = @json(config('notifications.channels.push.vapid_public_key'));
    </script>
    <div x-data="{
            notifOpen: false,
            pushSupported: false,
            pushSubscribed: false,
            pushBusy: false,
            async initPush() {
                if (! window.pushNotifications) return;
                this.pushSupported = window.pushNotifications.isSupported() && Boolean(window.__vapidPublicKey);
                if (this.pushSupported) this.pushSubscribed = await window.pushNotifications.isSubscribed();
            },
            async togglePush() {
                if (this.pushBusy) return;
                this.pushBusy = true;
                try {
                    if (this.pushSubscribed) {
                        await window.pushNotifications.unsubscribe();
                        this.pushSubscribed = false;
                    } else {
                        await window.pushNotifications.subscribe();
                        this.pushSubscribed = true;
                    }
                } catch (e) {
                    /* permission denied or unsupported — silently leave state unchanged */
                } finally {
                    this.pushBusy = false;
                }
            },
        }"
        x-init="initPush()" class="relative">
        <button @click="notifOpen = !notifOpen" @click.outside="notifOpen = false"
            class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-brand-400"
            aria-label="{{ __('messages.nav.notifications') }}">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 9a6 6 0 1 1 12 0c0 5 2 6 2 6H4s2-1 2-6z" stroke-linejoin="round"/><path d="M10 20a2 2 0 0 0 4 0" stroke-linecap="round"/></svg>
            <span x-show="$store.notifications.unreadCount > 0" x-cloak
                class="absolute -right-0.5 -top-0.5 inline-flex min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                x-text="$store.notifications.unreadCount > 9 ? '9+' : $store.notifications.unreadCount"></span>
        </button>

        <div x-show="notifOpen" x-cloak x-transition
            class="absolute {{ $isRtl ? 'left-0' : 'right-0' }} mt-2 w-80 rounded-xl border border-slate-200 bg-white py-2 shadow-lg dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between px-4 py-1.5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('messages.nav.notifications') }}</p>
                <button type="button" @click="$store.notifications.markAllRead()" class="text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">Mark all read</button>
            </div>
            <div class="max-h-80 overflow-y-auto">
                <template x-if="$store.notifications.items.length === 0">
                    <p class="px-4 py-6 text-center text-sm text-slate-400">No notifications yet.</p>
                </template>
                <template x-for="n in $store.notifications.items" :key="n.id">
                    <a :href="n.url ?? '{{ route('notifications.index') }}'"
                        class="block border-t border-slate-50 px-4 py-3 text-sm transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700"
                        :class="n.read_at ? 'text-slate-500' : 'font-semibold text-slate-800 dark:text-slate-100'">
                        <p x-text="n.title"></p>
                        <p class="mt-0.5 text-xs font-normal text-slate-400" x-text="n.body"></p>
                    </a>
                </template>
            </div>
            <div x-show="pushSupported" x-cloak class="border-t border-slate-100 px-4 py-2 dark:border-slate-700">
                <button type="button" @click="togglePush()" :disabled="pushBusy"
                    class="flex w-full items-center justify-between text-xs font-medium text-slate-500 transition hover:text-brand-700 disabled:cursor-wait disabled:opacity-60 dark:text-slate-400 dark:hover:text-brand-400">
                    <span class="flex items-center gap-1.5">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9a6 6 0 1 1 12 0c0 5 2 6 2 6H4s2-1 2-6z" stroke-linejoin="round"/></svg>
                        <span x-text="pushSubscribed ? 'Push notifications on' : 'Enable push notifications'"></span>
                    </span>
                    <span class="relative inline-flex h-4 w-7 shrink-0 items-center rounded-full transition" :class="pushSubscribed ? 'bg-brand-600' : 'bg-slate-300 dark:bg-slate-600'">
                        <span class="inline-block h-3 w-3 transform rounded-full bg-white transition" :class="pushSubscribed ? 'translate-x-3.5' : 'translate-x-0.5'"></span>
                    </span>
                </button>
            </div>
            <div class="border-t border-slate-100 px-4 pt-2 dark:border-slate-700">
                <a href="{{ route('notifications.index') }}" class="block py-1 text-center text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">View all</a>
            </div>
        </div>
    </div>
@endauth
