package com.medrep.fleet.ui.tourplan

import android.graphics.Color
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.google.android.material.card.MaterialCardView
import com.medrep.fleet.R
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.TourPlan
import com.medrep.fleet.data.model.TourPlanStop
import com.medrep.fleet.data.prefs.TokenPrefs
import com.medrep.fleet.databinding.ActivityTourPlanDetailBinding
import kotlinx.coroutines.launch

class TourPlanDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityTourPlanDetailBinding
    private var planId: Int = -1

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityTourPlanDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        planId = intent.getIntExtra(EXTRA_PLAN_ID, -1)
        if (planId == -1) { finish(); return }

        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.title = "Tour Plan"

        loadPlan()
    }

    private fun loadPlan() {
        val token = TokenPrefs.getToken(this) ?: return
        binding.progressBar.visibility = View.VISIBLE
        lifecycleScope.launch {
            try {
                val r = ApiClient.create(token).getTourPlan(planId)
                if (r.isSuccessful) {
                    r.body()?.let { renderPlan(it) }
                } else {
                    Toast.makeText(this@TourPlanDetailActivity, "Failed to load.", Toast.LENGTH_SHORT).show()
                }
            } catch (_: Exception) {
                Toast.makeText(this@TourPlanDetailActivity, "Network error.", Toast.LENGTH_SHORT).show()
            } finally {
                binding.progressBar.visibility = View.GONE
            }
        }
    }

    private fun renderPlan(plan: TourPlan) {
        supportActionBar?.title = plan.weekLabel

        binding.tvWeekLabel.text  = plan.weekLabel
        binding.tvStatus.text     = plan.statusLabel
        binding.tvStatus.setTextColor(statusColor(plan.status))

        if (plan.status == "rejected" && !plan.rejectionReason.isNullOrBlank()) {
            binding.tvRejection.visibility = View.VISIBLE
            binding.tvRejection.text       = "Rejected: ${plan.rejectionReason}"
        }

        // Submit button — only for draft
        if (plan.status == "draft") {
            binding.btnSubmit.visibility = View.VISIBLE
            binding.btnSubmit.setOnClickListener { submitPlan() }
        }

        // Build day groups
        val stopsByDay = plan.stops.groupBy { it.dayOfWeek }
        binding.daysContainer.removeAllViews()

        for (day in 1..6) {
            val dayStops = stopsByDay[day] ?: emptyList()
            if (dayStops.isEmpty() && plan.status != "approved") continue

            val dayView = LayoutInflater.from(this)
                .inflate(R.layout.layout_day_detail, binding.daysContainer, false)

            val label = dayStops.firstOrNull()?.let {
                "${it.dayName}, ${it.date}"
            } ?: "Day $day"

            dayView.findViewById<TextView>(R.id.tvDayLabel).text = label

            val stopsContainer = dayView.findViewById<LinearLayout>(R.id.stopsContainer)
            dayStops.forEach { stop ->
                val row = buildStopRow(stop, plan.status == "approved")
                stopsContainer.addView(row)
            }

            if (dayStops.isEmpty()) {
                val empty = TextView(this)
                empty.text = "Rest day"
                empty.setTextColor(Color.parseColor("#94A3B8"))
                empty.textSize = 12f
                empty.setPadding(0, 4, 0, 4)
                stopsContainer.addView(empty)
            }

            binding.daysContainer.addView(dayView)
        }
    }

    private fun buildStopRow(stop: TourPlanStop, showActual: Boolean): View {
        val row = LayoutInflater.from(this).inflate(R.layout.item_tour_plan_stop, null, false)

        row.findViewById<TextView>(R.id.tvCustomerName).text =
            stop.customer?.name ?: "Customer #${stop.customerId}"
        row.findViewById<TextView>(R.id.tvArea).apply {
            text = stop.customer?.type?.let { "${it.replaceFirstChar(Char::uppercaseChar)}" }
                ?: ""
            if (stop.area != null) append(" · ${stop.area}")
        }

        val dot    = row.findViewById<View>(R.id.statusDot)
        val tvBadge = row.findViewById<TextView>(R.id.tvBadge)

        if (!showActual) {
            dot.setBackgroundColor(Color.parseColor("#FBBF24"))
            tvBadge.visibility = View.GONE
        } else if (stop.isVisited) {
            dot.setBackgroundColor(Color.parseColor("#10B981"))
            tvBadge.text = "Visited"
            tvBadge.setTextColor(Color.parseColor("#059669"))
        } else {
            dot.setBackgroundColor(Color.parseColor("#F87171"))
            tvBadge.text = "Missed"
            tvBadge.setTextColor(Color.parseColor("#DC2626"))
        }

        return row
    }

    private fun submitPlan() {
        val token = TokenPrefs.getToken(this) ?: return
        binding.btnSubmit.isEnabled = false
        lifecycleScope.launch {
            try {
                val r = ApiClient.create(token).submitTourPlan(planId)
                if (r.isSuccessful) {
                    Toast.makeText(this@TourPlanDetailActivity, "Plan submitted for approval.", Toast.LENGTH_SHORT).show()
                    loadPlan()
                } else {
                    Toast.makeText(this@TourPlanDetailActivity, "Submit failed.", Toast.LENGTH_SHORT).show()
                    binding.btnSubmit.isEnabled = true
                }
            } catch (_: Exception) {
                Toast.makeText(this@TourPlanDetailActivity, "Network error.", Toast.LENGTH_SHORT).show()
                binding.btnSubmit.isEnabled = true
            }
        }
    }

    private fun statusColor(status: String) = when (status) {
        "approved"  -> Color.parseColor("#15803D")
        "submitted" -> Color.parseColor("#1D4ED8")
        "rejected"  -> Color.parseColor("#B91C1C")
        else        -> Color.parseColor("#475569")
    }

    override fun onSupportNavigateUp(): Boolean { finish(); return true }

    companion object {
        const val EXTRA_PLAN_ID = "plan_id"
    }
}
