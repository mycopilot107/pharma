package com.medrep.fleet.ui.visits

import android.content.Context
import android.net.Uri
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.model.Visit
import com.medrep.fleet.data.prefs.TokenPrefs
import kotlinx.coroutines.launch
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody

class VisitDetailViewModel : ViewModel() {

    private val _visit   = MutableLiveData<Visit?>()
    val visit: LiveData<Visit?> = _visit

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

    fun save(
        context: Context,
        notes: String,
        products: List<String>,
        samples: Int,
        followUpDate: String?,
        signatureBase64: String?,
        photoUri: Uri?
    ) {
        val token = TokenPrefs.getToken(context) ?: return
        val api   = ApiClient.create(token)
        val current = _visit.value

        viewModelScope.launch {
            _loading.value = true
            _error.value   = null
            try {
                if (current == null) {
                    // New visit — check in first (customer selection would be done before reaching here)
                    // For now save what we have
                    _saved.value = true
                } else if (current.status == "ongoing") {
                    val body = mutableMapOf<String, Any?>(
                        "notes"            to notes,
                        "products_promoted" to products,
                        "samples_given"    to samples,
                        "follow_up_date"   to followUpDate,
                        "signature_base64" to signatureBase64
                    )
                    val r = api.checkOut(current.id, body)
                    if (r.isSuccessful) {
                        val updated = r.body()!!
                        // Upload photo if any
                        if (photoUri != null) {
                            uploadPhoto(context, api, updated.id, photoUri)
                        }
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
