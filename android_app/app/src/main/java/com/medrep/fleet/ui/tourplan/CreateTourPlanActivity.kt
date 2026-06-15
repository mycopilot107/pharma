package com.medrep.fleet.ui.tourplan

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.google.android.material.chip.Chip
import com.google.android.material.chip.ChipGroup
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.Customer
import com.medrep.fleet.data.prefs.TokenPrefs
import com.medrep.fleet.databinding.ActivityCreateTourPlanBinding
import kotlinx.coroutines.launch
import java.time.LocalDate
import java.time.format.DateTimeFormatter

class CreateTourPlanActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCreateTourPlanBinding

    private var customers: List<Customer> = emptyList()
    private var weekStart: LocalDate = getNextMonday()

    // stops[dayOfWeek] -> list of (customerId, customerName)
    private val stops = mutableMapOf<Int, MutableList<Pair<Int, String>>>()

    private val dayNames = mapOf(
        1 to "Monday", 2 to "Tuesday", 3 to "Wednesday",
        4 to "Thursday", 5 to "Friday", 6 to "Saturday"
    )

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCreateTourPlanBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.title = "New Tour Plan"

        updateWeekHeader()
        loadCustomers()

        binding.btnPrevWeek.setOnClickListener {
            weekStart = weekStart.minusWeeks(1)
            updateWeekHeader()
        }
        binding.btnNextWeek.setOnClickListener {
            weekStart = weekStart.plusWeeks(1)
            updateWeekHeader()
        }

        binding.btnSave.setOnClickListener { savePlan(submit = false) }
        binding.btnSubmit.setOnClickListener { savePlan(submit = true) }
    }

    private fun updateWeekHeader() {
        val fmt = DateTimeFormatter.ofPattern("d MMM")
        binding.tvWeekRange.text =
            "${weekStart.format(fmt)} – ${weekStart.plusDays(5).format(fmt)}"
    }

    private fun loadCustomers() {
        val token = TokenPrefs.getToken(this) ?: return
        lifecycleScope.launch {
            try {
                val r = ApiClient.create(token).getCustomers(perPage = 200)
                if (r.isSuccessful) {
                    customers = r.body()?.data ?: emptyList()
                    buildDayForms()
                }
            } catch (_: Exception) {}
        }
    }

    private fun buildDayForms() {
        binding.daysContainer.removeAllViews()
        for (day in 1..6) {
            stops[day] = mutableListOf()
            val dayView = LayoutInflater.from(this)
                .inflate(com.medrep.fleet.R.layout.layout_day_stops, binding.daysContainer, false)

            dayView.findViewById<TextView>(com.medrep.fleet.R.id.tvDayLabel).text =
                "${dayNames[day]} · ${weekStart.plusDays((day - 1).toLong()).format(DateTimeFormatter.ofPattern("d MMM"))}"

            val chipGroup = dayView.findViewById<ChipGroup>(com.medrep.fleet.R.id.chipGroupStops)
            val btnAdd    = dayView.findViewById<Button>(com.medrep.fleet.R.id.btnAddStop)

            btnAdd.setOnClickListener { showCustomerPicker(day, chipGroup) }
            binding.daysContainer.addView(dayView)
        }
    }

    private fun showCustomerPicker(day: Int, chipGroup: ChipGroup) {
        val names  = customers.map { "${it.name} (${it.type ?: "customer"})" }.toTypedArray()
        val dialog = android.app.AlertDialog.Builder(this)
            .setTitle("Add stop for ${dayNames[day]}")
            .setItems(names) { _, idx ->
                val c = customers[idx]
                stops[day]?.add(Pair(c.id, c.name))
                addChip(chipGroup, c.name, day, c.id)
            }
            .setNegativeButton("Cancel", null)
            .create()
        dialog.show()
    }

    private fun addChip(chipGroup: ChipGroup, name: String, day: Int, customerId: Int) {
        val chip = Chip(this)
        chip.text = name
        chip.isCloseIconVisible = true
        chip.setOnCloseIconClickListener {
            chipGroup.removeView(chip)
            stops[day]?.removeAll { it.first == customerId }
        }
        chipGroup.addView(chip)
    }

    private fun savePlan(submit: Boolean) {
        val token = TokenPrefs.getToken(this) ?: return

        val stopList = mutableListOf<Map<String, Any>>()
        for ((day, list) in stops) {
            for ((customerId, _) in list) {
                stopList.add(mapOf("customer_id" to customerId, "day_of_week" to day))
            }
        }

        if (stopList.isEmpty()) {
            Toast.makeText(this, "Add at least one stop.", Toast.LENGTH_SHORT).show()
            return
        }

        binding.btnSave.isEnabled   = false
        binding.btnSubmit.isEnabled = false

        val fmt    = DateTimeFormatter.ofPattern("yyyy-MM-dd")
        val body   = mapOf<String, Any?>(
            "week_start" to weekStart.format(fmt),
            "notes"      to binding.etNotes.text?.toString()?.ifBlank { null },
            "stops"      to stopList
        )

        lifecycleScope.launch {
            try {
                val r = ApiClient.create(token).createTourPlan(body)
                if (r.isSuccessful) {
                    val plan = r.body()
                    if (submit && plan != null) {
                        ApiClient.create(token).submitTourPlan(plan.id)
                    }
                    Toast.makeText(
                        this@CreateTourPlanActivity,
                        if (submit) "Plan submitted for approval." else "Plan saved as draft.",
                        Toast.LENGTH_SHORT
                    ).show()
                    finish()
                } else {
                    Toast.makeText(this@CreateTourPlanActivity, "Failed to save plan.", Toast.LENGTH_SHORT).show()
                }
            } catch (_: Exception) {
                Toast.makeText(this@CreateTourPlanActivity, "Network error.", Toast.LENGTH_SHORT).show()
            } finally {
                binding.btnSave.isEnabled   = true
                binding.btnSubmit.isEnabled = true
            }
        }
    }

    override fun onSupportNavigateUp(): Boolean { finish(); return true }

    companion object {
        private fun getNextMonday(): LocalDate {
            val today = LocalDate.now()
            return today.plusDays(((8 - today.dayOfWeek.value) % 7).toLong().coerceAtLeast(0))
        }
    }
}
