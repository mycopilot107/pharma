package com.medrep.fleet.ui.visits

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.medrep.fleet.data.model.Visit
import com.medrep.fleet.databinding.ItemVisitBinding
import java.time.Instant
import java.time.ZoneId
import java.time.ZonedDateTime
import java.time.format.DateTimeFormatter

class VisitsAdapter(
    private val onClick: (Visit) -> Unit
) : ListAdapter<Visit, VisitsAdapter.VH>(DIFF) {

    inner class VH(val binding: ItemVisitBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemVisitBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val visit = getItem(position)
        holder.binding.apply {
            val name = visit.customer?.name ?: visit.placeName ?: "Visit #${visit.id}"
            tvCustomerName.text = name

            tvCheckIn.text  = formatTs(visit.checkInTime, "In:")  ?: "In: —"
            tvCheckOut.text = if (visit.checkOutTime != null)
                formatTs(visit.checkOutTime, "Out:") ?: "Out: —"
            else
                "Ongoing"

            val statusLabel = when (visit.status) {
                "completed"   -> "Completed"
                "in_progress" -> "In Progress"
                "planned"     -> "Planned"
                else          -> visit.status?.replaceFirstChar { it.uppercase() } ?: "—"
            }
            tvStatus.text = statusLabel

            tvMockWarning.visibility = if (visit.isMockDetected) View.VISIBLE else View.GONE
            root.setOnClickListener { onClick(visit) }
        }
    }

    private fun formatTs(ts: String?, prefix: String): String? {
        if (ts.isNullOrBlank()) return null
        return try {
            val zdt = ZonedDateTime.ofInstant(Instant.parse(ts), ZoneId.systemDefault())
            val time = DateTimeFormatter.ofPattern("d MMM, hh:mm a").format(zdt)
            "$prefix $time"
        } catch (_: Exception) {
            "$prefix $ts"
        }
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<Visit>() {
            override fun areItemsTheSame(a: Visit, b: Visit) = a.id == b.id
            override fun areContentsTheSame(a: Visit, b: Visit) = a == b
        }
    }
}
