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
    @SerializedName("today_visits") val todayVisits: Int = 0,
    @SerializedName("today_distance_km") val todayDistanceKm: Double = 0.0,
    @SerializedName("month_visits") val monthVisits: Int = 0,
    @SerializedName("pending_expenses") val pendingExpenses: Int = 0,
    @SerializedName("pending_orders") val pendingOrders: Int = 0,
    @SerializedName("unread_notifications") val unreadNotifications: Int = 0,
    @SerializedName("clocked_in") val clockedIn: Boolean = false,
    @SerializedName("clock_in_time") val clockInTime: String? = null,
    @SerializedName("recent_visits") val recentVisits: List<Visit> = emptyList(),
    @SerializedName("stats") val stats: DashboardStats = DashboardStats(),
    @SerializedName("attendance") val attendance: DashboardAttendance = DashboardAttendance(),
    @SerializedName("tracking_active") val trackingActive: Boolean = false,
)

data class DashboardStats(
    val planned: Int = 0,
    @SerializedName("in_progress") val inProgress: Int = 0,
    val completed: Int = 0
)

data class DashboardAttendance(
    val active: Boolean = false,
    @SerializedName("clock_in") val clockIn: String? = null,
    @SerializedName("clock_out") val clockOut: String? = null
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
    @SerializedName("customer_id")        val customerId: Int?,
    val customer: Customer?,
    @SerializedName("place_name")         val placeName: String?,
    @SerializedName("checked_in_at")      val checkInTime: String?,
    @SerializedName("checked_out_at")     val checkOutTime: String?,
    @SerializedName("check_in_latitude")  val checkInLat: Double?,
    @SerializedName("check_in_longitude") val checkInLng: Double?,
    @SerializedName("check_out_latitude") val checkOutLat: Double?,
    @SerializedName("check_out_longitude")val checkOutLng: Double?,
    @SerializedName("duration_minutes")   val durationMinutes: Int?,
    val notes: String?,
    @SerializedName("products_promoted")  val productsPromoted: List<String>?,
    @SerializedName("samples_given")      val samplesGiven: Int?,
    @SerializedName("follow_up_date")     val followUpDate: String?,
    @SerializedName("photo_url")          val photoUrl: String?,
    @SerializedName("is_mock_detected")   val isMockDetected: Boolean = false,
    val status: String?,
    @SerializedName("visit_type")         val visitType: String?,
    @SerializedName("created_at")         val createdAt: String?,
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
    val id: Int? = null,
    val latitude: Double,
    val longitude: Double,
    val accuracy: Float? = null,
    val speed: Float? = null,
    val heading: Float? = null,
    @SerializedName("is_mock") val isMock: Boolean = false,
    @SerializedName("created_at") val createdAt: String? = null
)
