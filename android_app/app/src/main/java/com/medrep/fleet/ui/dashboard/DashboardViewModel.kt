package com.medrep.fleet.ui.dashboard

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.DashboardData
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

class DashboardViewModel : ViewModel() {

    private val _dashboard = MutableLiveData<DashboardData>()
    val dashboard: LiveData<DashboardData> = _dashboard

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    private val _error = MutableLiveData<String?>(null)
    val error: LiveData<String?> = _error

    fun load(context: Context) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            _error.value = null
            try {
                val r = ApiClient.create(token).getDashboard()
                if (r.isSuccessful) _dashboard.value = r.body()
                else _error.value = "Failed to load dashboard"
            } catch (e: Exception) {
                _error.value = "Network error: ${e.message}"
            } finally {
                _loading.value = false
            }
        }
    }

    fun clockIn(context: Context) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            try {
                ApiClient.create(token).clockIn(emptyMap())
                load(context)
            } catch (_: Exception) {}
        }
    }

    fun clockOut(context: Context) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            try {
                ApiClient.create(token).clockOut(emptyMap())
                load(context)
            } catch (_: Exception) {}
        }
    }
}
