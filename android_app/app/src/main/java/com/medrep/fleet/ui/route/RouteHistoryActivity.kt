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
import com.medrep.fleet.databinding.ActivityRouteHistoryBinding
import org.osmdroid.config.Configuration
import org.osmdroid.tileprovider.tilesource.TileSourceFactory
import org.osmdroid.util.BoundingBox
import org.osmdroid.util.GeoPoint
import org.osmdroid.views.overlay.Marker
import org.osmdroid.views.overlay.Polyline
import java.time.LocalDate
import java.time.format.DateTimeFormatter

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

    // Track state to avoid unnecessary redraws / bad zoom jumps
    private var firstDraw = true
    private var lastPointCount = 0

    // Cached marker icons — built once per activity instance
    private val startIcon: BitmapDrawable by lazy { buildStartIcon() }
    private val currentIcon: BitmapDrawable by lazy { buildCurrentIcon() }

    companion object {
        private const val LIVE_POLL_MS = 5_000L   // poll every 5 s
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

        val today = LocalDate.now().format(DateTimeFormatter.ofPattern("yyyy-MM-dd"))
        vm.load(this, today)

        vm.points.observe(this) { points ->
            if (points.isEmpty()) {
                binding.tvNoRoute.visibility = View.VISIBLE
                binding.cardLegend.visibility = View.GONE
                return@observe
            }
            // Skip redraw if point count hasn't changed (avoids flicker on every poll)
            if (points.size == lastPointCount) return@observe
            lastPointCount = points.size

            binding.tvNoRoute.visibility = View.GONE
            binding.cardLegend.visibility = View.VISIBLE
            drawRoute(points.map { GeoPoint(it.latitude, it.longitude) })
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

    private fun drawRoute(geoPoints: List<GeoPoint>) {
        binding.mapView.overlays.clear()

        // Route polyline
        val polyline = Polyline().apply {
            setPoints(geoPoints)
            outlinePaint.color = Color.parseColor("#1976D2")
            outlinePaint.strokeWidth = 7f
            outlinePaint.strokeCap = android.graphics.Paint.Cap.ROUND
            outlinePaint.strokeJoin = android.graphics.Paint.Join.ROUND
        }
        binding.mapView.overlays.add(polyline)

        // Start marker (green, "S")
        val startMarker = Marker(binding.mapView).apply {
            position = geoPoints.first()
            icon = startIcon
            title = "Start"
            snippet = "First GPS point of the day"
            setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM)
        }

        // Current location marker (blue concentric rings)
        val currentMarker = Marker(binding.mapView).apply {
            position = geoPoints.last()
            icon = currentIcon
            title = "Current Location"
            snippet = "Most recent GPS ping"
            setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_CENTER)
        }

        binding.mapView.overlays.add(startMarker)
        binding.mapView.overlays.add(currentMarker)

        // Camera control
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
            // On live updates just follow the current position
            binding.mapView.controller.animateTo(geoPoints.last())
        }

        // Update legend stats
        val distKm = calcDistanceKm(geoPoints)
        binding.tvRouteStats.text = if (distKm > 0)
            "${geoPoints.size} pts · %.1f km".format(distKm)
        else
            "${geoPoints.size} pts"

        binding.mapView.invalidate()
    }

    // ── Marker icon builders ──────────────────────────────────────────────────

    /**
     * Green filled circle with white "S" letter + a small teardrop tail.
     * Represents the first GPS ping (start of shift).
     */
    private fun buildStartIcon(): BitmapDrawable {
        val dp = resources.displayMetrics.density
        val radius = (22 * dp)
        val tailH  = (14 * dp)
        val w = (radius * 2 + 6 * dp).toInt()
        val h = (radius * 2 + tailH + 6 * dp).toInt()
        val cx = w / 2f
        val cy = radius + 3 * dp

        val bmp = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
        val canvas = Canvas(bmp)
        val paint = Paint(Paint.ANTI_ALIAS_FLAG)

        // White shadow/border
        paint.color = Color.WHITE
        paint.style = Paint.Style.FILL
        canvas.drawCircle(cx, cy, radius + 3 * dp, paint)

        // Green fill
        paint.color = Color.parseColor("#2E7D32")
        canvas.drawCircle(cx, cy, radius, paint)

        // White inner ring
        paint.color = Color.WHITE
        paint.style = Paint.Style.STROKE
        paint.strokeWidth = 2 * dp
        canvas.drawCircle(cx, cy, radius - 5 * dp, paint)

        // "S" text
        paint.style = Paint.Style.FILL
        paint.color = Color.WHITE
        paint.textSize = radius * 1.1f
        paint.textAlign = Paint.Align.CENTER
        paint.typeface = Typeface.DEFAULT_BOLD
        val textY = cy - (paint.descent() + paint.ascent()) / 2f
        canvas.drawText("S", cx, textY, paint)

        // Teardrop tail
        paint.color = Color.parseColor("#2E7D32")
        val tail = Path().apply {
            moveTo(cx - 5 * dp, cy + radius - 2 * dp)
            lineTo(cx + 5 * dp, cy + radius - 2 * dp)
            lineTo(cx, cy + radius + tailH)
            close()
        }
        canvas.drawPath(tail, paint)

        return BitmapDrawable(resources, bmp)
    }

    /**
     * Blue "you are here" concentric circles.
     * Represents the most recent GPS ping (current position).
     */
    private fun buildCurrentIcon(): BitmapDrawable {
        val dp = resources.displayMetrics.density
        val size = (56 * dp).toInt()
        val cx = size / 2f
        val cy = size / 2f

        val bmp = Bitmap.createBitmap(size, size, Bitmap.Config.ARGB_8888)
        val canvas = Canvas(bmp)
        val paint = Paint(Paint.ANTI_ALIAS_FLAG)

        // Outer translucent blue ring (pulse effect)
        paint.color = Color.argb(50, 21, 101, 192)
        paint.style = Paint.Style.FILL
        canvas.drawCircle(cx, cy, size / 2f, paint)

        // Mid semi-transparent ring
        paint.color = Color.argb(100, 21, 101, 192)
        canvas.drawCircle(cx, cy, size / 2f - 8 * dp, paint)

        // White ring
        paint.color = Color.WHITE
        canvas.drawCircle(cx, cy, size / 2f - 14 * dp, paint)

        // Blue solid inner dot
        paint.color = Color.parseColor("#1565C0")
        canvas.drawCircle(cx, cy, size / 2f - 20 * dp, paint)

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
                result
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
