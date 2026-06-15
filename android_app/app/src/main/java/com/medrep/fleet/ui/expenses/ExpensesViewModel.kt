package com.medrep.fleet.ui.expenses

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.Expense
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch

class ExpensesViewModel : ViewModel() {

    private val _expenses = MutableLiveData<List<Expense>>(emptyList())
    val expenses: LiveData<List<Expense>> = _expenses

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    private val _error = MutableLiveData<String?>(null)
    val error: LiveData<String?> = _error

    private val _saved = MutableLiveData(false)
    val saved: LiveData<Boolean> = _saved

    fun load(context: Context) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getExpenses()
                if (r.isSuccessful) _expenses.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {
            } finally {
                _loading.value = false
            }
        }
    }

    fun addExpense(context: Context, category: String, amount: Double, description: String, date: String) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            _error.value = null
            try {
                val r = ApiClient.create(token).createExpense(
                    mapOf(
                        "type"         to category.lowercase(),
                        "amount"       to amount,
                        "description"  to description,
                        "expense_date" to date
                    )
                )
                if (r.isSuccessful) {
                    _saved.value = true
                    load(context)
                } else {
                    val msg = try {
                        org.json.JSONObject(r.errorBody()?.string() ?: "{}").getString("message")
                    } catch (_: Exception) { "Failed to save expense (${r.code()})" }
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
