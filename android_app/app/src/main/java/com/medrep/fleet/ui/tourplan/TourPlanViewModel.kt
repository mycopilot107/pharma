package com.medrep.fleet.ui.tourplan

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.TourPlan
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

class TourPlanViewModel : ViewModel() {

    private val _plans   = MutableLiveData<List<TourPlan>>(emptyList())
    val plans: LiveData<List<TourPlan>> = _plans

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    private val _error   = MutableLiveData<String?>(null)
    val error: LiveData<String?> = _error

    fun load(context: Context, weekStart: String? = null) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            _error.value   = null
            try {
                val r = ApiClient.create(token).getTourPlans(weekStart)
                if (r.isSuccessful) {
                    _plans.value = r.body()?.data ?: emptyList()
                } else {
                    _error.value = "Failed to load tour plans."
                }
            } catch (e: Exception) {
                _error.value = "Network error."
            } finally {
                _loading.value = false
            }
        }
    }

    fun submit(context: Context, planId: Int, onDone: (Boolean, String) -> Unit) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            try {
                val r = ApiClient.create(token).submitTourPlan(planId)
                if (r.isSuccessful) {
                    onDone(true, "Tour plan submitted for approval.")
                } else {
                    onDone(false, r.errorBody()?.string() ?: "Submit failed.")
                }
            } catch (e: Exception) {
                onDone(false, "Network error.")
            }
        }
    }

    fun delete(context: Context, planId: Int, onDone: (Boolean) -> Unit) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            try {
                val r = ApiClient.create(token).deleteTourPlan(planId)
                onDone(r.isSuccessful)
            } catch (e: Exception) {
                onDone(false)
            }
        }
    }
}
