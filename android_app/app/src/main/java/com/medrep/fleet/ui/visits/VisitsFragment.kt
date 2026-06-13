package com.medrep.fleet.ui.visits

import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.text.Editable
import android.text.TextWatcher
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.datepicker.MaterialDatePicker
import com.medrep.fleet.databinding.FragmentVisitsBinding
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class VisitsFragment : Fragment() {

    private var _binding: FragmentVisitsBinding? = null
    private val binding get() = _binding!!
    private val vm: VisitsViewModel by viewModels()
    private lateinit var adapter: VisitsAdapter

    private val searchHandler = Handler(Looper.getMainLooper())
    private var searchRunnable: Runnable? = null

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentVisitsBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = VisitsAdapter { visit ->
            startActivity(
                Intent(requireContext(), VisitDetailActivity::class.java)
                    .putExtra(VisitDetailActivity.EXTRA_VISIT_ID, visit.id)
            )
        }

        binding.rvVisits.layoutManager = LinearLayoutManager(requireContext())
        binding.rvVisits.adapter = adapter

        // ── Pre-select today's date in the chip ───────────────────────────
        val todayDisplay = SimpleDateFormat("d MMM yyyy", Locale.getDefault()).format(Date())
        binding.tvDateLabel.text        = todayDisplay
        binding.btnClearDate.visibility = View.VISIBLE

        // ── Observe visits list ───────────────────────────────────────────
        vm.visits.observe(viewLifecycleOwner) { visits ->
            adapter.submitList(visits)
            binding.tvEmpty.visibility = if (visits.isEmpty()) View.VISIBLE else View.GONE
        }

        // ── Observe loading ───────────────────────────────────────────────
        vm.loading.observe(viewLifecycleOwner) { loading ->
            binding.progressBar.visibility  = if (loading) View.VISIBLE else View.GONE
            binding.swipeRefresh.isRefreshing = loading
        }

        // ── Observe pagination ────────────────────────────────────────────
        vm.currentPage.observe(viewLifecycleOwner) { _ -> updatePagination() }
        vm.totalPages.observe(viewLifecycleOwner)  { _ -> updatePagination() }
        vm.totalCount.observe(viewLifecycleOwner)  { count ->
            if (count > 0) {
                binding.tvTotalBadge.text       = "$count total"
                binding.tvTotalBadge.visibility = View.VISIBLE
            } else {
                binding.tvTotalBadge.visibility = View.GONE
            }
            updatePagination()
        }

        // ── Search with 400 ms debounce ───────────────────────────────────
        binding.etSearch.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
            override fun afterTextChanged(s: Editable?) {
                binding.btnClearSearch.visibility =
                    if (s.isNullOrEmpty()) View.GONE else View.VISIBLE

                searchRunnable?.let { searchHandler.removeCallbacks(it) }
                val query = s?.toString() ?: ""
                searchRunnable = Runnable { vm.search(requireContext(), query) }
                searchHandler.postDelayed(searchRunnable!!, 400L)
            }
        })

        binding.btnClearSearch.setOnClickListener {
            binding.etSearch.setText("")
            binding.btnClearSearch.visibility = View.GONE
        }

        // ── Date picker ───────────────────────────────────────────────────
        binding.chipDate.setOnClickListener {
            val picker = MaterialDatePicker.Builder.datePicker()
                .setTitleText("Filter by date")
                .build()
            picker.addOnPositiveButtonClickListener { ms ->
                val apiDate     = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).format(Date(ms))
                val displayDate = SimpleDateFormat("d MMM yyyy", Locale.getDefault()).format(Date(ms))
                binding.tvDateLabel.text        = displayDate
                binding.btnClearDate.visibility = View.VISIBLE
                vm.filterDate(requireContext(), apiDate)
            }
            picker.show(parentFragmentManager, "date_picker")
        }

        binding.btnClearDate.setOnClickListener {
            binding.tvDateLabel.text        = "Pick Date"
            binding.btnClearDate.visibility = View.GONE
            vm.filterDate(requireContext(), null)
        }

        // ── Pagination buttons ────────────────────────────────────────────
        binding.btnPrev.setOnClickListener {
            val page = vm.currentPage.value ?: 1
            if (page > 1) vm.goToPage(requireContext(), page - 1)
        }

        binding.btnNext.setOnClickListener {
            val page  = vm.currentPage.value ?: 1
            val total = vm.totalPages.value  ?: 1
            if (page < total) vm.goToPage(requireContext(), page + 1)
        }

        // ── Swipe to refresh ──────────────────────────────────────────────
        binding.swipeRefresh.setOnRefreshListener { vm.load(requireContext()) }

        // ── New visit FAB ─────────────────────────────────────────────────
        binding.fabCheckIn.setOnClickListener {
            startActivity(
                Intent(requireContext(), VisitDetailActivity::class.java)
                    .putExtra(VisitDetailActivity.EXTRA_NEW_VISIT, true)
            )
        }

        vm.load(requireContext())
    }

    private fun updatePagination() {
        val page  = vm.currentPage.value ?: 1
        val total = vm.totalPages.value  ?: 1
        val count = vm.totalCount.value  ?: 0

        if (total > 1) {
            binding.layoutPagination.visibility = View.VISIBLE
            binding.tvPageInfo.text = "Page $page of $total  ·  $count visits"
            binding.btnPrev.isEnabled = page > 1
            binding.btnNext.isEnabled = page < total
            binding.btnPrev.alpha = if (page > 1) 1f else 0.4f
            binding.btnNext.alpha = if (page < total) 1f else 0.4f
        } else {
            binding.layoutPagination.visibility = View.GONE
        }
    }

    override fun onResume() {
        super.onResume()
        vm.load(requireContext())
    }

    override fun onDestroyView() {
        searchRunnable?.let { searchHandler.removeCallbacks(it) }
        super.onDestroyView()
        _binding = null
    }
}
