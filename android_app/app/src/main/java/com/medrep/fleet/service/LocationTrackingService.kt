package com.medrep.fleet.service

import android.Manifest
import android.app.*
import android.content.Intent
import android.content.pm.PackageManager
import android.content.pm.ServiceInfo
import android.location.Location
import android.os.*
import android.util.Log
import androidx.core.app.ActivityCompat
import androidx.core.app.NotificationCompat
import com.google.android.gms.location.*
import com.medrep.fleet.MainActivity
import com.medrep.fleet.R
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.*

/**
 * Native Android Foreground Service using FusedLocationProviderClient.
 * Runs as a persistent foreground service so MIUI / ColorOS / OxygenOS
 * cannot kill it even when the app is in the background.
 *
 * Sends GPS pings to the server every PING_INTERVAL_MS milliseconds.
 */
class LocationTrackingService : Service() {

    companion object {
        const val ACTION_START = "com.medrep.fleet.START_TRACKING"
        const val ACTION_STOP  = "com.medrep.fleet.STOP_TRACKING"

        const val NOTIF_CHANNEL_ID = "medrep_tracking"
        const val NOTIF_ID = 1001

        private const val PING_INTERVAL_MS    = 1_000L    // ping server every 1 s
        private const val LOCATION_INTERVAL_MS = 1_000L   // request GPS every 1 s
        private const val LOCATION_FASTEST_MS  = 1_000L   // fastest GPS update
        private const val MIN_DISTANCE_M       = 0f        // send every update regardless of distance
        private const val MIN_PING_GAP_MS      = 1_000L   // rate-limit: never ping faster than 1 s

        // Impossible speed check: 300 km/h = ~83 m/s
        private const val MAX_PLAUSIBLE_SPEED_MPS = 83f

        var isMockLocationDetected = false
            private set

        // Broadcast action so UI can react to mock detection
        const val BROADCAST_MOCK_DETECTED = "com.medrep.fleet.MOCK_DETECTED"
    }

    private lateinit var fusedClient: FusedLocationProviderClient
    private lateinit var locationCallback: LocationCallback
    private val serviceScope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    private var lastPingTime = 0L
    private var lastLocation: Location? = null

    // ── Lifecycle ────────────────────────────────────────────────────────────

    override fun onCreate() {
        super.onCreate()
        fusedClient = LocationServices.getFusedLocationProviderClient(this)
        createNotificationChannel()
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        when (intent?.action) {
            ACTION_STOP -> {
                stopTracking()
                return START_NOT_STICKY
            }
            else -> startTracking()
        }
        return START_STICKY  // restart automatically if killed
    }

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onDestroy() {
        stopTracking()
        serviceScope.cancel()
        super.onDestroy()
    }

    // ── Start / Stop ─────────────────────────────────────────────────────────

    private fun startTracking() {
        val notification = buildNotification()
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            startForeground(NOTIF_ID, notification, ServiceInfo.FOREGROUND_SERVICE_TYPE_LOCATION)
        } else {
            startForeground(NOTIF_ID, notification)
        }

        val request = LocationRequest.Builder(Priority.PRIORITY_HIGH_ACCURACY, LOCATION_INTERVAL_MS)
            .setMinUpdateIntervalMillis(LOCATION_FASTEST_MS)
            .setMinUpdateDistanceMeters(MIN_DISTANCE_M)
            .setWaitForAccurateLocation(false)
            .build()

        locationCallback = object : LocationCallback() {
            override fun onLocationResult(result: LocationResult) {
                result.lastLocation?.let { onLocation(it) }
            }
        }

        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
            == PackageManager.PERMISSION_GRANTED) {
            fusedClient.requestLocationUpdates(request, locationCallback, Looper.getMainLooper())
        }
    }

    private fun stopTracking() {
        if (::locationCallback.isInitialized) {
            fusedClient.removeLocationUpdates(locationCallback)
        }
        stopForeground(STOP_FOREGROUND_REMOVE)
        stopSelf()
    }

    // ── Location processing ───────────────────────────────────────────────────

    private fun onLocation(location: Location) {
        // ── Mock location detection ───────────────────────────────────────
        @Suppress("DEPRECATION")
        val isMocked = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            location.isMock
        } else {
            location.isFromMockProvider
        }
        if (isMocked && !isMockLocationDetected) {
            isMockLocationDetected = true
            sendBroadcast(Intent(BROADCAST_MOCK_DETECTED))
            Log.w("MedRepGPS", "MOCK LOCATION DETECTED")
        }

        // ── Impossible speed detection ────────────────────────────────────
        val prev = lastLocation
        if (prev != null) {
            val timeDeltaSec = (location.time - prev.time) / 1000f
            if (timeDeltaSec > 0) {
                val distM = prev.distanceTo(location)
                val speedMps = distM / timeDeltaSec
                if (speedMps > MAX_PLAUSIBLE_SPEED_MPS && !isMockLocationDetected) {
                    isMockLocationDetected = true
                    sendBroadcast(Intent(BROADCAST_MOCK_DETECTED))
                    Log.w("MedRepGPS", "IMPOSSIBLE SPEED: ${speedMps} m/s")
                }
            }
        }
        lastLocation = location

        // ── Rate-limit pings ──────────────────────────────────────────────
        val now = System.currentTimeMillis()
        if (now - lastPingTime < MIN_PING_GAP_MS) return
        lastPingTime = now

        // ── Send ping to server ───────────────────────────────────────────
        serviceScope.launch {
            sendPing(location, isMocked)
        }
    }

    private suspend fun sendPing(location: Location, isMocked: Boolean) {
        try {
            val token = TokenPrefs.getToken(this) ?: return
            val api = ApiClient.create(token)
            api.trackingPing(
                mapOf(
                    "latitude"    to location.latitude,
                    "longitude"   to location.longitude,
                    "accuracy"    to location.accuracy.toDouble(),
                    "speed"       to if (location.hasSpeed()) location.speed.toDouble() else null,
                    "heading"     to if (location.hasBearing()) location.bearing.toDouble() else null,
                    "altitude"    to location.altitude,
                    "is_mock"     to isMocked,
                    "is_background" to true,
                )
            )
        } catch (e: Exception) {
            Log.e("MedRepGPS", "Ping failed: ${e.message}")
        }
    }

    // ── Notification ──────────────────────────────────────────────────────────

    private fun createNotificationChannel() {
        val channel = NotificationChannel(
            NOTIF_CHANNEL_ID,
            "Live GPS Tracking",
            NotificationManager.IMPORTANCE_LOW
        ).apply {
            description = "MedRep Fleet is recording your route"
            setShowBadge(false)
        }
        getSystemService(NotificationManager::class.java)
            .createNotificationChannel(channel)
    }

    private fun buildNotification(): Notification {
        val tapIntent = PendingIntent.getActivity(
            this, 0,
            Intent(this, MainActivity::class.java),
            PendingIntent.FLAG_IMMUTABLE
        )
        val stopIntent = PendingIntent.getService(
            this, 1,
            Intent(this, LocationTrackingService::class.java).apply { action = ACTION_STOP },
            PendingIntent.FLAG_IMMUTABLE
        )
        return NotificationCompat.Builder(this, NOTIF_CHANNEL_ID)
            .setContentTitle("Live GPS tracking active")
            .setContentText("MedRep Fleet is recording your route")
            .setSmallIcon(R.drawable.ic_gps)
            .setOngoing(true)
            .setContentIntent(tapIntent)
            .addAction(R.drawable.ic_stop, "Stop", stopIntent)
            .build()
    }
}
