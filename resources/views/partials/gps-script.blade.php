<script>
function getGpsPosition(callback, errorCallback) {
    if (!navigator.geolocation) {
        errorCallback('GPS is not supported on this device.');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (pos) => callback({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
        () => errorCallback('Unable to get GPS location. Please enable location access.'),
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}

function fillGpsFields(latId, lngId, statusId) {
    const statusEl = statusId ? document.getElementById(statusId) : null;
    if (statusEl) statusEl.textContent = 'Getting location...';
    getGpsPosition(
        ({ lat, lng }) => {
            document.getElementById(latId).value = lat;
            document.getElementById(lngId).value = lng;
            if (statusEl) statusEl.textContent = 'Location captured: ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
        },
        (msg) => { if (statusEl) statusEl.textContent = msg; else alert(msg); }
    );
}

function submitWithGps(formId, latId, lngId, statusId) {
    const form = document.getElementById(formId);
    const statusEl = statusId ? document.getElementById(statusId) : null;
    if (statusEl) statusEl.textContent = 'Getting location...';
    getGpsPosition(
        ({ lat, lng }) => {
            document.getElementById(latId).value = lat;
            document.getElementById(lngId).value = lng;
            form.submit();
        },
        (msg) => { if (statusEl) statusEl.textContent = msg; else alert(msg); }
    );
}
</script>
