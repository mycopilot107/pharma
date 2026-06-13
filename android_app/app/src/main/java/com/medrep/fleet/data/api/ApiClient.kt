package com.medrep.fleet.data.api

import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object ApiClient {

    private const val BASE_URL = "https://mrvisitstrack.net/api/v1/"

    /** Broadcast action fired when the server returns 401. MainActivity listens and goes to login. */
    const val ACTION_SESSION_EXPIRED = "com.medrep.fleet.SESSION_EXPIRED"

    // Holds the application context so the 401 interceptor can broadcast without a Context param.
    var appContext: android.content.Context? = null

    fun create(token: String? = null): ApiService {
        val client = OkHttpClient.Builder()
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(30, TimeUnit.SECONDS)
            .addInterceptor { chain ->
                val request = chain.request().newBuilder()
                    .header("Accept", "application/json")
                    .header("Content-Type", "application/json")
                    .apply { if (token != null) header("Authorization", "Bearer $token") }
                    .build()
                val response = chain.proceed(request)
                if (response.code == 401) {
                    appContext?.let { ctx ->
                        com.medrep.fleet.data.prefs.TokenPrefs.clear(ctx)
                        val intent = android.content.Intent(ACTION_SESSION_EXPIRED)
                        ctx.sendBroadcast(intent)
                    }
                }
                response
            }
            .addInterceptor(
                HttpLoggingInterceptor().apply {
                    level = HttpLoggingInterceptor.Level.BODY
                }
            )
            .build()

        return Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
