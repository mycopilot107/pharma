package com.medrep.fleet.data.model

import com.google.gson.annotations.SerializedName

// ── Auth ─────────────────────────────────────────────────────────────────────

data class LoginResponse(
    val token: String,
    val user: User
)

data class MessageResponse(
    val message: String
)

// ── Pagination wrappers ───────────────────────────────────────────────────────

data class PaginatedResponse<T>(
    val data: List<T>,
    val current_page: Int,
    val last_page: Int,
    val total: Int
)

data class ListResponse<T>(
    val data: List<T>
)

// ── User ─────────────────────────────────────────────────────────────────────

data class User(
    val id: Int,
    val name: String,
    val email: String,
    val role: String,
    val phone: String?,
    val avatar: String?,
    @SerializedName("company_name") val companyName: String?
)

// ── Dashboard ─────────────────────────────────────────────────────────────────

data class DashboardData(
    @SerializedName("today_visits") val todayVisits: Int,
    @SerializedName("today_distance_km") val todayDistanceKm: Double,
    @SerializedName("month_visits") val monthVisits: Int,
    @SerializedName("pending_expenses") val pendingExpenses: Int,
    @SerializedName("pending_orders") val pendingOrders: Int,
    @SerializedName("unread_notifications") val unreadNotifications: Int,
    @SerializedName("clocked_in") val clockedIn: Boolean,
    @SerializedName("clock_in_time") val clockInTime: String?,
    @SerializedName("recent_visits") val recentVisits: List<Visit>
)

// ── Attendance ────────────────────────────────────────────────────────────────

data class AttendanceResponse(
    val id: Int?,
    @SerializedName("clock_in") val clockIn: String?,
    @SerializedName("clock_out") val clockOut: String?,
    @SerializedName("total_distance_km") val totalDistanceKm: Double?,
    val status: String?
)

// ── Visit ─────────────────────────────────────────────────────────────────────

data class Visit(
    val id: Int,
    @SerializedName("customer_id") val customerId: Int,
    val customer: Customer?,
    @SerializedName("check_in_time") val checkInTime: String,
    @SerializedName("check_out_time") val checkOutTime: String?,
    @SerializedName("check_in_lat") val checkInLat: Double,
    @SerializedName("check_in_lng") val checkInLng: Double,
    @SerializedName("check_out_lat") val checkOutLat: Double?,
    @SerializedName("check_out_lng") val checkOutLng: Double?,
    val notes: String?,
    @SerializedName("photo_url") val photoUrl: String?,
    @SerializedName("signature_url") val signatureUrl: String?,
    @SerializedName("products_promoted") val productsPromoted: List<String>?,
    @SerializedName("samples_given") val samplesGiven: Int?,
    @SerializedName("follow_up_date") val followUpDate: String?,
    @SerializedName("is_mock_detected") val isMockDetected: Boolean,
    val status: String   // "ongoing" | "completed"
)

// ── Customer ─────────────────────────────────────────────────────────────────

data class Customer(
    val id: Int,
    val name: String,
    val type: String?,   // "doctor" | "chemist" | "hospital" | etc.
    val specialty: String?,
    val address: String?,
    val city: String?,
    val phone: String?,
    val email: String?,
    val lat: Double?,
    val lng: Double?,
    @SerializedName("visit_count") val visitCount: Int?
)

// ── Product ───────────────────────────────────────────────────────────────────

data class Product(
    val id: Int,
    val name: String,
    val category: String?,
    val description: String?
)

// ── Expense ───────────────────────────────────────────────────────────────────

data class Expense(
    val id: Int,
    val date: String,
    val category: String,
    val amount: Double,
    val description: String?,
    @SerializedName("receipt_url") val receiptUrl: String?,
    val status: String   // "pending" | "approved" | "rejected"
)

// ── Leave ─────────────────────────────────────────────────────────────────────

data class Leave(
    val id: Int,
    @SerializedName("start_date") val startDate: String,
    @SerializedName("end_date") val endDate: String,
    val type: String,
    val reason: String?,
    val status: String   // "pending" | "approved" | "rejected"
)

// ── Order ─────────────────────────────────────────────────────────────────────

data class Order(
    val id: Int,
    @SerializedName("customer_id") val customerId: Int,
    val customer: Customer?,
    val date: String,
    val total: Double,
    val status: String,
    val items: List<OrderItem>?
)

data class OrderItem(
    val id: Int,
    @SerializedName("product_id") val productId: Int,
    val product: Product?,
    val quantity: Int,
    val price: Double
)

// ── Notification ─────────────────────────────────────────────────────────────

data class AppNotification(
    val id: Int,
    val title: String,
    val body: String,
    val type: String?,
    @SerializedName("read_at") val readAt: String?,
    @SerializedName("created_at") val createdAt: String
)

// ── Tour Plan ─────────────────────────────────────────────────────────────────

data class TourPlanEntry(
    val id: Int,
    val date: String,
    @SerializedName("customer_id") val customerId: Int,
    val customer: Customer?,
    val status: String   // "planned" | "done" | "missed"
)

// ── Route/Tracking ────────────────────────────────────────────────────────────

data class TrackingPoint(
    val id: Int,
    val latitude: Double,
    val longitude: Double,
    val accuracy: Float?,
    val speed: Float?,
    val heading: Float?,
    @SerializedName("is_mock") val isMock: Boolean,
    @SerializedName("created_at") val createdAt: String
)
