package com.medrep.fleet.ui.leaves

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.bottomsheet.BottomSheetDialog
import com.medrep.fleet.R
import com.medrep.fleet.databinding.BottomSheetApplyLeaveBinding
import com.medrep.fleet.databinding.FragmentLeavesBinding

class LeavesFragment : Fragment() {

    private var _binding: FragmentLeavesBinding? = null
    private val binding get() = _binding!!
    private val vm: LeavesViewModel by viewModels()
    private lateinit var adapter: LeavesAdapter

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentLeavesBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = LeavesAdapter()
        binding.rvLeaves.layoutManager = LinearLayoutManager(requireContext())
        binding.rvLeaves.adapter = adapter

        vm.leaves.observe(viewLifecycleOwner) { list ->
            adapter.submitList(list)
            binding.tvEmpty.visibility = if (list.isEmpty()) View.VISIBLE else View.GONE
        }

        vm.loading.observe(viewLifecycleOwner) {
            binding.progressBar.visibility = if (it) View.VISIBLE else View.GONE
        }

        binding.fabApply.setOnClickListener { showApplyLeaveSheet() }
        binding.swipeRefresh.setOnRefreshListener { vm.load(requireContext()) }

        vm.load(requireContext())
    }

    private fun showApplyLeaveSheet() {
        val sheet = BottomSheetDialog(requireContext())
        val sb = BottomSheetApplyLeaveBinding.inflate(layoutInflater)
        sheet.setContentView(sb.root)

        val types = resources.getStringArray(R.array.leave_types)
        sb.spinnerType.adapter = ArrayAdapter(
            requireContext(), android.R.layout.simple_spinner_dropdown_item, types
        )

        sb.btnApply.setOnClickListener {
            val type      = sb.spinnerType.selectedItem.toString()
            val startDate = sb.etStartDate.text.toString().trim()
            val endDate   = sb.etEndDate.text.toString().trim()
            val reason    = sb.etReason.text.toString().trim()
            if (startDate.isEmpty() || endDate.isEmpty()) return@setOnClickListener
            vm.applyLeave(requireContext(), type, startDate, endDate, reason)
            sheet.dismiss()
        }

        sheet.show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
