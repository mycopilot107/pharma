package com.medrep.fleet.ui.visits

import android.content.Context
import android.net.Uri
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.Customer
import com.medrep.fleet.data.model.Visit
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody

class VisitDetailViewModel : ViewModel() {

    private val _visit   = MutableLiveData<Visit?>()
    val visit: LiveData<Visit?> = _visit

    private val _customers = MutableLiveData<List<Customer>>(emptyList())
    val customers: LiveData<List<Customer>> = _customers

    private val _loading = MutableLiveData(false)
    val loading: LiveData<Boolean> = _loading

    private val _error   = MutableLiveData<String?>(null)
    val error: LiveData<String?> = _error

    private val _saved   = MutableLiveData(false)
    val saved: LiveData<Boolean> = _saved

    var pendingPhotoUri: android.net.Uri? = null

    fun load(context: Context, visitId: Int) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            _loading.value = true
            try {
                val r = ApiClient.create(token).getVisit(visitId)
                if (r.isSuccessful) _visit.value = r.body()
                else _error.value = "Failed to load visit"
            } catch (e: Exception) {
                _error.value = "Network error: ${e.message}"
            } finally {
                _loading.value = false
            }
        }
    }

    fun loadCustomers(context: Context, type: String? = null, search: String? = null) {
        val token = TokenPrefs.getToken(context) ?: return
        viewModelScope.launch {
            try {
                val r = ApiClient.create(token).getCustomers(perPage = 100, type = type, search = search)
                if (r.isSuccessful) _customers.value = r.body()?.data ?: emptyList()
            } catch (_: Exception) {}
        }
    }

    fun checkIn(
        context: Context,
        customerId: Int?,
        visitType: String,
        notes: String,
        followUpDate: String?,
        photoUri: Uri?
    ) {
        val token = TokenPrefs.getToken(context) ?: return
        val api   = ApiClient.create(token)
        viewModelScope.launch {
            _loading.value = true
            _error.value   = null
            try {
                val body = mutableMapOf<String, Any?>(
                    "visit_type"     to visitType,
                    "customer_id"    to customerId,
                    "notes"          to notes.ifBlank { null },
                    "follow_up_date" to followUpDate,
                    "start_now"      to true,
                )
                val r = api.checkIn(body)
                if (r.isSuccessful) {
                    val newVisit = r.body()!!
                    if (photoUri != null) uploadPhoto(context, api, newVisit.id, photoUri)
                    _saved.value = true
                } else {
                    val msg = try {
                        org.json.JSONObject(r.errorBody()?.string() ?: "{}").getString("message")
                    } catch (_: Exception) { "Check-in failed (${r.code()})" }
                    _error.value = msg
                }
            } catch (e: Exception) {
                _error.value = "Network error: ${e.message}"
            } finally {
                _loading.value = false
            }
        }
    }

    fun save(
        context: Context,
        notes: String,
        products: List<String>,
        samples: Int,
        followUpDate: String?,
        photoUri: Uri?
    ) {
        val token = TokenPrefs.getToken(context) ?: return
        val api   = ApiClient.create(token)
        val current = _visit.value

        viewModelScope.launch {
            _loading.value = true
            _error.value   = null
            try {
                if (current != null && current.status == "ongoing") {
                    val body = mutableMapOf<String, Any?>(
                        "notes"             to notes,
                        "products_promoted" to products,
                        "samples_given"     to samples,
                        "follow_up_date"    to followUpDate,
                    )
                    val r = api.checkOut(current.id, body)
                    if (r.isSuccessful) {
                        val updated = r.body()!!
                        if (photoUri != null) uploadPhoto(context, api, updated.id, photoUri)
                        _saved.value = true
                    } else {
                        _error.value = "Failed to check out"
                    }
                }
            } catch (e: Exception) {
                _error.value = "Network error: ${e.message}"
            } finally {
                _loading.value = false
            }
        }
    }

    private suspend fun uploadPhoto(
        context: Context,
        api: com.medrep.fleet.data.api.ApiService,
        visitId: Int,
        uri: Uri
    ) {
        try {
            val bytes = context.contentResolver.openInputStream(uri)?.readBytes() ?: return
            val rb    = bytes.toRequestBody("image/jpeg".toMediaTypeOrNull())
            val part  = MultipartBody.Part.createFormData("photo", "visit_photo.jpg", rb)
            api.uploadVisitPhoto(visitId, part)
        } catch (_: Exception) {}
    }
}
