<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\LeaveController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\TargetController;
use App\Http\Controllers\Api\V1\TrackingController;
use App\Http\Controllers\Api\V1\VisitController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'api.representative'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/visits', [VisitController::class, 'index']);
        Route::post('/visits', [VisitController::class, 'store']);
        Route::get('/visits/{visit}', [VisitController::class, 'show']);
        Route::post('/visits/{visit}/check-in', [VisitController::class, 'checkIn']);
        Route::post('/visits/{visit}/check-out', [VisitController::class, 'checkOut']);
        Route::patch('/visits/{visit}/notes', [VisitController::class, 'updateNotes']);
        Route::post('/visits/{visit}/photos', [VisitController::class, 'uploadPhotos']);

        Route::get('/customers', [CustomerController::class, 'index']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);

        Route::get('/targets', [TargetController::class, 'index']);
        Route::get('/targets/{target}', [TargetController::class, 'show']);

        Route::get('/tracking/config', [TrackingController::class, 'trackingConfig']);
        Route::get('/tracking/status', [TrackingController::class, 'status']);
        Route::post('/tracking/ping', [TrackingController::class, 'ping']);
        Route::post('/tracking/pings/batch', [TrackingController::class, 'pingBatch']);
        Route::get('/tracking/geofences', [TrackingController::class, 'geofences']);
        Route::post('/tracking/geofence-event', [TrackingController::class, 'geofenceEvent']);
        Route::get('/tracking/route-history', [TrackingController::class, 'routeHistory']);
        Route::get('/tracking/movement-log', [TrackingController::class, 'movementLog']);
        Route::post('/attendance/clock-in', [TrackingController::class, 'clockIn']);
        Route::post('/attendance/clock-out', [TrackingController::class, 'clockOut']);

        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::delete('/orders/{order}', [OrderController::class, 'cancel']);

        Route::get('/leaves/balance', [LeaveController::class, 'balance']);
        Route::get('/leaves', [LeaveController::class, 'index']);
        Route::post('/leaves', [LeaveController::class, 'store']);
        Route::get('/leaves/{leave}', [LeaveController::class, 'show']);
        Route::delete('/leaves/{leave}', [LeaveController::class, 'cancel']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/count', [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::delete('/notifications/{notification}', [NotificationController::class, 'dismiss']);
    });
});
