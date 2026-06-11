package com.medrep.fleet.ui.visits

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.medrep.fleet.databinding.FragmentVisitsBinding

class VisitsFragment : Fragment() {

    private var _binding: FragmentVisitsBinding? = null
    private val binding get() = _binding!!
    private val vm: VisitsViewModel by viewModels()
    private lateinit var adapter: VisitsAdapter

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentVisitsBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = VisitsAdapter { visit ->
            val intent = Intent(requireContext(), VisitDetailActivity::class.java)
            intent.putExtra(VisitDetailActivity.EXTRA_VISIT_ID, visit.id)
            startActivity(intent)
        }

        binding.rvVisits.layoutManager = LinearLayoutManager(requireContext())
        binding.rvVisits.adapter = adapter

        vm.visits.observe(viewLifecycleOwner) { visits ->
            adapter.submitList(visits)
            binding.tvEmpty.visibility = if (visits.isEmpty()) View.VISIBLE else View.GONE
        }

        vm.loading.observe(viewLifecycleOwner) { loading ->
            binding.progressBar.visibility = if (loading) View.VISIBLE else View.GONE
            binding.swipeRefresh.isRefreshing = loading
        }

        binding.swipeRefresh.setOnRefreshListener { vm.load(requireContext()) }
        binding.fabCheckIn.setOnClickListener {
            val intent = Intent(requireContext(), VisitDetailActivity::class.java)
            intent.putExtra(VisitDetailActivity.EXTRA_NEW_VISIT, true)
            startActivity(intent)
        }

        vm.load(requireContext())
    }

    override fun onResume() {
        super.onResume()
        vm.load(requireContext())
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
