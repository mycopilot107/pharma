package com.medrep.fleet.ui.dashboard

import android.Manifest
import android.annotation.SuppressLint
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.content.pm.PackageManager
import android.location.LocationManager
import android.os.Build
import android.os.Bundle
import android.provider.Settings
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.content.ContextCompat
import androidx.core.location.LocationManagerCompat
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import com.google.android.gms.location.FusedLocationProviderClient
import com.google.android.gms.location.LocationServices
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.medrep.fleet.MainActivity
import com.medrep.fleet.R
import com.medrep.fleet.databinding.FragmentDashboardBinding
import com.medrep.fleet.ui.route.RouteHistoryActivity
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class DashboardFragment : Fragment() {

    private var _binding: FragmentDashboardBinding? = null
    private val binding get() = _binding!!
    private val vm: DashboardViewModel by viewModels()
    private lateinit var fusedClient: FusedLocationProviderClient

    private val locationStateReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context?, intent: Intent?) {
            if (intent?.action != LocationManager.PROVIDERS_CHANGED_ACTION) return
            val lm = requireContext().getSystemService(LocationManager::class.java)
            if (!LocationManagerCompat.isLocationEnabled(lm)) {
                showLocationOffAlert()
            }
        }
    }

    private val locationPermRequest = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { grants ->
        val fine = grants[Manifest.permission.ACCESS_FINE_LOCATION] == true
        val bg   = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q)
            grants[Manifest.permission.ACCESS_BACKGROUND_LOCATION] == true else true
        if (fine && bg) performClockIn()
    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentDashboardBinding.inflate(inflater, container, false)
        fusedClient = LocationServices.getFusedLocationProviderClient(requireActivity())
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.tvDate.text = SimpleDateFormat("EEE, d MMM yyyy", Locale.getDefault()).format(Date())

        vm.dashboard.observe(viewLifecycleOwner) { data ->
            binding.tvTodayVisits.text     = data.todayVisits.toString()
            binding.tvMonthVisits.text     = data.monthVisits.toString()
            binding.tvTodayKm.text         = "%.1f km".format(data.todayDistanceKm)
            binding.tvPendingExpenses.text = data.pendingExpenses.toString()

            // tvClockedStatus is always hidden — state conveyed visually
            binding.tvClockedStatus.visibility = View.GONE

            if (data.clockedIn) {
                binding.viewStatusDot.visibility = View.VISIBLE
                binding.tvClockTime.text         = data.clockInTime ?: ""
                binding.tvClockTime.visibility   = View.VISIBLE
                binding.btnClockIn.visibility    = View.GONE
                binding.btnClockOut.visibility   = View.VISIBLE
            } else {
                binding.viewStatusDot.visibility = View.GONE
                binding.tvClockTime.visibility   = View.GONE
                binding.btnClockIn.visibility    = View.VISIBLE
                binding.btnClockOut.visibility   = View.GONE
            }
        }

        vm.loading.observe(viewLifecycleOwner) { loading ->
            binding.progressBar.visibility = if (loading) View.VISIBLE else View.GONE
        }

        vm.error.observe(viewLifecycleOwner) { msg ->
            if (msg != null) {
                binding.tvError.text = msg
                binding.tvError.visibility = View.VISIBLE
            } else {
                binding.tvError.visibility = View.GONE
            }
        }

        vm.clockInResult.observe(viewLifecycleOwner) { result ->
            when (result) {
                true  -> { (requireActivity() as MainActivity).startLocationService(); vm.clearClockInResult() }
                false -> { (requireActivity() as MainActivity).stopLocationService();  vm.clearClockInResult() }
                null  -> {}
            }
        }

        binding.btnClockIn.setOnClickListener  { checkPermissionsThenClockIn() }
        binding.btnClockOut.setOnClickListener { doClockOut() }
        binding.btnViewRoute.setOnClickListener {
            startActivity(android.content.Intent(requireContext(), RouteHistoryActivity::class.java))
        }

        vm.load(requireContext())
    }

    private fun checkPermissionsThenClockIn() {
        val lm = requireContext().getSystemService(LocationManager::class.java)
        if (!LocationManagerCompat.isLocationEnabled(lm)) {
            MaterialAlertDialogBuilder(requireContext())
                .setTitle("Location is off")
                .setMessage("Please enable Location on your device before clocking in.")
                .setPositiveButton("Open Settings") { _, _ ->
                    startActivity(Intent(Settings.ACTION_LOCATION_SOURCE_SETTINGS))
                }
                .setNegativeButton("Cancel", null)
                .show()
            return
        }

        val needed = mutableListOf<String>()
        if (ContextCompat.checkSelfPermission(requireContext(), Manifest.permission.ACCESS_FINE_LOCATION)
            != PackageManager.PERMISSION_GRANTED) {
            needed += Manifest.permission.ACCESS_FINE_LOCATION
            needed += Manifest.permission.ACCESS_COARSE_LOCATION
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q &&
            ContextCompat.checkSelfPermission(requireContext(), Manifest.permission.ACCESS_BACKGROUND_LOCATION)
            != PackageManager.PERMISSION_GRANTED) {
            needed += Manifest.permission.ACCESS_BACKGROUND_LOCATION
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU &&
            ContextCompat.checkSelfPermission(requireContext(), Manifest.permission.POST_NOTIFICATIONS)
            != PackageManager.PERMISSION_GRANTED) {
            needed += Manifest.permission.POST_NOTIFICATIONS
        }
        if (needed.isEmpty()) performClockIn() else locationPermRequest.launch(needed.toTypedArray())
    }

    @SuppressLint("MissingPermission")
    private fun performClockIn() {
        fusedClient.lastLocation
            .addOnSuccessListener { loc ->
                vm.clockIn(requireContext(), loc?.latitude ?: 0.0, loc?.longitude ?: 0.0)
            }
            .addOnFailureListener {
                vm.clockIn(requireContext(), 0.0, 0.0)
            }
    }

    @SuppressLint("MissingPermission")
    private fun doClockOut() {
        fusedClient.lastLocation
            .addOnSuccessListener { loc ->
                vm.clockOut(requireContext(), loc?.latitude ?: 0.0, loc?.longitude ?: 0.0)
            }
            .addOnFailureListener {
                vm.clockOut(requireContext(), 0.0, 0.0)
            }
    }

    override fun onResume() {
        super.onResume()
        ContextCompat.registerReceiver(
            requireContext(),
            locationStateReceiver,
            IntentFilter(LocationManager.PROVIDERS_CHANGED_ACTION),
            ContextCompat.RECEIVER_EXPORTED
        )
    }

    override fun onPause() {
        super.onPause()
        requireContext().unregisterReceiver(locationStateReceiver)
    }

    private fun showLocationOffAlert() {
        if (!isAdded || isDetached) return
        MaterialAlertDialogBuilder(requireContext())
            .setTitle("Location turned off")
            .setMessage("GPS tracking has paused. Please turn Location back on to continue recording your route.")
            .setPositiveButton("Open Settings") { _, _ ->
                startActivity(Intent(Settings.ACTION_LOCATION_SOURCE_SETTINGS))
            }
            .setNegativeButton("Dismiss", null)
            .show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
