function loadGoogleMaps(key, onReady) {
    if (window.google && window.google.maps) {
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
    script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(key) + '&loading=async&libraries=places&callback=__initGoogleMaps';
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
}

document.addEventListener('alpine:init', () => {
    Alpine.data('addressMapPicker', (cfg = {}) => ({
        mapsKey: cfg.key || '',
        lat: cfg.lat || null,
        lng: cfg.lng || null,
        map: null,
        marker: null,
        locating: false,
        mapError: '',

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
                streetViewControl: false,
                mapTypeControl: false,
                fullscreenControl: false,
            });

            this.marker = new google.maps.Marker({
                position: start,
                map: this.map,
                draggable: true,
            });

            this.marker.addListener('dragend', () => this.applyPosition(this.marker.getPosition()));
            this.map.addListener('click', (e) => {
                this.marker.setPosition(e.latLng);
                this.applyPosition(e.latLng);
            });

            if (google.maps.places && this.$refs.search) {
                const autocomplete = new google.maps.places.Autocomplete(this.$refs.search, {
                    fields: ['geometry', 'formatted_address'],
                    componentRestrictions: { country: 'pk' },
                });
                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (!place.geometry || !place.geometry.location) return;
                    const loc = place.geometry.location;
                    this.map.setCenter(loc);
                    this.map.setZoom(16);
                    this.marker.setPosition(loc);
                    this.setLatLng(loc.lat(), loc.lng());
                    if (place.formatted_address) this.setAddressField(place.formatted_address);
                    this.reverseGeocode(loc, false);
                });
            }
        },

        applyPosition(latLng) {
            this.setLatLng(latLng.lat(), latLng.lng());
            this.reverseGeocode(latLng, true);
        },

        reverseGeocode(latLng, updateAddress) {
            if (!window.google || !google.maps.Geocoder) return;
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: latLng }, (results, status) => {
                if (status !== 'OK' || !results || !results[0]) return;
                if (updateAddress) this.setAddressField(results[0].formatted_address);
                const cityTypes = ['locality', 'postal_town', 'administrative_area_level_2', 'sublocality', 'administrative_area_level_1'];
                let comp = null;
                for (const type of cityTypes) {
                    comp = results[0].address_components.find((c) => c.types.includes(type));
                    if (comp) break;
                }
                if (comp) this.setCityField(comp.long_name);
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
                (pos) => {
                    const loc = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
                    this.map.setCenter(loc);
                    this.map.setZoom(16);
                    this.marker.setPosition(loc);
                    this.applyPosition(loc);
                    this.locating = false;
                },
                () => { this.locating = false; },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },
    }));
});
