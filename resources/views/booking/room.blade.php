@extends('layouts.app')

@section('title', 'Booking room — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ $backUrl }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to booking</a>

    <div
        x-data="bookingRoom({
            bookingId: {{ $booking->id }},
            currentUserId: {{ auth()->id() }},
            canSend: {{ $booking->isCommunicable() ? 'true' : 'false' }},
            isProvider: {{ $isProvider ? 'true' : 'false' }},
            canShare: {{ ($isProvider && $booking->canShareLocation()) ? 'true' : 'false' }},
            sendUrl: '{{ route('bookings.messages.store', $booking) }}',
            trackUrl: '{{ route('bookings.tracking.store', $booking) }}',
            messages: @js($messages),
            tracking: @js($tracking),
            status: @js($booking->status),
            statusLabel: @js(match ($booking->status) {
                'pending' => 'Pending', 'confirmed' => 'Confirmed', 'in_progress' => 'In progress',
                'completed' => 'Completed', 'cancelled' => 'Cancelled', default => ucfirst($booking->status),
            })
        })"
    >
    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $booking->service->name }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">With {{ $otherParty }} · {{ $booking->reference }}</p>
        </div>
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold transition"
            :class="statusClasses()" x-text="statusLabel"></span>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        {{-- Chat --}}
        <div class="lg:col-span-2">
            <div class="flex h-[32rem] flex-col rounded-2xl border border-slate-200 bg-slate-50 shadow-sm dark:border-slate-800 dark:bg-slate-800/70">
                <div class="flex items-center justify-between border-b border-slate-200 bg-white px-5 py-3 dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Messages</h2>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500">Updates live when the messaging server is on</span>
                </div>

                <div x-ref="thread" class="flex-1 space-y-3 overflow-y-auto px-5 py-4">
                    <template x-for="m in messages" :key="m.id">
                        <div class="flex flex-col" :class="m.sender_id === currentUserId ? 'items-end' : 'items-start'">
                            <div
                                class="inline-block max-w-[80%] rounded-2xl px-3.5 py-2 text-sm"
                                :class="m.sender_id === currentUserId ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200'">
                                <span x-text="m.body"></span>
                            </div>
                            <span class="mt-1 text-[11px] text-slate-400 dark:text-slate-500" x-text="m.sender_name + ' · ' + m.created_at"></span>
                        </div>
                    </template>
                    <template x-if="messages.length === 0">
                        <p class="py-10 text-center text-sm text-slate-400 dark:text-slate-500">No messages yet. Start the conversation.</p>
                    </template>
                </div>

                <div class="border-t border-slate-200 bg-white px-5 py-3 dark:border-slate-800 dark:bg-slate-900">
                    <template x-if="canSend">
                        <form @submit.prevent="send()" class="flex items-center gap-2">
                            <input x-model="draft" type="text" maxlength="2000" placeholder="Type a message…"
                                class="flex-1 rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Send</button>
                        </form>
                    </template>
                    <template x-if="! canSend">
                        <p class="text-center text-xs text-slate-400 dark:text-slate-500">This conversation is closed.</p>
                    </template>
                    <p x-show="error" x-cloak x-text="error" class="mt-2 text-xs text-red-600 dark:text-red-400"></p>
                </div>
            </div>
        </div>

        {{-- Tracking --}}
        <aside class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Live tracking</h2>

                <div class="mt-4">
                    <div x-show="tracking" x-cloak x-ref="mapEl" class="h-56 w-full rounded-xl border border-slate-200 dark:border-slate-800"></div>
                    <template x-if="tracking">
                        <div class="mt-3 space-y-2 rounded-xl bg-slate-50 p-4 text-sm dark:bg-slate-800/70">
                            <p x-show="tracking.note" x-cloak class="rounded-lg bg-white px-3 py-2 text-xs text-slate-600 dark:bg-slate-900 dark:text-slate-300" x-text="tracking.note"></p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">Updated <span x-text="tracking.time"></span></p>
                            <a :href="mapsLink()" target="_blank" rel="noopener"
                               class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">
                                Open in Google Maps
                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7M9 7h8v8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        </div>
                    </template>
                    <template x-if="! tracking">
                        <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-400">No location shared yet.</p>
                    </template>
                </div>

                @if ($isProvider)
                    <template x-if="canShare">
                        <div class="mt-5 space-y-3">
                            <input x-model="note" type="text" maxlength="255" placeholder="Status e.g. On the way"
                                class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            <button @click="shareLocation()" :disabled="sharing"
                                class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                                <span x-show="! sharing">Share my location</span>
                                <span x-show="sharing" x-cloak>Sharing…</span>
                            </button>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Your browser will ask for location permission.</p>
                        </div>
                    </template>
                    <template x-if="! canShare">
                        <p class="mt-5 text-xs text-slate-400 dark:text-slate-500">Location sharing is available while the booking is active.</p>
                    </template>
                @else
                    <p class="mt-5 text-xs text-slate-400 dark:text-slate-500">Your provider can share their live location here once on the way.</p>
                @endif
            </div>
        </aside>
    </div>
    </div>
