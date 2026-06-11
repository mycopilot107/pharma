package com.medrep.fleet.ui.tourplan

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.TourPlanEntry
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

class TourPlanViewModel : ViewModel() {

    private val _entries = MutableLiveData<List<TourPlanEntry>>(emptyList())
    val entries: LiveData<List<TourPlanEntry>> = _entries

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    fun load(context: Context, weekStart: String) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getTourPlan(weekStart)
                if (r.isSuccessful) _entries.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {
            } finally {
                _loading.value = false
            }
        }
    }
}
