package com.medrep.fleet.service

import android.Manifest
import android.app.*
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.content.pm.PackageManager
import android.content.pm.ServiceInfo
import android.location.Location
import android.location.LocationManager
import android.os.*
import android.util.Log
import androidx.core.app.ActivityCompat
import androidx.core.app.NotificationCompat
import androidx.core.content.ContextCompat
import androidx.core.location.LocationManagerCompat
import com.google.android.gms.location.*
import com.medrep.fleet.MainActivity
import com.medrep.fleet.R
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.api.ApiService
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.*

class LocationTrackingService : Service() {

    companion object {
        const val ACTION_START = "com.medrep.fleet.START_TRACKING"
        const val ACTION_STOP  = "com.medrep.fleet.STOP_TRACKING"

        const val NOTIF_CHANNEL_ID = "medrep_tracking"
        const val NOTIF_ID = 1001

        // GPS updates + server pings every 10 s — live enough, battery-friendly
        private const val LOCATION_INTERVAL_MS    = 10_000L
        private const val MIN_PING_GAP_MS         = 10_000L

        // Impossible speed: 300 km/h ≈ 83 m/s
        private const val MAX_PLAUSIBLE_SPEED_MPS = 83f

        var isMockLocationDetected = false
            private set

        const val BROADCAST_MOCK_DETECTED = "com.medrep.fleet.MOCK_DETECTED"
    }

    private lateinit var fusedClient: FusedLocationProviderClient
    private lateinit var locationCallback: LocationCallback
    private val serviceScope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    private var lastPingTime = 0L
    private var lastLocation: Location? = null

    // Created ONCE per tracking session — never recreated per ping
    private var api: ApiService? = null

