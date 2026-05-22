@if (auth()->check() && auth()->user()->isRepresentative() && auth()->user()->tracking_active)
<script>
(function() {
    const pingUrl = @json(route('mr.tracking.ping'));
    const intervalMs = {{ config('tracking.ping_interval_seconds') * 1000 }};
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function sendPing() {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                fetch(pingUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy: pos.coords.accuracy,
                    }),
                }).catch(() => {});
            },
            () => {},
            { enableHighAccuracy: true, maximumAge: 30000, timeout: 10000 }
        );
    }

    sendPing();
    setInterval(sendPing, intervalMs);
})();
</script>
@endif
