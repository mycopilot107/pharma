package com.medrep.fleet.data.prefs

import android.content.Context
import android.content.SharedPreferences
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey

object TokenPrefs {

    private const val FILE_NAME   = "medrep_secure_prefs"
    private const val KEY_TOKEN   = "auth_token"
    private const val KEY_USER_ID = "user_id"
    private const val KEY_USER_NAME = "user_name"
    private const val KEY_USER_EMAIL = "user_email"
    private const val KEY_USER_ROLE  = "user_role"

    private lateinit var prefs: SharedPreferences

    fun init(context: Context) {
        val masterKey = MasterKey.Builder(context)
            .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
            .build()
        prefs = EncryptedSharedPreferences.create(
            context,
            FILE_NAME,
            masterKey,
            EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
            EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
        )
    }

    fun saveToken(context: Context, token: String) {
        ensureInit(context)
        prefs.edit().putString(KEY_TOKEN, token).apply()
    }

    fun getToken(context: Context): String? {
        ensureInit(context)
        return prefs.getString(KEY_TOKEN, null)
    }

    fun saveUser(context: Context, id: Int, name: String, email: String, role: String) {
        ensureInit(context)
        prefs.edit()
            .putInt(KEY_USER_ID, id)
            .putString(KEY_USER_NAME, name)
            .putString(KEY_USER_EMAIL, email)
            .putString(KEY_USER_ROLE, role)
            .apply()
    }

    fun getUserId(context: Context): Int {
        ensureInit(context)
        return prefs.getInt(KEY_USER_ID, -1)
    }

    fun getUserName(context: Context): String {
        ensureInit(context)
        return prefs.getString(KEY_USER_NAME, "") ?: ""
    }

    fun getUserEmail(context: Context): String {
        ensureInit(context)
        return prefs.getString(KEY_USER_EMAIL, "") ?: ""
    }

    fun getUserRole(context: Context): String {
        ensureInit(context)
        return prefs.getString(KEY_USER_ROLE, "") ?: ""
    }

    fun clear(context: Context) {
        ensureInit(context)
        prefs.edit().clear().apply()
    }

    fun isLoggedIn(context: Context): Boolean = getToken(context) != null

    private fun ensureInit(context: Context) {
        if (!::prefs.isInitialized) init(context.applicationContext)
    }
}
