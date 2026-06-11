package com.medrep.fleet.ui.notifications

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.AppNotification
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

class NotificationsViewModel : ViewModel() {

    private val _notifications = MutableLiveData<List<AppNotification>>(emptyList())
    val notifications: LiveData<List<AppNotification>> = _notifications

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    fun load(context: Context) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getNotifications()
                if (r.isSuccessful) _notifications.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {
            } finally {
                _loading.value = false
            }
        }
    }

    fun markRead(context: Context, id: Int) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            try {
                ApiClient.create(token).markNotificationRead(id)
                load(context)
            } catch (_: Exception) {}
        }
    }
}
