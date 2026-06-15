package com.medrep.fleet.ui.expenses

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.bottomsheet.BottomSheetDialog
import com.google.android.material.datepicker.CalendarConstraints
import com.google.android.material.datepicker.DateValidatorPointBackward
import com.google.android.material.datepicker.MaterialDatePicker
import com.medrep.fleet.R
import com.medrep.fleet.databinding.BottomSheetAddExpenseBinding
import com.medrep.fleet.databinding.FragmentExpensesBinding
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import java.util.TimeZone

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

        vm.error.observe(viewLifecycleOwner) { msg ->
            if (msg != null) Toast.makeText(requireContext(), msg, Toast.LENGTH_LONG).show()
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

        var selectedDate = ""

        val openDatePicker = {
            val constraints = CalendarConstraints.Builder()
                .setValidator(DateValidatorPointBackward.now())
                .build()
            val picker = MaterialDatePicker.Builder.datePicker()
                .setTitleText("Select Expense Date")
                .setCalendarConstraints(constraints)
                .build()
            picker.addOnPositiveButtonClickListener { ms ->
                val apiFmt = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).apply {
                    timeZone = TimeZone.getTimeZone("UTC")
                }
                val displayFmt = SimpleDateFormat("d MMM yyyy", Locale.getDefault()).apply {
                    timeZone = TimeZone.getTimeZone("UTC")
                }
                selectedDate = apiFmt.format(Date(ms))
                sb.etDate.setText(displayFmt.format(Date(ms)))
            }
            picker.show(childFragmentManager, "expense_date")
        }

        sb.etDate.setOnClickListener { openDatePicker() }
        sb.tilDate.setEndIconOnClickListener { openDatePicker() }

        sb.btnSubmit.setOnClickListener {
            val category = sb.spinnerCategory.selectedItem.toString()
            val amount   = sb.etAmount.text.toString().toDoubleOrNull()
            val desc     = sb.etDescription.text.toString().trim()

            if (amount == null || amount <= 0) {
                Toast.makeText(requireContext(), "Enter a valid amount", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            if (selectedDate.isEmpty()) {
                Toast.makeText(requireContext(), "Please select a date", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            vm.addExpense(requireContext(), category, amount, desc, selectedDate)
            sheet.dismiss()
        }

        sheet.show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