</section>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bookingRoom', (cfg) => ({
        bookingId: cfg.bookingId,
        currentUserId: cfg.currentUserId,
        canSend: cfg.canSend,
        isProvider: cfg.isProvider,
        canShare: cfg.canShare,
        sendUrl: cfg.sendUrl,
        trackUrl: cfg.trackUrl,
        messages: cfg.messages || [],
        tracking: cfg.tracking || null,
        status: cfg.status,
        statusLabel: cfg.statusLabel,
        draft: '',
        note: '',
        sharing: false,
        error: '',
        channel: null,

        init() {
            this.$nextTick(() => this.scrollDown());
            if (this.tracking) {
                this.$nextTick(() => this.renderMapPosition());
            }

            if (window.Echo) {
                try {
                    this.channel = window.Echo.private('booking.' + this.bookingId);
                    this.channel.listen('.message.sent', (e) => this.pushMessage(e));
                    this.channel.listen('.location.updated', (e) => this.applyTracking(e));
                    this.channel.listen('.status.updated', (e) => {
                        this.status = e.status;
                        this.statusLabel = e.status_label;
                    });
                } catch (err) {
                    console.warn('Realtime unavailable for this room.', err);
                }
            }
        },

        statusClasses() {
            const map = {
                pending: 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                confirmed: 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400',
                in_progress: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400',
                completed: 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',
                cancelled: 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400',
            };
            return map[this.status] || 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },
        reqHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrf(),
            };
        },

        pushMessage(m) {
            if (!m || this.messages.some((x) => x.id === m.id)) return;
            this.messages.push(m);
            this.$nextTick(() => this.scrollDown());
        },
        scrollDown() {
            const el = this.$refs.thread;
            if (el) el.scrollTop = el.scrollHeight;
        },

        async send() {
            const body = this.draft.trim();
            if (!body) return;
            this.error = '';
            try {
                const res = await fetch(this.sendUrl, {
                    method: 'POST',
                    headers: this.reqHeaders(),
                    body: JSON.stringify({ body }),
                });
                if (res.ok) {
                    this.pushMessage(await res.json());
                    this.draft = '';
                } else {
                    const e = await res.json().catch(() => ({}));
                    this.error = e.message || 'Could not send message.';
                }
            } catch (err) {
                this.error = 'Network error. Message not sent.';
            }
        },

        map: null,
        marker: null,

        applyTracking(t) {
            if (!t) return;
            this.tracking = { latitude: t.latitude, longitude: t.longitude, note: t.note, time: t.time };
            this.$nextTick(() => this.renderMapPosition());
        },
        renderMapPosition() {
            if (!window.L || !this.tracking || !this.$refs.mapEl) return;
            const pos = [parseFloat(this.tracking.latitude), parseFloat(this.tracking.longitude)];

            if (!this.map) {
                this.map = window.L.map(this.$refs.mapEl).setView(pos, 15);
                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(this.map);
                this.marker = window.L.marker(pos).addTo(this.map);
            } else {
                this.map.invalidateSize();
                this.marker.setLatLng(pos);
                this.map.panTo(pos);
            }
        },
        mapsLink() {
            if (!this.tracking) return '#';
            return 'https://www.google.com/maps?q=' + this.tracking.latitude + ',' + this.tracking.longitude;
        },

        shareLocation() {
            this.error = '';
            if (!navigator.geolocation) {
                this.error = 'Geolocation is not supported by your browser.';
                return;
            }
            this.sharing = true;
            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    try {
                        const res = await fetch(this.trackUrl, {
                            method: 'POST',
                            headers: this.reqHeaders(),
                            body: JSON.stringify({
                                latitude: pos.coords.latitude,
                                longitude: pos.coords.longitude,
                                note: this.note,
                            }),
                        });
                        if (res.ok) {
                            this.applyTracking(await res.json());
                        } else {
                            const e = await res.json().catch(() => ({}));
                            this.error = e.message || 'Could not share location.';
                        }
                    } catch (err) {
                        this.error = 'Network error while sharing location.';
                    } finally {
                        this.sharing = false;
                    }
                },
                (err) => {
                    this.error = err.message || 'Could not get your location.';
                    this.sharing = false;
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },
    }));
});
</script>
@endsection