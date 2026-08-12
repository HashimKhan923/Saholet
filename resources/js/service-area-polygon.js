function loadGoogleMaps(key, onReady) {
    if (window.google && window.google.maps && window.google.maps.marker) {
        onReady();
        return;
    }

    document.addEventListener('google-maps-ready', onReady, { once: true });

    if (document.getElementById('google-maps-script')) return;

    // Same script id/callback as address-map.js's loader on purpose — if this
    // ever ends up on the same page as that one, whichever runs first wins
    // and the other just listens for the ready event instead of double-loading.
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

function buildVertexDot() {
    const dot = document.createElement('div');
    dot.style.width = '10px';
    dot.style.height = '10px';
    dot.style.borderRadius = '50%';
    dot.style.background = '#1a7a35';
    dot.style.border = '1.5px solid #ffffff';
    dot.style.boxShadow = '0 0 0 1px rgba(0,0,0,0.2)';
    return dot;
}

document.addEventListener('alpine:init', () => {
    // The `drawing` library (google.maps.drawing.DrawingManager) was removed
    // from the Maps JS API — this draws a polygon manually instead: click
    // "Draw boundary", click the map to place each vertex, click "Finish".
    Alpine.data('serviceAreaPolygonPicker', (cfg = {}) => {
        // Google Maps SDK instances (map/polygon/markers) are kept OUTSIDE the
        // object Alpine returns below — Alpine wraps everything in that object
        // in a reactive Proxy, and calling `.map = null` through that Proxy
        // silently no-ops instead of actually removing the overlay (the SDK
        // tracks its own overlays by the original object's identity, which the
        // Proxy isn't). Plain closure variables here sidestep that entirely.
        let map = null;
        let polygon = null;
        let vertexMarkers = [];

        function clearVertexMarkers() {
            vertexMarkers.forEach((m) => { m.map = null; });
            vertexMarkers = [];
        }

        function removePolygon() {
            if (polygon) {
                polygon.setMap(null);
                polygon = null;
            }
        }

        return {
            mapsKey: cfg.key || '',
            boundary: Array.isArray(cfg.boundary) && cfg.boundary.length >= 3 ? cfg.boundary : null,
            drawing: false,
            mapError: '',
            pointCount: 0,

            init() {
                this.pointCount = this.boundary ? this.boundary.length : 0;
                if (!this.mapsKey) {
                    this.mapError = 'Google Maps is not configured.';
                    return;
                }
                loadGoogleMaps(this.mapsKey, () => this.setup());
            },

            setup() {
                const center = this.boundary
                    ? this.boundary[0]
                    : { lat: 24.8607, lng: 67.0011 };

                map = new google.maps.Map(this.$refs.map, {
                    center,
                    zoom: this.boundary ? 15 : 12,
                    mapId: 'DEMO_MAP_ID',
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    streetViewControl: false,
                    mapTypeControl: true,
                    mapTypeControlOptions: {
                        style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                        position: google.maps.ControlPosition.TOP_RIGHT,
                        mapTypeIds: ['satellite', 'hybrid', 'roadmap'],
                    },
                    fullscreenControl: false,
                });

                if (google.maps.places && this.$refs.search) {
                    const placeAutocomplete = new google.maps.places.PlaceAutocompleteElement({
                        includedRegionCodes: ['pk'],
                    });
                    this.$refs.search.appendChild(placeAutocomplete);

                    placeAutocomplete.addEventListener('gmp-select', async ({ placePrediction }) => {
                        const place = placePrediction.toPlace();
                        await place.fetchFields({ fields: ['location'] });
                        if (!place.location) return;
                        map.setCenter(place.location);
                        map.setZoom(15);
                    });
                }

                map.addListener('click', (e) => {
                    if (!this.drawing) return;
                    this.addVertex(e.latLng);
                });

                if (this.boundary) {
                    this.renderPolygon(this.boundary, true);
                }
            },

            startDrawing() {
                this.clearPolygon();
                this.drawing = true;
            },

            addVertex(latLng) {
                if (!polygon) {
                    polygon = new google.maps.Polygon({
                        paths: [latLng],
                        fillColor: '#1a7a35',
                        fillOpacity: 0.2,
                        strokeColor: '#1a7a35',
                        strokeWeight: 2,
                        editable: false,
                        map,
                    });
                } else {
                    polygon.getPath().push(latLng);
                }

                const marker = new google.maps.marker.AdvancedMarkerElement({
                    position: latLng,
                    map,
                    content: buildVertexDot(),
                });
                vertexMarkers.push(marker);

                // Keep the hidden field in sync on every single point, not just
                // when "Finish" is clicked — otherwise clicking the form's Save
                // button mid-draw submits whatever the field held before this
                // drawing session (usually nothing), silently losing every point
                // placed so far.
                this.syncBoundary();
            },

            finishDrawing() {
                if (this.pointCount < 3) {
                    this.mapError = 'Add at least 3 points before finishing.';
                    return;
                }
                this.mapError = '';
                this.drawing = false;
                polygon.setOptions({ editable: true });
                clearVertexMarkers();
                this.watchPolygon();
                this.syncBoundary();
            },

            renderPolygon(points, editable) {
                polygon = new google.maps.Polygon({
                    paths: points,
                    fillColor: '#1a7a35',
                    fillOpacity: 0.2,
                    strokeColor: '#1a7a35',
                    strokeWeight: 2,
                    editable,
                    map,
                });

                const bounds = new google.maps.LatLngBounds();
                points.forEach((p) => bounds.extend(p));
                map.fitBounds(bounds);

                this.pointCount = points.length;
                this.watchPolygon();
            },

            watchPolygon() {
                const path = polygon.getPath();
                ['set_at', 'insert_at', 'remove_at'].forEach((evt) => {
                    google.maps.event.addListener(path, evt, () => this.syncBoundary());
                });
            },

            syncBoundary() {
                const path = polygon.getPath();
                const points = [];
                path.forEach((latLng) => points.push({ lat: latLng.lat(), lng: latLng.lng() }));
                this.boundary = points;
                this.pointCount = points.length;
                this.writeField(JSON.stringify(points));
            },

            clearPolygon() {
                this.drawing = false;
                this.mapError = '';
                clearVertexMarkers();
                removePolygon();
                this.boundary = null;
                this.pointCount = 0;
                this.writeField('');
            },

            writeField(value) {
                const el = this.$el.closest('form')?.querySelector('[name="boundary"]');
                if (el) el.value = value;
            },
        };
    });
});
