package com.medrep.fleet

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.os.Build
import android.os.Bundle
import android.view.View
import androidx.appcompat.app.AppCompatActivity
import androidx.navigation.fragment.NavHostFragment
import androidx.navigation.ui.setupWithNavController
import com.medrep.fleet.databinding.ActivityMainBinding
import com.medrep.fleet.data.prefs.TokenPrefs
import com.medrep.fleet.service.LocationTrackingService

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding

    private val mockReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context?, intent: Intent?) {
            if (intent?.action == LocationTrackingService.BROADCAST_MOCK_DETECTED) {
                binding.bannerMockGps.visibility = View.VISIBLE
            }
        }
    }

    private val sessionExpiredReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context?, intent: Intent?) {
            if (intent?.action == com.medrep.fleet.data.api.ApiClient.ACTION_SESSION_EXPIRED) {
                stopLocationService()
                startActivity(
                    Intent(this@MainActivity, LoginActivity::class.java)
                        .addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK)
                )
            }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Provide context to ApiClient so its 401 interceptor can broadcast
        com.medrep.fleet.data.api.ApiClient.appContext = applicationContext

        val navHost = supportFragmentManager
            .findFragmentById(R.id.nav_host_fragment) as NavHostFragment
        val navController = navHost.navController
        binding.bottomNav.setupWithNavController(navController)

        binding.bannerMockGps.visibility = View.GONE

        val mockFilter = IntentFilter(LocationTrackingService.BROADCAST_MOCK_DETECTED)
        val expiredFilter = IntentFilter(com.medrep.fleet.data.api.ApiClient.ACTION_SESSION_EXPIRED)

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            registerReceiver(mockReceiver, mockFilter, RECEIVER_NOT_EXPORTED)
            registerReceiver(sessionExpiredReceiver, expiredFilter, RECEIVER_NOT_EXPORTED)
        } else {
            @Suppress("UnspecifiedRegisterReceiverFlag")
            registerReceiver(mockReceiver, mockFilter)
            @Suppress("UnspecifiedRegisterReceiverFlag")
            registerReceiver(sessionExpiredReceiver, expiredFilter)
        }
    }

    override fun onDestroy() {
        unregisterReceiver(mockReceiver)
        unregisterReceiver(sessionExpiredReceiver)
        super.onDestroy()
    }

    fun startLocationService() {
        val intent = Intent(this, LocationTrackingService::class.java).apply {
            action = LocationTrackingService.ACTION_START
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            startForegroundService(intent)
        } else {
            startService(intent)
        }
    }

    fun stopLocationService() {
        val intent = Intent(this, LocationTrackingService::class.java).apply {
            action = LocationTrackingService.ACTION_STOP
        }
        startService(intent)
    }
}
