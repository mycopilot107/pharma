package com.medrep.fleet.ui.expenses

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.medrep.fleet.data.model.Expense
import com.medrep.fleet.databinding.ItemExpenseBinding

class ExpensesAdapter : ListAdapter<Expense, ExpensesAdapter.VH>(DIFF) {

    inner class VH(val binding: ItemExpenseBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemExpenseBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val e = getItem(position)
        holder.binding.apply {
            tvDate.text      = e.date
            tvCategory.text  = e.category.replaceFirstChar { it.uppercase() }
            tvAmount.text    = "₹%.2f".format(e.amount)
            tvStatus.text    = e.status.replaceFirstChar { it.uppercase() }
            tvDescription.text = e.description ?: ""
        }
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<Expense>() {
            override fun areItemsTheSame(a: Expense, b: Expense) = a.id == b.id
            override fun areContentsTheSame(a: Expense, b: Expense) = a == b
        }
    }
}
