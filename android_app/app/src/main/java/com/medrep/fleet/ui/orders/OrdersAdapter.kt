package com.medrep.fleet.ui.orders

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.medrep.fleet.data.model.Order
import com.medrep.fleet.databinding.ItemOrderBinding

class OrdersAdapter : ListAdapter<Order, OrdersAdapter.VH>(DIFF) {

    inner class VH(val binding: ItemOrderBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemOrderBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val o = getItem(position)
        holder.binding.apply {
            tvCustomer.text = o.customer?.name ?: "Customer #${o.customerId}"
            tvDate.text     = o.date
            tvTotal.text    = "₹%.2f".format(o.total)
            tvStatus.text   = o.status.replaceFirstChar { it.uppercase() }
        }
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<Order>() {
            override fun areItemsTheSame(a: Order, b: Order) = a.id == b.id
            override fun areContentsTheSame(a: Order, b: Order) = a == b
        }
    }
}