    private val locationStateReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context?, intent: Intent?) {
            if (intent?.action != LocationManager.PROVIDERS_CHANGED_ACTION) return
            val lm = getSystemService(LocationManager::class.java)
            if (LocationManagerCompat.isLocationEnabled(lm)) {
                updateNotification("Live GPS tracking active", "MR Visits Track is recording your route")
            } else {
                updateNotification("Location is OFF", "Turn on Location to resume GPS tracking")
            }
        }
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    override fun onCreate() {
        super.onCreate()
        fusedClient = LocationServices.getFusedLocationProviderClient(this)
        createNotificationChannel()
        ContextCompat.registerReceiver(
            this,
            locationStateReceiver,
            IntentFilter(LocationManager.PROVIDERS_CHANGED_ACTION),
            ContextCompat.RECEIVER_EXPORTED
        )
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        when (intent?.action) {
            ACTION_STOP -> {
                stopTracking()
                return START_NOT_STICKY
            }
            else -> startTracking()
        }
        return START_STICKY
    }

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onDestroy() {
        unregisterReceiver(locationStateReceiver)
        stopTracking()
        serviceScope.cancel()
        super.onDestroy()
    }

    // ── Start / Stop ──────────────────────────────────────────────────────────

    private fun startTracking() {
        val token = TokenPrefs.getToken(this)
        if (token == null) {
            Log.w("MedRepGPS", "No auth token — cannot start tracking")
            stopSelf()
            return
        }

        // Build the reusable API client once for this tracking session
        api = ApiClient.create(token)

        val notification = buildNotification()
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            startForeground(NOTIF_ID, notification, ServiceInfo.FOREGROUND_SERVICE_TYPE_LOCATION)
        } else {
            startForeground(NOTIF_ID, notification)
        }

        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
            != PackageManager.PERMISSION_GRANTED) {
            Log.w("MedRepGPS", "Location permission missing — stopping service")
            stopSelf()
            return
        }

        val request = LocationRequest.Builder(Priority.PRIORITY_HIGH_ACCURACY, LOCATION_INTERVAL_MS)
            .setMinUpdateIntervalMillis(LOCATION_INTERVAL_MS)
            .setMinUpdateDistanceMeters(0f)
            .setWaitForAccurateLocation(false)
            .build()

        locationCallback = object : LocationCallback() {
            override fun onLocationResult(result: LocationResult) {
                result.lastLocation?.let { onLocation(it) }
            }
        }

        fusedClient.requestLocationUpdates(request, locationCallback, Looper.getMainLooper())
        Log.i("MedRepGPS", "Tracking started — pinging every ${LOCATION_INTERVAL_MS / 1000}s")
    }

    private fun stopTracking() {
        if (::locationCallback.isInitialized) {
            fusedClient.removeLocationUpdates(locationCallback)
        }
        api = null
        stopForeground(STOP_FOREGROUND_REMOVE)
        stopSelf()
    }

    // ── Location processing ───────────────────────────────────────────────────

    private fun onLocation(location: Location) {
        // Mock location detection
        @Suppress("DEPRECATION")
        val isMocked = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            location.isMock
        } else {
            location.isFromMockProvider
        }
        if (isMocked && !isMockLocationDetected) {
            isMockLocationDetected = true
            sendBroadcast(Intent(BROADCAST_MOCK_DETECTED))
            Log.w("MedRepGPS", "Mock location detected")
        }

        // Impossible speed detection
        val prev = lastLocation
        if (prev != null) {
            val timeDeltaSec = (location.time - prev.time) / 1000f
            if (timeDeltaSec > 0) {
                val speedMps = prev.distanceTo(location) / timeDeltaSec
                if (speedMps > MAX_PLAUSIBLE_SPEED_MPS && !isMockLocationDetected) {
                    isMockLocationDetected = true
                    sendBroadcast(Intent(BROADCAST_MOCK_DETECTED))
                    Log.w("MedRepGPS", "Impossible speed detected: ${speedMps.toInt()} m/s")
                }
            }
        }
        lastLocation = location

        // Rate-limit server pings
        val now = System.currentTimeMillis()
        if (now - lastPingTime < MIN_PING_GAP_MS) return
        lastPingTime = now

        serviceScope.launch { sendPing(location, isMocked) }
    }

    private suspend fun sendPing(location: Location, isMocked: Boolean) {
        val svc = api ?: run {
            Log.w("MedRepGPS", "API service not initialised — skipping ping")
            return
        }
        try {
            val response = svc.trackingPing(
                mapOf(
                    "latitude"      to location.latitude,
                    "longitude"     to location.longitude,
                    "accuracy"      to location.accuracy.toDouble(),
                    "speed"         to if (location.hasSpeed()) location.speed.toDouble() else null,
                    "heading"       to if (location.hasBearing()) location.bearing.toDouble() else null,
                    "altitude"      to location.altitude,
                    "is_background" to true,
                )
            )
            if (response.isSuccessful) {
                Log.d("MedRepGPS", "Ping OK — lat=${location.latitude}, lng=${location.longitude}")
            } else {
                val body = response.errorBody()?.string() ?: ""
                Log.w("MedRepGPS", "Ping HTTP ${response.code()}: $body")

                // Token expired → clear api so we don't keep hammering with bad credentials
                if (response.code() == 401) {
                    Log.w("MedRepGPS", "Auth token rejected — stopping tracking")
                    api = null
                }
            }
        } catch (e: Exception) {
            Log.e("MedRepGPS", "Ping network error: ${e.message}")
        }
    }

    // ── Notification ──────────────────────────────────────────────────────────

    private fun createNotificationChannel() {
        val channel = NotificationChannel(
            NOTIF_CHANNEL_ID,
            "Live GPS Tracking",
            NotificationManager.IMPORTANCE_LOW
        ).apply {
            description = "MR Visits Track is recording your route"
            setShowBadge(false)
        }
        getSystemService(NotificationManager::class.java).createNotificationChannel(channel)
    }

    private fun buildNotification(
        title: String = "Live GPS tracking active",
        text: String = "MR Visits Track is recording your route",
    ): Notification {
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
            .setContentTitle(title)
            .setContentText(text)
            .setSmallIcon(R.drawable.ic_gps)
            .setOngoing(true)
            .setContentIntent(tapIntent)
            .addAction(R.drawable.ic_stop, "Stop", stopIntent)
            .build()
    }

    private fun updateNotification(title: String, text: String) {
        getSystemService(NotificationManager::class.java).notify(NOTIF_ID, buildNotification(title, text))
    }
}
