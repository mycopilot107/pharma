package com.medrep.fleet.ui.route

import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.Path
import android.graphics.Typeface
import android.graphics.drawable.BitmapDrawable
import android.location.Location
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.View
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import com.medrep.fleet.data.model.TrackingPoint
import com.medrep.fleet.databinding.ActivityRouteHistoryBinding
import org.osmdroid.config.Configuration
import org.osmdroid.tileprovider.tilesource.TileSourceFactory
import org.osmdroid.util.BoundingBox
import org.osmdroid.util.GeoPoint
import org.osmdroid.views.overlay.Marker
import org.osmdroid.views.overlay.Polyline
import java.time.Instant
import java.time.ZoneId
import java.time.ZonedDateTime
import java.time.format.DateTimeFormatter
import java.time.LocalDate

class RouteHistoryActivity : AppCompatActivity() {

    private lateinit var binding: ActivityRouteHistoryBinding
    private val vm: RouteHistoryViewModel by viewModels()

    private val liveHandler = Handler(Looper.getMainLooper())
    private val liveRunnable = object : Runnable {
        override fun run() {
            vm.loadLive(this@RouteHistoryActivity)
            liveHandler.postDelayed(this, LIVE_POLL_MS)
        }
    }

    private var firstDraw = true
    private var lastPointCount = 0

    private val startIcon: BitmapDrawable by lazy { buildStartIcon() }
    private val currentIcon: BitmapDrawable by lazy { buildCurrentIcon() }

    companion object {
        private const val LIVE_POLL_MS    = 5_000L
        private const val STOP_RADIUS_M   = 50f    // within 50 m = stationary
        private const val MIN_STOP_MIN    = 10L    // must be stopped >= 10 min
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        Configuration.getInstance().userAgentValue = packageName
        binding = ActivityRouteHistoryBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.title = "Today's Route"

        binding.mapView.setTileSource(TileSourceFactory.MAPNIK)
        binding.mapView.setMultiTouchControls(true)
        binding.mapView.controller.setZoom(15.0)

        // Keep the "no route" card always hidden — map speaks for itself
        binding.tvNoRoute.visibility = View.GONE

        val today = LocalDate.now().format(DateTimeFormatter.ofPattern("yyyy-MM-dd"))
        vm.load(this, today)

        vm.points.observe(this) { points ->
            if (points.isEmpty()) return@observe
            if (points.size == lastPointCount) return@observe
            lastPointCount = points.size

            binding.cardLegend.visibility = View.VISIBLE
            drawRoute(points)
        }

        vm.loading.observe(this) {
            binding.progressBar.visibility = if (it) View.VISIBLE else View.GONE
        }
    }

    override fun onResume() {
        super.onResume()
        binding.mapView.onResume()
        liveHandler.post(liveRunnable)
    }

    override fun onPause() {
        super.onPause()
        binding.mapView.onPause()
        liveHandler.removeCallbacks(liveRunnable)
    }

    // ── Route drawing ─────────────────────────────────────────────────────────

    private fun drawRoute(points: List<TrackingPoint>) {
        val geoPoints = points.map { GeoPoint(it.latitude, it.longitude) }
        binding.mapView.overlays.clear()

        // Route polyline
        val polyline = Polyline().apply {
            setPoints(geoPoints)
            outlinePaint.color = Color.parseColor("#1976D2")
            outlinePaint.strokeWidth = 7f
            outlinePaint.strokeCap = Paint.Cap.ROUND
            outlinePaint.strokeJoin = Paint.Join.ROUND
        }
        binding.mapView.overlays.add(polyline)

        // Start marker — show clock-in time in tooltip
        val startSnippet = tsToHHMM(points.first().createdAt)
            ?.let { "Started at $it" } ?: "First GPS point of the day"

        val startMarker = Marker(binding.mapView).apply {
            position = geoPoints.first()
            icon = startIcon
            title = "Start"
            snippet = startSnippet
            setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM)
        }

