package com.medrep.fleet.ui.dashboard

import android.Manifest
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.content.ContextCompat
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import com.medrep.fleet.MainActivity
import com.medrep.fleet.databinding.FragmentDashboardBinding

class DashboardFragment : Fragment() {

    private var _binding: FragmentDashboardBinding? = null
    private val binding get() = _binding!!
    private val vm: DashboardViewModel by viewModels()

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
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        vm.dashboard.observe(viewLifecycleOwner) { data ->
            binding.tvTodayVisits.text  = data.todayVisits.toString()
            binding.tvMonthVisits.text  = data.monthVisits.toString()
            binding.tvTodayKm.text      = "%.1f km".format(data.todayDistanceKm)
            binding.tvPendingExpenses.text = data.pendingExpenses.toString()
            binding.tvClockedStatus.text   = if (data.clockedIn) "Clocked In" else "Clocked Out"

            if (data.clockedIn) {
                binding.btnClockIn.visibility  = View.GONE
                binding.btnClockOut.visibility = View.VISIBLE
                binding.tvClockTime.text = "Since: ${data.clockInTime ?: ""}"
            } else {
                binding.btnClockIn.visibility  = View.VISIBLE
                binding.btnClockOut.visibility = View.GONE
                binding.tvClockTime.text = ""
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

        binding.btnClockIn.setOnClickListener  { checkPermissionsThenClockIn() }
        binding.btnClockOut.setOnClickListener { clockOut() }
        binding.btnViewRoute.setOnClickListener {
            // TODO: navigate to route history
        }

        vm.load(requireContext())
    }

    private fun checkPermissionsThenClockIn() {
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

    private fun performClockIn() {
        vm.clockIn(requireContext())
        (requireActivity() as MainActivity).startLocationService()
    }

    private fun clockOut() {
        vm.clockOut(requireContext())
        (requireActivity() as MainActivity).stopLocationService()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
