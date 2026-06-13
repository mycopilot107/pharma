package com.medrep.fleet.ui.orders

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.Customer
import com.medrep.fleet.data.model.Product
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

data class OrderLineItem(
    val productId: Int,
    val quantity: Int,
    val discount: Double,
    val remark: String?
)

class CreateOrderViewModel : ViewModel() {

    private val _customers = MutableLiveData<List<Customer>>(emptyList())
    val customers: LiveData<List<Customer>> = _customers

    private val _products = MutableLiveData<List<Product>>(emptyList())
    val products: LiveData<List<Product>> = _products

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    private val _error = MutableLiveData<String?>(null)
    val error: LiveData<String?> = _error

    private val _saved = MutableLiveData(false)
    val saved: LiveData<Boolean> = _saved

    fun loadCustomers(context: Context, type: String? = null, search: String? = null) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            try {
                val r = ApiClient.create(token).getCustomers(perPage = 100, type = type, search = search)
                if (r.isSuccessful) _customers.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {}
        }
    }

    fun loadProducts(context: Context) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            try {
                val r = ApiClient.create(token).getProducts()
                if (r.isSuccessful) _products.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {}
        }
    }

    fun submitOrder(
        context: Context,
        customerId: Int,
        orderDate: String,
        items: List<OrderLineItem>,
        notes: String
    ) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            _error.value   = null
            try {
                val body = mutableMapOf<String, Any?>(
                    "customer_id" to customerId,
                    "order_date"  to orderDate,
                    "notes"       to notes.ifBlank { null },
                    "items"       to items.map { item ->
                        mapOf(
                            "product_id" to item.productId,
                            "quantity"   to item.quantity,
                            "discount"   to item.discount,
                            "remark"     to item.remark?.ifBlank { null },
                        )
                    }
                )
                val r = ApiClient.create(token).createOrder(body)
                if (r.isSuccessful) {
                    _saved.value = true
                } else {
                    val msg = try {
                        val json = org.json.JSONObject(r.errorBody()?.string() ?: "{}")
                        when {
                            json.has("message") -> json.getString("message")
                            json.has("errors")  -> {
                                val errs = json.getJSONObject("errors")
                                errs.keys().asSequence().map { errs.getJSONArray(it).getString(0) }.first()
                            }
                            else -> "Submit failed (${r.code()})"
                        }
                    } catch (_: Exception) { "Submit failed (${r.code()})" }
                    _error.value = msg
                }
            } catch (e: Exception) {
                _error.value = "Network error: ${e.message}"
            } finally {
                _loading.value = false
            }
        }
    }
}
