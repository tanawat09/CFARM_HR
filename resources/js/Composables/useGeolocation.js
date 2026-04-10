import { ref, onUnmounted } from 'vue';

export function useGeolocation() {
    const coords = ref({ latitude: null, longitude: null, accuracy: null });
    const error = ref(null);
    const isSupported = 'navigator' in window && 'geolocation' in navigator;
    const watcher = ref(null);

    const getLocation = (options = { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }) => {
        return new Promise((resolve, reject) => {
            if (!isSupported) {
                error.value = 'Geolocation is not supported by your browser';
                reject(error.value);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    coords.value = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };
                    resolve(coords.value);
                },
                (err) => {
                    error.value = err.message;
                    reject(err.message);
                },
                options
            );
        });
    };

    return { coords, error, isSupported, getLocation };
}
