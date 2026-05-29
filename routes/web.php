<?php

use App\Http\Controllers\Admin\AiReportController as AdminAiReportController;
use App\Http\Controllers\Admin\TrackingController as AdminTrackingController;
use App\Http\Controllers\Admin\ExpenseController as AdminExpenseController;
use App\Http\Controllers\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\Admin\LeaveReportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderReportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerCrmController as AdminCustomerCrmController;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\VisitReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyRegistrationController;
use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Mr\AiReportController as MrAiReportController;
use App\Http\Controllers\Mr\CustomerController as MrCustomerController;
use App\Http\Controllers\Mr\CustomerCrmController as MrCustomerCrmController;
use App\Http\Controllers\Mr\DailyRouteController;
use App\Http\Controllers\Mr\ExpenseController as MrExpenseController;
use App\Http\Controllers\Mr\LeaveController as MrLeaveController;
use App\Http\Controllers\Mr\OrderController as MrOrderController;
use App\Http\Controllers\Mr\NotificationController as MrNotificationController;
use App\Http\Controllers\Mr\MrDashboardController;
use App\Http\Controllers\Mr\TargetController as MrTargetController;
use App\Http\Controllers\Mr\TrackingController as MrTrackingController;
use App\Http\Controllers\Mr\VisitController as MrVisitController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SuperAdmin\CompanyController as SuperAdminCompanyController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about',    [PageController::class, 'about'])->name('about');
Route::get('/features', [PageController::class, 'features'])->name('features');
Route::get('/pricing',  [PageController::class, 'pricing'])->name('pricing');
Route::get('/app',      [PageController::class, 'appDownload'])->name('app.download');
Route::get('/faq',      [PageController::class, 'faq'])->name('faq');
Route::get('/terms',    [PageController::class, 'terms'])->name('terms');
Route::get('/policy',   [PageController::class, 'policy'])->name('policy');
Route::get('/contact',  [PageController::class, 'contact'])->name('contact');

Route::get('/register-company', [CompanyRegistrationController::class, 'create'])->name('companies.register');
Route::post('/register-company', [CompanyRegistrationController::class, 'store'])->name('companies.register.store');
Route::get('/api/plans/{plan}/price', [CompanyRegistrationController::class, 'planPrice'])->name('plans.price');

Route::get('/payment/{company}/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::post('/payment/verify', [PaymentController::class, 'verify'])->name('payment.verify');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'super.admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/companies', [SuperAdminCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{company}', [SuperAdminCompanyController::class, 'show'])->name('companies.show');
    Route::put('/companies/{company}', [SuperAdminCompanyController::class, 'update'])->name('companies.update');
    Route::post('/companies/{company}/activate', [SuperAdminCompanyController::class, 'activate'])->name('companies.activate');
    Route::post('/companies/{company}/deactivate', [SuperAdminCompanyController::class, 'deactivate'])->name('companies.deactivate');
    Route::get('/settings', [SuperAdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SuperAdminSettingsController::class, 'update'])->name('settings.update');
});

