package com.medrep.fleet.ui.expenses

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
import com.medrep.fleet.databinding.BottomSheetAddExpenseBinding
import com.medrep.fleet.databinding.FragmentExpensesBinding

class ExpensesFragment : Fragment() {

    private var _binding: FragmentExpensesBinding? = null
    private val binding get() = _binding!!
    private val vm: ExpensesViewModel by viewModels()
    private lateinit var adapter: ExpensesAdapter

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentExpensesBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = ExpensesAdapter()
        binding.rvExpenses.layoutManager = LinearLayoutManager(requireContext())
        binding.rvExpenses.adapter = adapter

        vm.expenses.observe(viewLifecycleOwner) { list ->
            adapter.submitList(list)
            binding.tvEmpty.visibility = if (list.isEmpty()) View.VISIBLE else View.GONE
        }

        vm.loading.observe(viewLifecycleOwner) { loading ->
            binding.progressBar.visibility = if (loading) View.VISIBLE else View.GONE
            binding.swipeRefresh.isRefreshing = loading
        }

        binding.swipeRefresh.setOnRefreshListener { vm.load(requireContext()) }
        binding.fabAdd.setOnClickListener { showAddExpenseSheet() }

        vm.load(requireContext())
    }

    private fun showAddExpenseSheet() {
        val sheet = BottomSheetDialog(requireContext())
        val sb = BottomSheetAddExpenseBinding.inflate(layoutInflater)
        sheet.setContentView(sb.root)

        val categories = resources.getStringArray(R.array.expense_categories)
        sb.spinnerCategory.adapter = ArrayAdapter(
            requireContext(), android.R.layout.simple_spinner_dropdown_item, categories
        )

        sb.btnSubmit.setOnClickListener {
            val category = sb.spinnerCategory.selectedItem.toString()
            val amount   = sb.etAmount.text.toString().toDoubleOrNull() ?: return@setOnClickListener
            val desc     = sb.etDescription.text.toString().trim()
            val date     = sb.etDate.text.toString().trim()
            vm.addExpense(requireContext(), category, amount, desc, date)
            sheet.dismiss()
        }

        sheet.show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
