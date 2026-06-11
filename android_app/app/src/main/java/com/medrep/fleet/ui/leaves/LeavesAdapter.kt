package com.medrep.fleet.ui.leaves

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.medrep.fleet.data.model.Leave
import com.medrep.fleet.databinding.ItemLeaveBinding

class LeavesAdapter : ListAdapter<Leave, LeavesAdapter.VH>(DIFF) {

    inner class VH(val binding: ItemLeaveBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemLeaveBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val l = getItem(position)
        holder.binding.apply {
            tvType.text      = l.type.replaceFirstChar { it.uppercase() }
            tvDates.text     = "${l.startDate} → ${l.endDate}"
            tvStatus.text    = l.status.replaceFirstChar { it.uppercase() }
            tvReason.text    = l.reason ?: ""
        }
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<Leave>() {
            override fun areItemsTheSame(a: Leave, b: Leave) = a.id == b.id
            override fun areContentsTheSame(a: Leave, b: Leave) = a == b
        }
    }
}
