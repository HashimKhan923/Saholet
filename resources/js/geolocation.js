document.addEventListener('alpine:init', () => {
    Alpine.data('addressGeolocation', () => ({
        locating: false,
        error: '',

        detect() {
            this.error = '';

            if (!navigator.geolocation) {
                this.error = 'Geolocation is not supported by your browser.';
                return;
            }

            this.locating = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => this.resolve(pos),
                (err) => {
                    this.error = err.message || 'Could not get your location. You can still type your address.';
                    this.locating = false;
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        async resolve(pos) {
            const { latitude, longitude } = pos.coords;

            if (this.$refs.lat) this.$refs.lat.value = latitude;
            if (this.$refs.lng) this.$refs.lng.value = longitude;

            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`,
                    { headers: { Accept: 'application/json' } }
                );

                if (res.ok) {
                    const data = await res.json();

                    if (data.display_name && this.$refs.address) {
                        this.$refs.address.value = data.display_name;
                    }

                    const city = data.address?.city || data.address?.town || data.address?.county;
                    const cityEl = document.getElementById('city');
                    if (city && cityEl && !cityEl.value) {
                        cityEl.value = city;
                    }
                } else {
                    this.error = 'Got your location, but could not look up the address automatically. Please type it in.';
                }
            } catch (e) {
                this.error = 'Got your location, but could not look up the address automatically. Please type it in.';
            } finally {
                this.locating = false;
            }
        },
    }));
});
