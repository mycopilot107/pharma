package com.medrep.fleet.ui.tourplan

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.medrep.fleet.databinding.FragmentTourPlanBinding
import java.time.LocalDate
import java.time.format.DateTimeFormatter

class TourPlanFragment : Fragment() {

    private var _binding: FragmentTourPlanBinding? = null
    private val binding get() = _binding!!
    private val vm: TourPlanViewModel by viewModels()
    private lateinit var adapter: TourPlanAdapter

    private var currentWeekStart: LocalDate = getMonday(LocalDate.now())

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentTourPlanBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = TourPlanAdapter()
        binding.rvPlan.layoutManager = LinearLayoutManager(requireContext())
        binding.rvPlan.adapter = adapter

        vm.entries.observe(viewLifecycleOwner) { entries ->
            adapter.submitList(entries)
        }

        vm.loading.observe(viewLifecycleOwner) {
            binding.progressBar.visibility = if (it) View.VISIBLE else View.GONE
        }

        binding.btnPrevWeek.setOnClickListener {
            currentWeekStart = currentWeekStart.minusWeeks(1)
            updateWeekHeader()
            loadWeek()
        }

        binding.btnNextWeek.setOnClickListener {
            currentWeekStart = currentWeekStart.plusWeeks(1)
            updateWeekHeader()
            loadWeek()
        }

        updateWeekHeader()
        loadWeek()
    }

    private fun loadWeek() {
        val fmt = DateTimeFormatter.ofPattern("yyyy-MM-dd")
        vm.load(requireContext(), currentWeekStart.format(fmt))
    }

    private fun updateWeekHeader() {
        val fmt = DateTimeFormatter.ofPattern("d MMM")
        val end = currentWeekStart.plusDays(5)
        binding.tvWeekRange.text = "${currentWeekStart.format(fmt)} – ${end.format(fmt)}"
    }

    private fun getMonday(date: LocalDate): LocalDate {
        return date.minusDays((date.dayOfWeek.value - 1).toLong())
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
