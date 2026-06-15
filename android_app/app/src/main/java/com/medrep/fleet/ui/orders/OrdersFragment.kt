package com.medrep.fleet.ui.orders

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
import com.medrep.fleet.databinding.FragmentOrdersBinding

class OrdersFragment : Fragment() {

    private var _binding: FragmentOrdersBinding? = null
    private val binding get() = _binding!!
    private val vm: OrdersViewModel by viewModels()
    private lateinit var adapter: OrdersAdapter

    private val searchHandler = Handler(Looper.getMainLooper())
    private var searchRunnable: Runnable? = null

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentOrdersBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = OrdersAdapter()
        binding.rvOrders.layoutManager = LinearLayoutManager(requireContext())
        binding.rvOrders.adapter = adapter

        vm.orders.observe(viewLifecycleOwner) { list ->
            adapter.submitList(list)
            binding.tvEmpty.visibility = if (list.isEmpty()) View.VISIBLE else View.GONE
        }

        vm.loading.observe(viewLifecycleOwner) {
            binding.progressBar.visibility  = if (it) View.VISIBLE else View.GONE
            binding.swipeRefresh.isRefreshing = it
        }

        binding.etSearch.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
            override fun afterTextChanged(s: Editable?) {
                searchRunnable?.let { searchHandler.removeCallbacks(it) }
                val query = s?.toString()?.trim()
                searchRunnable = Runnable {
                    vm.load(requireContext(), search = query?.ifBlank { null })
                }
                searchHandler.postDelayed(searchRunnable!!, 400L)
            }
        })

        binding.swipeRefresh.setOnRefreshListener {
            vm.load(requireContext(), search = binding.etSearch.text?.toString()?.trim()?.ifBlank { null })
        }

        binding.fabNewOrder.setOnClickListener {
            startActivity(Intent(requireContext(), CreateOrderActivity::class.java))
        }

        vm.load(requireContext())
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
