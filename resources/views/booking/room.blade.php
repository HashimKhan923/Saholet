@extends('layouts.app')

@section('title', 'Booking room — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
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
            trackingHistory: @js($trackingHistory),
            destination: @js(($booking->latitude !== null && $booking->longitude !== null) ? [
                'lat' => (float) $booking->latitude,
                'lng' => (float) $booking->longitude,
                'address' => $booking->address,
            ] : null),
            googleMapsKey: @js(config('services.google_maps.key')),
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

    {{-- 1 (messages) : 3 (map) --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-4">
        {{-- Messages — narrow vertical bar --}}
        <div class="lg:col-span-1">
            <div class="flex h-136 flex-col rounded-2xl border border-slate-200 bg-slate-50 shadow-sm dark:border-slate-800 dark:bg-slate-800/70">
                <div class="border-b border-slate-200 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Messages</h2>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500">Live while online</span>
                </div>

                <div x-ref="thread" class="flex-1 space-y-3 overflow-y-auto px-4 py-4">
                    <template x-for="m in messages" :key="m.id">
                        <div class="flex flex-col" :class="m.sender_id === currentUserId ? 'items-end' : 'items-start'">
                            <div
                                class="inline-block max-w-full rounded-2xl px-3 py-2 text-sm wrap-break-word"
                                :class="m.sender_id === currentUserId ? 'bg-brand-600 text-white' : 'border border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200'">
                                <span x-text="m.body"></span>
                            </div>
                            <span class="mt-1 text-[10px] text-slate-400 dark:text-slate-500" x-text="m.sender_name + ' · ' + m.created_at"></span>
                        </div>
                    </template>
                    <template x-if="messages.length === 0">
                        <p class="py-10 text-center text-xs text-slate-400 dark:text-slate-500">No messages yet. Start the conversation.</p>
                    </template>
                </div>

                <div class="border-t border-slate-200 bg-white px-3 py-3 dark:border-slate-800 dark:bg-slate-900">
                    <template x-if="canSend">
                        <form @submit.prevent="send()" class="space-y-2">
                            <input x-model="draft" type="text" maxlength="2000" placeholder="Type a message…"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Send</button>
                        </form>
                    </template>
                    <template x-if="! canSend">
                        <p class="text-center text-xs text-slate-400 dark:text-slate-500">This conversation is closed.</p>
                    </template>
                    <p x-show="error" x-cloak x-text="error" class="mt-2 text-xs text-red-600 dark:text-red-400"></p>
                </div>
            </div>
        </div>

        {{-- Live tracking map — the big box --}}
        <div class="lg:col-span-3">
            <div class="flex h-136 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-slate-800">
                    <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white">Live tracking</h2>
                    <template x-if="tracking">
                        <span class="text-xs text-slate-400 dark:text-slate-500">Updated <span x-text="tracking.time"></span></span>
                    </template>
                </div>

                <div class="relative flex-1">
                    <div x-ref="mapEl" class="h-full w-full"></div>

                    <template x-if="! tracking && ! mapError">
                        <div class="absolute inset-0 flex items-center justify-center bg-slate-50 dark:bg-slate-800/70">
                            <p class="max-w-xs rounded-xl border border-dashed border-slate-300 bg-white px-5 py-6 text-center text-sm text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                                @if($isProvider)
                                    No location shared yet. Tap "Share my location" below once you're on the way.
                                @else
                                    Your provider hasn't shared their location yet. It'll appear here live once they're on the way.
                                @endif
                            </p>
                        </div>
                    </template>

                    <template x-if="mapError">
                        <div class="absolute inset-0 flex items-center justify-center bg-slate-50 dark:bg-slate-800/70">
                            <p class="max-w-xs rounded-xl border border-dashed border-red-300 bg-white px-5 py-6 text-center text-sm text-red-500 shadow-sm dark:border-red-900 dark:bg-slate-900" x-text="mapError"></p>
                        </div>
                    </template>
                </div>

                <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 bg-slate-50 px-5 py-3 dark:border-slate-800 dark:bg-slate-800/60">
                    <template x-if="tracking && tracking.note">
                        <span class="rounded-full bg-white px-3 py-1 text-xs text-slate-600 shadow-sm dark:bg-slate-900 dark:text-slate-300" x-text="tracking.note"></span>
                    </template>

                    <template x-if="tracking">
                        <a :href="mapsLink()" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400">
                            Open in Google Maps
                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7M9 7h8v8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </template>

                    @if ($isProvider)
                        <template x-if="canShare">
                            <div class="ms-auto flex flex-1 flex-wrap items-center justify-end gap-2 sm:flex-nowrap">
                                <template x-if="! liveTracking">
                                    <input x-model="note" type="text" maxlength="255" placeholder="Status e.g. On the way"
                                        class="w-full min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:max-w-xs">
                                </template>
                                <template x-if="liveTracking">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">
                                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-brand-600"></span>
                                        Live — updating automatically
                                    </span>
                                </template>
                                <button x-show="! liveTracking" @click="startLiveTracking()" :disabled="sharing"
                                    class="shrink-0 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                                    <span x-show="! sharing">Start live tracking</span>
                                    <span x-show="sharing" x-cloak>Starting…</span>
                                </button>
                                <button x-show="liveTracking" x-cloak @click="stopLiveTracking()"
                                    class="shrink-0 rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                    Stop
                                </button>
                            </div>
                        </template>
                        <template x-if="! canShare">
                            <p class="ms-auto text-xs text-slate-400 dark:text-slate-500">Location sharing is available while the booking is active.</p>
                        </template>
                    @else
                        <p class="ms-auto text-xs text-slate-400 dark:text-slate-500">Your provider's live location appears here once they're on the way — no need to refresh.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<script>
window.__initGoogleMaps = function () {
    document.dispatchEvent(new Event('google-maps-ready'));
};

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
        trackingHistory: cfg.trackingHistory || [],
        destination: cfg.destination || null,
        googleMapsKey: cfg.googleMapsKey || '',
        status: cfg.status,
        statusLabel: cfg.statusLabel,
        draft: '',
        note: '',
        sharing: false,
        liveTracking: false,
        watchId: null,
        pushIntervalId: null,
        lastKnownPos: null,
        error: '',
        mapError: '',
        channel: null,

        init() {
            this.$nextTick(() => this.scrollDown());
            this.loadGoogleMaps();

            if (window.Echo) {
                try {
                    this.channel = window.Echo.private('booking.' + this.bookingId);
                    this.channel.listen('.message.sent', (e) => this.pushMessage(e));
                    this.channel.listen('.location.updated', (e) => this.applyTracking(e));
                    this.channel.listen('.status.updated', (e) => {
                        this.status = e.status;
                        this.statusLabel = e.status_label;
                        if ((e.status === 'completed' || e.status === 'cancelled') && this.liveTracking) {
                            this.stopLiveTracking();
                        }
                    });
                } catch (err) {
                    console.warn('Realtime unavailable for this room.', err);
                }
            }
        },

        loadGoogleMaps() {
            if (!this.googleMapsKey) {
                this.mapError = 'Google Maps API key is not configured.';
                return;
            }
            if (window.google && window.google.maps) {
                this.initMap();
                return;
            }
            document.addEventListener('google-maps-ready', () => this.initMap(), { once: true });
            if (document.getElementById('google-maps-script')) return;

            const script = document.createElement('script');
            script.id = 'google-maps-script';
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(this.googleMapsKey) + '&loading=async&callback=__initGoogleMaps';
            script.async = true;
            script.defer = true;
            script.onerror = () => { this.mapError = 'Could not load Google Maps.'; };
            document.head.appendChild(script);
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
        destMarker: null,
        trail: null,
        pathCoords: [],

        initMap() {
            if (!this.$refs.mapEl || this.map) return;

            const center = this.tracking
                ? { lat: parseFloat(this.tracking.latitude), lng: parseFloat(this.tracking.longitude) }
                : (this.destination ? { lat: this.destination.lat, lng: this.destination.lng } : { lat: 24.8607, lng: 67.0011 });

            this.map = new google.maps.Map(this.$refs.mapEl, {
                center,
                zoom: 14,
                streetViewControl: false,
                mapTypeControl: false,
                fullscreenControl: false,
            });

            if (this.destination) {
                this.destMarker = new google.maps.Marker({
                    position: { lat: this.destination.lat, lng: this.destination.lng },
                    map: this.map,
                    title: this.destination.address || 'Job location',
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 8,
                        fillColor: '#c4341f',
                        fillOpacity: 1,
                        strokeColor: '#ffffff',
                        strokeWeight: 2,
                    },
                });
            }

            // Breadcrumb trail — every point recorded for this job, oldest first.
            this.pathCoords = (this.trackingHistory || []).map((p) => ({ lat: p.lat, lng: p.lng }));
            this.trail = new google.maps.Polyline({
                path: this.pathCoords,
                map: this.map,
                strokeColor: '#1a7a35',
                strokeOpacity: 0.85,
                strokeWeight: 4,
            });

            if (this.tracking) {
                this.renderMapPosition();
            }
        },

        applyTracking(t) {
            if (!t) return;
            this.tracking = { latitude: t.latitude, longitude: t.longitude, note: t.note, time: t.time };
            this.$nextTick(() => this.renderMapPosition());
        },

        renderMapPosition() {
            if (!window.google || !this.map || !this.tracking) return;
            const pos = { lat: parseFloat(this.tracking.latitude), lng: parseFloat(this.tracking.longitude) };

            if (!this.marker) {
                this.marker = new google.maps.Marker({
                    position: pos,
                    map: this.map,
                    title: 'Provider',
                });
            } else {
                this.marker.setPosition(pos);
            }

            if (this.trail) {
                const last = this.pathCoords[this.pathCoords.length - 1];
                if (!last || last.lat !== pos.lat || last.lng !== pos.lng) {
                    this.pathCoords.push(pos);
                    this.trail.setPath(this.pathCoords);
                }
            }

            if (this.destMarker) {
                const bounds = new google.maps.LatLngBounds();
                bounds.extend(pos);
                bounds.extend(this.destMarker.getPosition());
                this.map.fitBounds(bounds, 64);
            } else {
                this.map.panTo(pos);
            }
        },

        mapsLink() {
            if (!this.tracking) return '#';
            return 'https://www.google.com/maps?q=' + this.tracking.latitude + ',' + this.tracking.longitude;
        },

        /**
         * Foodpanda/Bykea-style: one tap starts it, then it keeps pushing the
         * provider's position on its own — watchPosition() keeps the freshest
         * fix in memory, a fixed interval actually posts it to the server, so
         * a flood of GPS events doesn't turn into a flood of network requests.
         */
        startLiveTracking() {
            this.error = '';
            if (!navigator.geolocation) {
                this.error = 'Geolocation is not supported by your browser.';
                return;
            }
            this.sharing = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.lastKnownPos = pos;
                    this.sharing = false;
                    this.liveTracking = true;
                    this.pushPosition();

                    this.watchId = navigator.geolocation.watchPosition(
                        (p) => { this.lastKnownPos = p; },
                        (err) => { this.error = err.message || 'Lost access to your location.'; },
                        { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 }
                    );
                    this.pushIntervalId = setInterval(() => this.pushPosition(), 8000);
                },
                (err) => {
                    this.error = err.message || 'Could not get your location.';
                    this.sharing = false;
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        stopLiveTracking() {
            if (this.watchId !== null) {
                navigator.geolocation.clearWatch(this.watchId);
                this.watchId = null;
            }
            if (this.pushIntervalId !== null) {
                clearInterval(this.pushIntervalId);
                this.pushIntervalId = null;
            }
            this.liveTracking = false;
        },

        async pushPosition() {
            if (!this.lastKnownPos) return;
            try {
                const res = await fetch(this.trackUrl, {
                    method: 'POST',
                    headers: this.reqHeaders(),
                    body: JSON.stringify({
                        latitude: this.lastKnownPos.coords.latitude,
                        longitude: this.lastKnownPos.coords.longitude,
                        note: this.note,
                    }),
                });
                if (res.ok) {
                    this.applyTracking(await res.json());
                } else if (res.status === 422) {
                    // Booking is no longer active — stop trying.
                    this.stopLiveTracking();
                }
            } catch (err) {
                // Transient network hiccup — the next interval tick will retry, tracking stays on.
            }
        },
    }));
});
</script>
@endsection
