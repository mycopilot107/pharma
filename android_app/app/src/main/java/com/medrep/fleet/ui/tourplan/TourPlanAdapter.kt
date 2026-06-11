package com.medrep.fleet.ui.tourplan

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.medrep.fleet.data.model.TourPlanEntry
import com.medrep.fleet.databinding.ItemTourPlanBinding

class TourPlanAdapter : ListAdapter<TourPlanEntry, TourPlanAdapter.VH>(DIFF) {

    inner class VH(val binding: ItemTourPlanBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemTourPlanBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val entry = getItem(position)
        holder.binding.apply {
            tvDate.text     = entry.date
            tvCustomer.text = entry.customer?.name ?: "Customer #${entry.customerId}"
            tvStatus.text   = entry.status.replaceFirstChar { it.uppercase() }

            val statusColor = when (entry.status) {
                "done"   -> android.graphics.Color.parseColor("#4CAF50")
                "missed" -> android.graphics.Color.parseColor("#F44336")
                else     -> android.graphics.Color.parseColor("#2196F3")
            }
            tvStatus.setTextColor(statusColor)
        }
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<TourPlanEntry>() {
            override fun areItemsTheSame(a: TourPlanEntry, b: TourPlanEntry) = a.id == b.id
            override fun areContentsTheSame(a: TourPlanEntry, b: TourPlanEntry) = a == b
        }
    }
}
