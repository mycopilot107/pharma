package com.medrep.fleet.ui.leaves

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.Leave
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

class LeavesViewModel : ViewModel() {

    private val _leaves = MutableLiveData<List<Leave>>(emptyList())
    val leaves: LiveData<List<Leave>> = _leaves

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    fun load(context: Context) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getLeaves()
                if (r.isSuccessful) _leaves.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {
            } finally {
                _loading.value = false
            }
        }
    }

    fun applyLeave(context: Context, type: String, startDate: String, endDate: String, reason: String) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            try {
                ApiClient.create(token).applyLeave(
                    mapOf("type" to type, "start_date" to startDate, "end_date" to endDate, "reason" to reason)
                )
                load(context)
            } catch (_: Exception) {}
        }
    }
}
