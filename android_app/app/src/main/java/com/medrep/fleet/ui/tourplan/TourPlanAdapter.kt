package com.medrep.fleet.ui.tourplan

import android.graphics.Color
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.medrep.fleet.data.model.TourPlan
import com.medrep.fleet.databinding.ItemTourPlanBinding

class TourPlanAdapter(
    private val onClick: (TourPlan) -> Unit
) : ListAdapter<TourPlan, TourPlanAdapter.VH>(DIFF) {

    inner class VH(val b: ItemTourPlanBinding) : RecyclerView.ViewHolder(b.root) {
        init { b.root.setOnClickListener { onClick(getItem(adapterPosition)) } }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH =
        VH(ItemTourPlanBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindViewHolder(holder: VH, position: Int) {
        val plan = getItem(position)
        holder.b.apply {
            tvDate.text     = plan.weekLabel
            tvCustomer.text = "${plan.stopsCount} stop${if (plan.stopsCount != 1) "s" else ""} planned"
            tvStatus.text   = plan.statusLabel

            val (textColor, bgRes) = when (plan.status) {
                "approved"  -> Color.parseColor("#15803D") to com.medrep.fleet.R.drawable.bg_chip_success
                "submitted" -> Color.parseColor("#1D4ED8") to com.medrep.fleet.R.drawable.bg_chip_info
                "rejected"  -> Color.parseColor("#B91C1C") to com.medrep.fleet.R.drawable.bg_chip_error
                else        -> Color.parseColor("#475569") to com.medrep.fleet.R.drawable.bg_chip_info
            }
            tvStatus.setTextColor(textColor)
            tvStatus.setBackgroundResource(bgRes)
        }
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<TourPlan>() {
            override fun areItemsTheSame(a: TourPlan, b: TourPlan) = a.id == b.id
            override fun areContentsTheSame(a: TourPlan, b: TourPlan) = a == b
        }
    }
}
