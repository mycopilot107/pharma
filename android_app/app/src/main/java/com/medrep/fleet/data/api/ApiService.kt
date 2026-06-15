package com.medrep.fleet.data.api

import com.medrep.fleet.data.model.*
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // ── Auth ─────────────────────────────────────────────────────────────────

    @POST("login")
    suspend fun login(@Body body: Map<String, String>): Response<LoginResponse>

    @POST("logout")
    suspend fun logout(): Response<MessageResponse>

    // ── Dashboard ─────────────────────────────────────────────────────────────

    @GET("dashboard")
    suspend fun getDashboard(): Response<DashboardData>

    // ── Attendance ────────────────────────────────────────────────────────────

    @POST("attendance/clock-in")
    suspend fun clockIn(@Body body: Map<String, @JvmSuppressWildcards Any?>): Response<AttendanceResponse>

    @POST("attendance/clock-out")
    suspend fun clockOut(@Body body: Map<String, @JvmSuppressWildcards Any?>): Response<AttendanceResponse>

    @GET("attendance/today")
    suspend fun getTodayAttendance(): Response<AttendanceResponse>

    // ── GPS Tracking ──────────────────────────────────────────────────────────

    @POST("tracking/ping")
    suspend fun trackingPing(@Body body: Map<String, @JvmSuppressWildcards Any?>): Response<MessageResponse>

    // ── Visits ────────────────────────────────────────────────────────────────

    @GET("visits")
    suspend fun getVisits(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 5,
        @Query("date") date: String? = null,
        @Query("search") search: String? = null
    ): Response<PaginatedResponse<Visit>>

    @GET("visits/{id}")
    suspend fun getVisit(@Path("id") id: Int): Response<Visit>

    @POST("visits")
    suspend fun checkIn(@Body body: Map<String, @JvmSuppressWildcards Any?>): Response<Visit>

    @POST("visits/{id}/check-out")
    suspend fun checkOut(
        @Path("id") id: Int,
        @Body body: Map<String, @JvmSuppressWildcards Any?>
    ): Response<Visit>

    @Multipart
    @POST("visits/{id}/photos")
    suspend fun uploadVisitPhoto(
        @Path("id") id: Int,
        @Part photo: MultipartBody.Part
    ): Response<Visit>

    // ── Customers ─────────────────────────────────────────────────────────────

    @GET("customers")
    suspend fun getCustomers(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 100,
        @Query("type") type: String? = null,
        @Query("search") search: String? = null
    ): Response<PaginatedResponse<Customer>>

    @GET("customers/{id}")
    suspend fun getCustomer(@Path("id") id: Int): Response<Customer>

    // ── Products ──────────────────────────────────────────────────────────────

    @GET("products")
    suspend fun getProducts(
        @Query("search") search: String? = null
    ): Response<ListResponse<Product>>

    // ── Expenses ─────────────────────────────────────────────────────────────

    @GET("expenses")
    suspend fun getExpenses(
        @Query("page") page: Int = 1
    ): Response<PaginatedResponse<Expense>>

    @POST("expenses")
    suspend fun createExpense(@Body body: Map<String, @JvmSuppressWildcards Any?>): Response<Expense>

    @Multipart
    @POST("expenses/{id}/receipt")
    suspend fun uploadExpenseReceipt(
        @Path("id") id: Int,
        @Part receipt: MultipartBody.Part
    ): Response<Expense>

    // ── Leaves ────────────────────────────────────────────────────────────────

    @GET("leaves")
    suspend fun getLeaves(
        @Query("page") page: Int = 1
    ): Response<PaginatedResponse<Leave>>

    @POST("leaves")
    suspend fun applyLeave(@Body body: Map<String, @JvmSuppressWildcards Any?>): Response<Leave>

    // ── Orders ────────────────────────────────────────────────────────────────

    @GET("orders")
    suspend fun getOrders(
        @Query("page") page: Int = 1,
        @Query("search") search: String? = null
    ): Response<PaginatedResponse<Order>>

    @POST("orders")
    suspend fun createOrder(@Body body: Map<String, @JvmSuppressWildcards Any?>): Response<Order>

    // ── Notifications ─────────────────────────────────────────────────────────

    @GET("notifications")
    suspend fun getNotifications(
        @Query("page") page: Int = 1
    ): Response<PaginatedResponse<AppNotification>>

    @POST("notifications/{id}/read")
    suspend fun markNotificationRead(@Path("id") id: Int): Response<MessageResponse>

    // ── Tour Plan ─────────────────────────────────────────────────────────────

    @GET("tour-plans")
    suspend fun getTourPlans(
        @Query("week_start") weekStart: String? = null
    ): Response<ListResponse<TourPlan>>

    @POST("tour-plans")
    suspend fun createTourPlan(@Body body: Map<String, @JvmSuppressWildcards Any?>): Response<TourPlan>

    @GET("tour-plans/{id}")
    suspend fun getTourPlan(@Path("id") id: Int): Response<TourPlan>

    @PUT("tour-plans/{id}")
    suspend fun updateTourPlan(
        @Path("id") id: Int,
        @Body body: Map<String, @JvmSuppressWildcards Any?>
    ): Response<TourPlan>

    @POST("tour-plans/{id}/submit")
    suspend fun submitTourPlan(@Path("id") id: Int): Response<MessageResponse>

    @DELETE("tour-plans/{id}")
    suspend fun deleteTourPlan(@Path("id") id: Int): Response<MessageResponse>

    // ── Route History ─────────────────────────────────────────────────────────

    @GET("tracking/route-history")
    suspend fun getRouteHistory(
        @Query("date") date: String
    ): Response<ListResponse<TrackingPoint>>

    // Live track from Redis — returns points for today streamed from Redis sorted set
    @GET("tracking/live")
    suspend fun getLiveTrack(
        @Query("user_id") userId: Int? = null
    ): Response<ListResponse<TrackingPoint>>
}
