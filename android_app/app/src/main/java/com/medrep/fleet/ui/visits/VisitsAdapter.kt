package com.medrep.fleet.ui.visits

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.medrep.fleet.data.model.Visit
import com.medrep.fleet.databinding.ItemVisitBinding

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
            tvCustomerName.text = visit.customer?.name ?: "Customer #${visit.customerId}"
            tvCheckIn.text      = "In: ${visit.checkInTime}"
            tvCheckOut.text     = if (visit.checkOutTime != null) "Out: ${visit.checkOutTime}" else "Ongoing"
            tvStatus.text       = visit.status.replaceFirstChar { it.uppercase() }

            if (visit.isMockDetected) {
                tvMockWarning.visibility = android.view.View.VISIBLE
            } else {
                tvMockWarning.visibility = android.view.View.GONE
            }

            root.setOnClickListener { onClick(visit) }
        }
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<Visit>() {
            override fun areItemsTheSame(a: Visit, b: Visit) = a.id == b.id
            override fun areContentsTheSame(a: Visit, b: Visit) = a == b
        }
    }
}
