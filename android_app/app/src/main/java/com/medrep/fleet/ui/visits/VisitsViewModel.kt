package com.medrep.fleet.ui.visits

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.Visit
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

class VisitsViewModel : ViewModel() {

    private val _visits = MutableLiveData<List<Visit>>(emptyList())
    val visits: LiveData<List<Visit>> = _visits

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    fun load(context: Context) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getVisits()
                if (r.isSuccessful) _visits.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {
            } finally {
                _loading.value = false
            }
        }
    }
}