Route::middleware(['auth', 'company.admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [CompanyUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [CompanyUserController::class, 'create'])->name('users.create');
    Route::post('/users', [CompanyUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [CompanyUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [CompanyUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [CompanyUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [CompanyUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/admin/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('/admin/customers/create', [CustomerController::class, 'create'])->name('admin.customers.create');
    Route::post('/admin/customers', [CustomerController::class, 'store'])->name('admin.customers.store');
    Route::get('/admin/customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
    Route::get('/admin/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('admin.customers.edit');
    Route::put('/admin/customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');
    Route::delete('/admin/customers/{customer}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy');

    Route::post('/admin/customers/{customer}/follow-ups', [AdminCustomerCrmController::class, 'storeFollowUp'])->name('admin.customers.follow-ups.store');
    Route::patch('/admin/follow-ups/{followUp}/complete', [AdminCustomerCrmController::class, 'completeFollowUp'])->name('admin.follow-ups.complete');
    Route::post('/admin/customers/{customer}/prescriptions', [AdminCustomerCrmController::class, 'storePrescription'])->name('admin.customers.prescriptions.store');
    Route::post('/admin/customers/{customer}/purchases', [AdminCustomerCrmController::class, 'storePurchase'])->name('admin.customers.purchases.store');

    Route::get('/admin/visits', [VisitReportController::class, 'index'])->name('admin.visits.index');
    Route::get('/admin/visits/{visit}', [VisitReportController::class, 'show'])->name('admin.visits.show');

    Route::get('/admin/targets', [TargetController::class, 'index'])->name('admin.targets.index');
    Route::get('/admin/targets/create', [TargetController::class, 'create'])->name('admin.targets.create');
    Route::post('/admin/targets', [TargetController::class, 'store'])->name('admin.targets.store');
    Route::get('/admin/targets/{target}/edit', [TargetController::class, 'edit'])->name('admin.targets.edit');
    Route::put('/admin/targets/{target}', [TargetController::class, 'update'])->name('admin.targets.update');
    Route::delete('/admin/targets/{target}', [TargetController::class, 'destroy'])->name('admin.targets.destroy');
    Route::patch('/admin/targets/{target}/progress', [TargetController::class, 'updateProgress'])->name('admin.targets.progress');

    Route::get('/admin/ai-reports', [AdminAiReportController::class, 'index'])->name('admin.ai-reports.index');
    Route::post('/admin/ai-reports/generate', [AdminAiReportController::class, 'generate'])->name('admin.ai-reports.generate');
    Route::get('/admin/ai-reports/{aiReport}', [AdminAiReportController::class, 'show'])->name('admin.ai-reports.show');

    Route::get('/admin/tracking', [AdminTrackingController::class, 'index'])->name('admin.tracking.index');
    Route::get('/admin/tracking/live', [AdminTrackingController::class, 'liveData'])->name('admin.tracking.live');
    Route::get('/admin/tracking/reps/{user}/route', [AdminTrackingController::class, 'routeHistory'])->name('admin.tracking.route');
    Route::get('/admin/tracking/fraud-alerts', [AdminTrackingController::class, 'fraudAlerts'])->name('admin.tracking.fraud');

    Route::get('/admin/expenses', [AdminExpenseController::class, 'index'])->name('admin.expenses.index');
    Route::get('/admin/expenses/{expense}', [AdminExpenseController::class, 'show'])->name('admin.expenses.show');
    Route::post('/admin/expenses/{expense}/approve', [AdminExpenseController::class, 'approve'])->name('admin.expenses.approve');
    Route::post('/admin/expenses/{expense}/reject', [AdminExpenseController::class, 'reject'])->name('admin.expenses.reject');

    Route::get('/admin/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/admin/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/admin/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/admin/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

    Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/reports', [OrderReportController::class, 'index'])->name('admin.orders.reports');
    Route::get('/admin/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/admin/orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('admin.orders.confirm');
    Route::post('/admin/orders/{order}/deliver', [AdminOrderController::class, 'deliver'])->name('admin.orders.deliver');
    Route::post('/admin/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('admin.orders.cancel');

    Route::get('/admin/leaves', [AdminLeaveController::class, 'index'])->name('admin.leaves.index');
    Route::get('/admin/leaves/reports', [LeaveReportController::class, 'index'])->name('admin.leaves.reports');
    Route::get('/admin/leaves/{leave}', [AdminLeaveController::class, 'show'])->name('admin.leaves.show');
    Route::post('/admin/leaves/{leave}/approve', [AdminLeaveController::class, 'approve'])->name('admin.leaves.approve');
    Route::post('/admin/leaves/{leave}/reject', [AdminLeaveController::class, 'reject'])->name('admin.leaves.reject');

    Route::get('/admin/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/admin/notifications/count', [AdminNotificationController::class, 'unreadCount'])->name('admin.notifications.count');
    Route::patch('/admin/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('admin.notifications.read');
    Route::post('/admin/notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('admin.notifications.read-all');
    Route::delete('/admin/notifications/{notification}', [AdminNotificationController::class, 'dismiss'])->name('admin.notifications.dismiss');
});

Route::middleware(['auth', 'representative'])->prefix('mr')->name('mr.')->group(function () {
    Route::get('/dashboard', [MrDashboardController::class, 'index'])->name('dashboard');

    Route::get('/visits', [MrVisitController::class, 'index'])->name('visits.index');
    Route::get('/visits/create', [MrVisitController::class, 'create'])->name('visits.create');
    Route::post('/visits', [MrVisitController::class, 'store'])->name('visits.store');
    Route::get('/visits/{visit}', [MrVisitController::class, 'show'])->name('visits.show');
    Route::post('/visits/{visit}/check-in', [MrVisitController::class, 'checkIn'])->name('visits.check-in');
    Route::post('/visits/{visit}/check-out', [MrVisitController::class, 'checkOut'])->name('visits.check-out');
    Route::post('/visits/{visit}/notes', [MrVisitController::class, 'updateNotes'])->name('visits.notes');
    Route::post('/visits/{visit}/photos', [MrVisitController::class, 'uploadPhotos'])->name('visits.photos');
    Route::post('/visits/{visit}/ai-summary', [MrVisitController::class, 'generateSummary'])->name('visits.ai-summary');

    Route::get('/ai-reports', [MrAiReportController::class, 'index'])->name('ai-reports.index');
    Route::post('/ai-reports/generate', [MrAiReportController::class, 'generate'])->name('ai-reports.generate');
    Route::get('/ai-reports/{aiReport}', [MrAiReportController::class, 'show'])->name('ai-reports.show');

    Route::get('/routes', [DailyRouteController::class, 'index'])->name('routes.index');
    Route::post('/routes', [DailyRouteController::class, 'store'])->name('routes.store');

    Route::get('/customers', [MrCustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [MrCustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [MrCustomerController::class, 'show'])->name('customers.show');

    Route::post('/customers/{customer}/follow-ups', [MrCustomerCrmController::class, 'storeFollowUp'])->name('customers.follow-ups.store');
    Route::patch('/follow-ups/{followUp}/complete', [MrCustomerCrmController::class, 'completeFollowUp'])->name('follow-ups.complete');
    Route::post('/customers/{customer}/prescriptions', [MrCustomerCrmController::class, 'storePrescription'])->name('customers.prescriptions.store');
    Route::post('/customers/{customer}/purchases', [MrCustomerCrmController::class, 'storePurchase'])->name('customers.purchases.store');

    Route::get('/targets', [MrTargetController::class, 'index'])->name('targets.index');
    Route::get('/targets/{target}', [MrTargetController::class, 'show'])->name('targets.show');

    Route::post('/tracking/ping', [MrTrackingController::class, 'ping'])->name('tracking.ping');
    Route::post('/attendance/clock-in', [MrTrackingController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [MrTrackingController::class, 'clockOut'])->name('attendance.clock-out');
    Route::get('/tracking/status', [MrTrackingController::class, 'status'])->name('tracking.status');

    Route::get('/expenses', [MrExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [MrExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [MrExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}', [MrExpenseController::class, 'show'])->name('expenses.show');
    Route::delete('/expenses/{expense}', [MrExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::get('/orders', [MrOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [MrOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [MrOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [MrOrderController::class, 'show'])->name('orders.show');
    Route::delete('/orders/{order}', [MrOrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/leaves', [MrLeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [MrLeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [MrLeaveController::class, 'store'])->name('leaves.store');
    Route::get('/leaves/{leave}', [MrLeaveController::class, 'show'])->name('leaves.show');
    Route::delete('/leaves/{leave}', [MrLeaveController::class, 'cancel'])->name('leaves.cancel');

    Route::get('/notifications', [MrNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [MrNotificationController::class, 'unreadCount'])->name('notifications.count');
    Route::patch('/notifications/{notification}/read', [MrNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [MrNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [MrNotificationController::class, 'dismiss'])->name('notifications.dismiss');
});
