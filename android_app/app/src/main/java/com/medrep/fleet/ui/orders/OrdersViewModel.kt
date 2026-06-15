package com.medrep.fleet.ui.orders

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.Order
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

class OrdersViewModel : ViewModel() {

    private val _orders = MutableLiveData<List<Order>>(emptyList())
    val orders: LiveData<List<Order>> = _orders

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    private var currentSearch: String? = null

    fun load(context: Context, search: String? = currentSearch) {
        currentSearch = search
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getOrders(search = search)
                if (r.isSuccessful) _orders.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {
            } finally {
                _loading.value = false
            }
        }
    }
}
