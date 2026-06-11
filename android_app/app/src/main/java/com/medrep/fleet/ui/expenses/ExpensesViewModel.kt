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
            try {
                ApiClient.create(token).createExpense(
                    mapOf("category" to category, "amount" to amount, "description" to description, "date" to date)
                )
                load(context)
            } catch (_: Exception) {}
        }
    }
}
