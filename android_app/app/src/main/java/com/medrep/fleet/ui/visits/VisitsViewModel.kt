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

    private val _currentPage = MutableLiveData(1)
    val currentPage: LiveData<Int> = _currentPage

    private val _totalPages = MutableLiveData(1)
    val totalPages: LiveData<Int> = _totalPages

    private val _totalCount = MutableLiveData(0)
    val totalCount: LiveData<Int> = _totalCount

    private var searchQuery: String = ""
    private var dateFilter: String? = java.time.LocalDate.now().toString()  // default = today
    private var page: Int = 1

    companion object {
        const val PER_PAGE = 5
    }

    fun load(context: Context) = fetch(context, page)

    fun search(context: Context, query: String) {
        searchQuery = query
        page = 1
        fetch(context, 1)
    }

    fun filterDate(context: Context, date: String?) {
        dateFilter = date
        page = 1
        fetch(context, 1)
    }

    fun goToPage(context: Context, newPage: Int) {
        page = newPage
        fetch(context, newPage)
    }

    private fun fetch(context: Context, fetchPage: Int) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getVisits(
                    page    = fetchPage,
                    perPage = PER_PAGE,
                    date    = dateFilter,
                    search  = searchQuery.ifBlank { null },
                )
                if (r.isSuccessful) {
                    val body = r.body()
                    _visits.value      = body?.data         ?: emptyList()
                    _currentPage.value = body?.current_page ?: 1
                    _totalPages.value  = body?.last_page    ?: 1
                    _totalCount.value  = body?.total        ?: 0
                }
            } catch (_: Exception) {
            } finally {
                _loading.value = false
            }
        }
    }
}
