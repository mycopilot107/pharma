package com.medrep.fleet.ui.customers

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.medrep.fleet.data.model.Customer
import com.medrep.fleet.databinding.ItemCustomerBinding

class CustomersAdapter : ListAdapter<Customer, CustomersAdapter.VH>(DIFF) {

    inner class VH(val binding: ItemCustomerBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemCustomerBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val c = getItem(position)
        holder.binding.apply {
            tvName.text      = c.name
            tvType.text      = c.type?.replaceFirstChar { it.uppercase() } ?: ""
            tvCity.text      = c.city ?: ""
            tvPhone.text     = c.phone ?: ""
            tvVisitCount.text = ""
        }
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<Customer>() {
            override fun areItemsTheSame(a: Customer, b: Customer) = a.id == b.id
            override fun areContentsTheSame(a: Customer, b: Customer) = a == b
        }
    }
}
