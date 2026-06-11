package com.medrep.fleet.ui.notifications

import android.graphics.Typeface
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.medrep.fleet.data.model.AppNotification
import com.medrep.fleet.databinding.ItemNotificationBinding

class NotificationsAdapter(
    private val onRead: (AppNotification) -> Unit
) : ListAdapter<AppNotification, NotificationsAdapter.VH>(DIFF) {

    inner class VH(val binding: ItemNotificationBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val b = ItemNotificationBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return VH(b)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val n = getItem(position)
        holder.binding.apply {
            tvTitle.text   = n.title
            tvBody.text    = n.body
            tvTime.text    = n.createdAt

            val isUnread = n.readAt == null
            tvTitle.setTypeface(null, if (isUnread) Typeface.BOLD else Typeface.NORMAL)

            root.setOnClickListener {
                if (isUnread) onRead(n)
            }
        }
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<AppNotification>() {
            override fun areItemsTheSame(a: AppNotification, b: AppNotification) = a.id == b.id
            override fun areContentsTheSame(a: AppNotification, b: AppNotification) = a == b
        }
    }
}
