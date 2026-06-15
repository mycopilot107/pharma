package com.medrep.fleet.ui.tourplan

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.medrep.fleet.databinding.FragmentTourPlanBinding

class TourPlanFragment : Fragment() {

    private var _binding: FragmentTourPlanBinding? = null
    private val binding get() = _binding!!
    private val vm: TourPlanViewModel by viewModels()
    private lateinit var adapter: TourPlanAdapter

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentTourPlanBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = TourPlanAdapter { plan ->
            val intent = Intent(requireContext(), TourPlanDetailActivity::class.java)
            intent.putExtra(TourPlanDetailActivity.EXTRA_PLAN_ID, plan.id)
            startActivity(intent)
        }

        binding.rvPlan.layoutManager = LinearLayoutManager(requireContext())
        binding.rvPlan.adapter = adapter

        vm.plans.observe(viewLifecycleOwner) { plans ->
            adapter.submitList(plans)
            binding.emptyState.visibility = if (plans.isEmpty()) View.VISIBLE else View.GONE
        }

        vm.loading.observe(viewLifecycleOwner) {
            binding.progressBar.visibility = if (it) View.VISIBLE else View.GONE
        }

        vm.error.observe(viewLifecycleOwner) { err ->
            if (err != null) Toast.makeText(requireContext(), err, Toast.LENGTH_SHORT).show()
        }

        binding.fab.setOnClickListener {
            startActivity(Intent(requireContext(), CreateTourPlanActivity::class.java))
        }

        load()
    }

    override fun onResume() {
        super.onResume()
        load()
    }

    private fun load() {
        vm.load(requireContext())
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