        // Current location marker
        val lastTs = tsToHHMM(points.last().createdAt)
        val currentMarker = Marker(binding.mapView).apply {
            position = geoPoints.last()
            icon = currentIcon
            title = "Current Location"
            snippet = if (lastTs != null) "Last ping at $lastTs" else "Most recent GPS ping"
            setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_CENTER)
        }

        binding.mapView.overlays.add(startMarker)
        binding.mapView.overlays.add(currentMarker)

        // Stoppage markers
        detectStoppages(points).forEach { stop ->
            val durationLabel = if (stop.durationMin >= 60)
                "${stop.durationMin / 60}h ${stop.durationMin % 60}m"
            else
                "${stop.durationMin} min"
            val icon = buildStoppageIcon(stop.durationMin)
            Marker(binding.mapView).apply {
                position = stop.location
                this.icon = icon
                title = "Stopped $durationLabel"
                snippet = "${stop.date}  ${stop.startTime} – ${stop.endTime}"
                setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM)
            }.also { binding.mapView.overlays.add(it) }
        }

        // Camera
        if (firstDraw) {
            firstDraw = false
            if (geoPoints.size > 1) {
                val box = BoundingBox.fromGeoPoints(geoPoints)
                binding.mapView.zoomToBoundingBox(box.increaseByScale(1.3f), true)
            } else {
                binding.mapView.controller.setCenter(geoPoints.first())
                binding.mapView.controller.setZoom(16.0)
            }
        } else {
            binding.mapView.controller.animateTo(geoPoints.last())
        }

        val distKm = calcDistanceKm(geoPoints)
        binding.tvRouteStats.text = if (distKm > 0)
            "${geoPoints.size} pts · %.1f km".format(distKm)
        else
            "${geoPoints.size} pts"

        binding.mapView.invalidate()
    }

    // ── Stoppage detection ────────────────────────────────────────────────────

    private data class Stoppage(
        val location: GeoPoint,
        val date: String,          // e.g. "13 Jun 2026"
        val startTime: String,     // HH:MM
        val endTime: String,       // HH:MM
        val durationMin: Long,
    )

    private fun detectStoppages(points: List<TrackingPoint>): List<Stoppage> {
        if (points.size < 2) return emptyList()
        val stoppages = mutableListOf<Stoppage>()
        val distResult = FloatArray(1)
        var i = 0

        while (i < points.size) {
            val anchor = points[i]
            var j = i + 1

            // Advance while each subsequent point is within STOP_RADIUS_M of anchor
            while (j < points.size) {
                Location.distanceBetween(
                    anchor.latitude, anchor.longitude,
                    points[j].latitude, points[j].longitude,
                    distResult,
                )
                if (distResult[0] > STOP_RADIUS_M) break
                j++
            }

            if (j > i + 1) {
                val startMs = tsToMs(anchor.createdAt)
                val endMs   = tsToMs(points[j - 1].createdAt)
                if (startMs != null && endMs != null) {
                    val durationMin = (endMs - startMs) / 60_000L
                    if (durationMin >= MIN_STOP_MIN) {
                        stoppages.add(
                            Stoppage(
                                location    = GeoPoint(anchor.latitude, anchor.longitude),
                                date        = msToDate(startMs),
                                startTime   = msToHHMM(startMs),
                                endTime     = msToHHMM(endMs),
                                durationMin = durationMin,
                            )
                        )
                    }
                }
            }
            i = j
        }
        return stoppages
    }

    // ── Timestamp helpers ─────────────────────────────────────────────────────

    private fun tsToMs(ts: String?): Long? {
        if (ts.isNullOrBlank()) return null
        return try { Instant.parse(ts).toEpochMilli() } catch (_: Exception) { null }
    }

    private fun tsToHHMM(ts: String?): String? {
        val ms = tsToMs(ts) ?: return null
        return msToHHMM(ms)
    }

    private fun msToHHMM(ms: Long): String {
        val zdt = ZonedDateTime.ofInstant(Instant.ofEpochMilli(ms), ZoneId.systemDefault())
        return "%02d:%02d".format(zdt.hour, zdt.minute)
    }

    private fun msToDate(ms: Long): String {
        val zdt = ZonedDateTime.ofInstant(Instant.ofEpochMilli(ms), ZoneId.systemDefault())
        val month = zdt.month.getDisplayName(
            java.time.format.TextStyle.SHORT, java.util.Locale.getDefault()
        )
        return "${zdt.dayOfMonth} $month ${zdt.year}"
    }

    // ── Marker icon builders ──────────────────────────────────────────────────

    private fun buildStartIcon(): BitmapDrawable {
        val dp     = resources.displayMetrics.density
        val radius = 22 * dp
        val tailH  = 14 * dp
        val w = (radius * 2 + 6 * dp).toInt()
        val h = (radius * 2 + tailH + 6 * dp).toInt()
        val cx = w / 2f
        val cy = radius + 3 * dp

        val bmp    = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
        val canvas = Canvas(bmp)
        val paint  = Paint(Paint.ANTI_ALIAS_FLAG)

        paint.color = Color.WHITE; paint.style = Paint.Style.FILL
        canvas.drawCircle(cx, cy, radius + 3 * dp, paint)

        paint.color = Color.parseColor("#2E7D32")
        canvas.drawCircle(cx, cy, radius, paint)

        paint.color = Color.WHITE; paint.style = Paint.Style.STROKE; paint.strokeWidth = 2 * dp
        canvas.drawCircle(cx, cy, radius - 5 * dp, paint)

        paint.style = Paint.Style.FILL; paint.color = Color.WHITE
        paint.textSize = radius * 1.1f; paint.textAlign = Paint.Align.CENTER
        paint.typeface = Typeface.DEFAULT_BOLD
        canvas.drawText("S", cx, cy - (paint.descent() + paint.ascent()) / 2f, paint)

        paint.color = Color.parseColor("#2E7D32")
        canvas.drawPath(Path().apply {
            moveTo(cx - 5 * dp, cy + radius - 2 * dp)
            lineTo(cx + 5 * dp, cy + radius - 2 * dp)
            lineTo(cx, cy + radius + tailH)
            close()
        }, paint)

        return BitmapDrawable(resources, bmp)
    }

    private fun buildCurrentIcon(): BitmapDrawable {
        val dp   = resources.displayMetrics.density
        val size = (56 * dp).toInt()
        val cx   = size / 2f; val cy = size / 2f

        val bmp    = Bitmap.createBitmap(size, size, Bitmap.Config.ARGB_8888)
        val canvas = Canvas(bmp)
        val paint  = Paint(Paint.ANTI_ALIAS_FLAG)

        paint.color = Color.argb(50, 21, 101, 192); paint.style = Paint.Style.FILL
        canvas.drawCircle(cx, cy, size / 2f, paint)
        paint.color = Color.argb(100, 21, 101, 192)
        canvas.drawCircle(cx, cy, size / 2f - 8 * dp, paint)
        paint.color = Color.WHITE
        canvas.drawCircle(cx, cy, size / 2f - 14 * dp, paint)
        paint.color = Color.parseColor("#1565C0")
        canvas.drawCircle(cx, cy, size / 2f - 20 * dp, paint)

        return BitmapDrawable(resources, bmp)
    }

    /** Amber teardrop pin labelled with the stop duration (e.g. "15m"). */
    private fun buildStoppageIcon(durationMin: Long): BitmapDrawable {
        val dp     = resources.displayMetrics.density
        val radius = 18 * dp
        val tailH  = 12 * dp
        val w = (radius * 2 + 6 * dp).toInt()
        val h = (radius * 2 + tailH + 6 * dp).toInt()
        val cx = w / 2f
        val cy = radius + 3 * dp

        val bmp    = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
        val canvas = Canvas(bmp)
        val paint  = Paint(Paint.ANTI_ALIAS_FLAG)

        paint.color = Color.WHITE; paint.style = Paint.Style.FILL
        canvas.drawCircle(cx, cy, radius + 3 * dp, paint)

        paint.color = Color.parseColor("#E65100")  // deep orange
        canvas.drawCircle(cx, cy, radius, paint)

        paint.color = Color.WHITE
        val label = if (durationMin >= 60) "${durationMin / 60}h" else "${durationMin}m"
        paint.textSize = radius * 0.85f; paint.textAlign = Paint.Align.CENTER
        paint.typeface = Typeface.DEFAULT_BOLD
        canvas.drawText(label, cx, cy - (paint.descent() + paint.ascent()) / 2f, paint)

        paint.color = Color.parseColor("#E65100")
        canvas.drawPath(Path().apply {
            moveTo(cx - 4 * dp, cy + radius - 2 * dp)
            lineTo(cx + 4 * dp, cy + radius - 2 * dp)
            lineTo(cx, cy + radius + tailH)
            close()
        }, paint)

        return BitmapDrawable(resources, bmp)
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private fun calcDistanceKm(points: List<GeoPoint>): Double {
        if (points.size < 2) return 0.0
        var totalMeters = 0.0
        val result = FloatArray(1)
        for (i in 1 until points.size) {
            Location.distanceBetween(
                points[i - 1].latitude, points[i - 1].longitude,
                points[i].latitude,     points[i].longitude,
                result,
            )
            totalMeters += result[0]
        }
        return totalMeters / 1000.0
    }

    override fun onSupportNavigateUp(): Boolean {
        onBackPressedDispatcher.onBackPressed()
        return true
    }
}
