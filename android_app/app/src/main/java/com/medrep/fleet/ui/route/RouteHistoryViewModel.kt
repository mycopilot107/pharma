package com.medrep.fleet.ui.route

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.TrackingPoint
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

class RouteHistoryViewModel : ViewModel() {

    private val _points = MutableLiveData<List<TrackingPoint>>(emptyList())
    val points: LiveData<List<TrackingPoint>> = _points

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    fun load(context: Context, date: String) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getRouteHistory(date)
                if (r.isSuccessful) _points.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {
            } finally {
                _loading.value = false
            }
        }
    }
}
