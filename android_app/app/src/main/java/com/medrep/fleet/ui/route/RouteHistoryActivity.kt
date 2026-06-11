package com.medrep.fleet.ui.route

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

    companion object {
        private const val LIVE_POLL_MS = 3_000L
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        Configuration.getInstance().userAgentValue = packageName
        binding = ActivityRouteHistoryBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.title = "Live Route"

        binding.mapView.setTileSource(TileSourceFactory.MAPNIK)
        binding.mapView.setMultiTouchControls(true)
        binding.mapView.controller.setZoom(15.0)

        // Initial history load
        val today = LocalDate.now().format(DateTimeFormatter.ofPattern("yyyy-MM-dd"))
        vm.load(this, today)

        vm.points.observe(this) { points ->
            if (points.isEmpty()) {
                binding.tvNoRoute.visibility = View.VISIBLE
                return@observe
            }
            binding.tvNoRoute.visibility = View.GONE
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

    private fun drawRoute(geoPoints: List<GeoPoint>) {
        val polyline = Polyline().apply {
            setPoints(geoPoints)
            outlinePaint.color = android.graphics.Color.parseColor("#1976D2")
            outlinePaint.strokeWidth = 6f
        }
        binding.mapView.overlays.clear()
        binding.mapView.overlays.add(polyline)

        if (geoPoints.isNotEmpty()) {
            val start = Marker(binding.mapView).apply {
                position = geoPoints.first()
                title = "Start"
                setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM)
            }
            val end = Marker(binding.mapView).apply {
                position = geoPoints.last()
                title = "Now"
                setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM)
            }
            binding.mapView.overlays.add(start)
            binding.mapView.overlays.add(end)

            // Only auto-zoom on first draw; let user pan freely after
            if (binding.mapView.overlayManager.size <= 3) {
                val box = BoundingBox.fromGeoPoints(geoPoints)
                binding.mapView.zoomToBoundingBox(box.increaseByScale(1.2f), true)
            } else {
                // Just move camera to current position
                binding.mapView.controller.animateTo(geoPoints.last())
            }
        }

        binding.mapView.invalidate()
    }

    override fun onSupportNavigateUp(): Boolean {
        onBackPressedDispatcher.onBackPressed()
        return true
    }
}
