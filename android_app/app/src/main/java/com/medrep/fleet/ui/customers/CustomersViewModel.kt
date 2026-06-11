package com.medrep.fleet.ui.customers

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.Customer
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class CustomersViewModel : ViewModel() {

    private val _customers = MutableLiveData<List<Customer>>(emptyList())
    val customers: LiveData<List<Customer>> = _customers

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    private var searchJob: Job? = null

    fun load(context: Context, query: String? = null) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getCustomers(search = query)
                if (r.isSuccessful) _customers.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {
            } finally {
                _loading.value = false
            }
        }
    }

    fun search(context: Context, query: String) {
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            delay(300)
            load(context, query.ifEmpty { null })
        }
    }
}
