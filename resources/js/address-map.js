function loadGoogleMaps(key, onReady) {
    if (window.google && window.google.maps && window.google.maps.marker) {
        onReady();
        return;
    }

    document.addEventListener('google-maps-ready', onReady, { once: true });

    if (document.getElementById('google-maps-script')) return;

    window.__initGoogleMaps = function () {
        document.dispatchEvent(new Event('google-maps-ready'));
    };

    const script = document.createElement('script');
    script.id = 'google-maps-script';
    // `marker` is required for AdvancedMarkerElement (google.maps.Marker is
    // deprecated); `places` is required for PlaceAutocompleteElement (the
    // legacy Autocomplete widget is deprecated too).
    script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(key) + '&loading=async&libraries=places,marker&callback=__initGoogleMaps';
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
}

/** Mirrors GeofenceService::pointInPolygon() in PHP — same ray-casting test. */
function pointInPolygon(lat, lng, boundary) {
    const vertices = (boundary || []).filter((v) => v && typeof v.lat === 'number' && typeof v.lng === 'number');
    const count = vertices.length;
    if (count < 3) return false;

    let inside = false;
    for (let i = 0, j = count - 1; i < count; j = i++) {
        const latI = vertices[i].lat;
        const lngI = vertices[i].lng;
        const latJ = vertices[j].lat;
        const lngJ = vertices[j].lng;

        const intersects = (lngI > lng) !== (lngJ > lng)
            && lat < (latJ - latI) * (lng - lngI) / (lngJ - lngI) + latI;

        if (intersects) inside = !inside;
    }

    return inside;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('addressMapPicker', (cfg = {}) => {
        // Kept outside Alpine's reactive data for the same reason as the
        // service-area drawing tool — these are purely informational overlays
        // that are only ever added, never removed, so it's not strictly
        // required here, but consistent with the rest of this file's pattern.
        let serviceAreaPolygons = [];

        return {
            mapsKey: cfg.key || '',
            lat: cfg.lat || null,
            lng: cfg.lng || null,
            areas: Array.isArray(cfg.areas) ? cfg.areas : [],
            map: null,
            marker: null,
            locating: false,
            mapError: '',
            outsideServiceArea: false,
            outsideAreaLabel: '',

            init() {
                if (!this.mapsKey) {
                    this.mapError = 'Google Maps is not configured.';
                    return;
                }
                loadGoogleMaps(this.mapsKey, () => this.setup());
            },

            setup() {
                const start = (this.lat && this.lng)
                    ? { lat: parseFloat(this.lat), lng: parseFloat(this.lng) }
                    : { lat: 24.8607, lng: 67.0011 };

                this.map = new google.maps.Map(this.$refs.map, {
                    center: start,
                    zoom: (this.lat && this.lng) ? 16 : 12,
                    mapId: 'DEMO_MAP_ID',
                    streetViewControl: false,
                    mapTypeControl: false,
                    fullscreenControl: false,
                });

                this.areas.forEach((area) => {
                    serviceAreaPolygons.push(new google.maps.Polygon({
                        paths: area.boundary,
                        fillColor: '#1a7a35',
                        fillOpacity: 0.08,
                        strokeColor: '#1a7a35',
                        strokeOpacity: 0.6,
                        strokeWeight: 1.5,
                        clickable: false,
                        map: this.map,
                    }));
                });

                this.marker = new google.maps.marker.AdvancedMarkerElement({
                    position: start,
                    map: this.map,
                    gmpDraggable: true,
                });

                this.marker.addListener('dragend', () => this.applyPosition(this.markerPosition()));
                this.map.addListener('click', (e) => {
                    const pos = { lat: e.latLng.lat(), lng: e.latLng.lng() };
                    this.marker.position = pos;
                    this.applyPosition(pos);
                });

                if (google.maps.places && this.$refs.search) {
                    const placeAutocomplete = new google.maps.places.PlaceAutocompleteElement({
                        includedRegionCodes: ['pk'],
                    });
                    this.$refs.search.appendChild(placeAutocomplete);

                    placeAutocomplete.addEventListener('gmp-select', async ({ placePrediction }) => {
                        const place = placePrediction.toPlace();
                        await place.fetchFields({ fields: ['location', 'formattedAddress'] });
                        if (!place.location) return;
                        const pos = { lat: place.location.lat(), lng: place.location.lng() };
                        this.map.setCenter(pos);
                        this.map.setZoom(16);
                        this.marker.position = pos;
                        this.setLatLng(pos.lat, pos.lng);
                        if (place.formattedAddress) this.setAddressField(place.formattedAddress);
                        this.reverseGeocode(pos, false);
                    });
                }

                // Check whatever position we started with too (e.g. editing an
                // already-saved address that's outside a boundary drawn later).
                if (this.lat && this.lng) {
                    this.checkServiceArea(parseFloat(this.lat), parseFloat(this.lng));
                }
            },

            /** AdvancedMarkerElement.position can read back as a plain literal or a LatLng-like object depending on how it was last set — normalize either shape. */
            markerPosition() {
                const pos = this.marker.position;
                return typeof pos.lat === 'function' ? { lat: pos.lat(), lng: pos.lng() } : { lat: pos.lat, lng: pos.lng };
            },

            applyPosition(pos) {
                this.setLatLng(pos.lat, pos.lng);
                this.reverseGeocode(pos, true);
            },

            /** No areas configured (or geo-fencing is off) → this.areas is simply empty, so nothing is ever blocked here. */
            checkServiceArea(lat, lng, fallbackLabel) {
                if (this.areas.length === 0) {
                    this.outsideServiceArea = false;
                    this.setSubmitDisabled(false);
                    return;
                }

                const inside = this.areas.some((area) => pointInPolygon(lat, lng, area.boundary));
                this.outsideServiceArea = !inside;
                this.outsideAreaLabel = inside ? '' : (fallbackLabel || 'this area');
                this.setSubmitDisabled(!inside);
            },

            setSubmitDisabled(disabled) {
                const btn = this.$el.closest('form')?.querySelector('button[type="submit"]');
                if (btn) btn.disabled = disabled;
            },

            reverseGeocode(pos, updateAddress) {
                if (!window.google || !google.maps.Geocoder) return;
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: pos }, (results, status) => {
                    if (status !== 'OK' || !results || !results[0]) {
                        this.checkServiceArea(pos.lat, pos.lng);
                        return;
                    }
                    if (updateAddress) this.setAddressField(results[0].formatted_address);
                    const cityTypes = ['locality', 'postal_town', 'administrative_area_level_2', 'sublocality', 'administrative_area_level_1'];
                    let comp = null;
                    for (const type of cityTypes) {
                        comp = results[0].address_components.find((c) => c.types.includes(type));
                        if (comp) break;
                    }
                    if (comp) this.setCityField(comp.long_name);
                    this.checkServiceArea(pos.lat, pos.lng, results[0].formatted_address);
                });
            },

            fieldIn(name) {
                return this.$el.closest('form')?.querySelector('[name="' + name + '"]') || null;
            },
            setAddressField(value) {
                const el = this.fieldIn('address');
                if (el) el.value = value;
            },
            setCityField(value) {
                const el = this.fieldIn('city');
                if (el) el.value = value;
            },
            setLatLng(lat, lng) {
                this.lat = lat;
                this.lng = lng;
                const latEl = this.fieldIn('latitude');
                const lngEl = this.fieldIn('longitude');
                if (latEl) latEl.value = lat;
                if (lngEl) lngEl.value = lng;
            },

            locate() {
                if (!navigator.geolocation || !this.map) return;
                this.locating = true;
                navigator.geolocation.getCurrentPosition(
                    (posResult) => {
                        const pos = { lat: posResult.coords.latitude, lng: posResult.coords.longitude };
                        this.map.setCenter(pos);
                        this.map.setZoom(16);
                        this.marker.position = pos;
                        this.applyPosition(pos);
                        this.locating = false;
                    },
                    () => { this.locating = false; },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            },
        };
    });
});
