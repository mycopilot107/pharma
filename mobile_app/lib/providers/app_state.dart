import 'package:flutter/foundation.dart';

import '../core/api/api_client.dart';
import '../core/location/location_helper.dart';
import '../core/storage/token_storage.dart';
import '../models/dashboard_data.dart';
import '../models/user.dart';
import '../services/auth_service.dart';
import '../services/customer_service.dart';
import '../services/dashboard_service.dart';
import '../services/expense_service.dart';
import '../services/notification_service.dart';
import '../core/tracking/background_location_tracker.dart';
import '../services/tracking_service.dart';
import '../services/visit_service.dart';

class AppState extends ChangeNotifier {
  AppState() {
    final storage = TokenStorage();
    final api = ApiClient(storage);
    auth = AuthService(api, storage);
    dashboard = DashboardService(api);
    visits = VisitService(api);
    customers = CustomerService(api);
    expenses = ExpenseService(api);
    notifications = NotificationService(api);
    tracking = TrackingService(api);
  }

  late final AuthService auth;
  late final DashboardService dashboard;
  late final VisitService visits;
  late final CustomerService customers;
  late final ExpenseService expenses;
  late final NotificationService notifications;
  late final TrackingService tracking;
  BackgroundLocationTracker? locationTracker;

  User? user;
  String? lastGeofenceMessage;
  DashboardData? dashboardData;
  bool loading = false;
  bool initialized = false;
  String? error;
  int unreadCount = 0;

  Future<void> bootstrap() async {
    if (!await auth.hasToken()) {
      initialized = true;
      notifyListeners();
      return;
    }
    loading = true;
    notifyListeners();
    try {
      user = await auth.me();
      await refreshDashboard();
      await ensureTrackingIfOnDuty();
    } catch (e) {
      await auth.logout();
      user = null;
    } finally {
      loading = false;
      initialized = true;
      notifyListeners();
    }
  }

  Future<bool> login(String email, String password) async {
    loading = true;
    error = null;
    notifyListeners();
    try {
      user = await auth.login(email, password);
      await refreshDashboard();
      return true;
    } catch (e) {
      error = e.toString().replaceFirst('ApiException: ', '');
      return false;
    } finally {
      loading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    await stopLocationTracking();
    await auth.logout();
    user = null;
    dashboardData = null;
    unreadCount = 0;
    notifyListeners();
  }

  Future<void> refreshDashboard() async {
    dashboardData = await dashboard.fetch();
    unreadCount = dashboardData?.unreadNotifications ?? 0;
    user = await auth.me();
    notifyListeners();
  }

  Future<void> refreshUnreadCount() async {
    unreadCount = await notifications.unreadCount();
    notifyListeners();
  }

  Future<void> clockIn() async {
    final pos = await LocationHelper.getCurrentPosition();
    user = await tracking.clockIn(pos.latitude, pos.longitude);
    await startLocationTracking();
    await refreshDashboard();
  }

  Future<void> clockOut() async {
    final pos = await LocationHelper.getCurrentPosition();
    await stopLocationTracking();
    user = await tracking.clockOut(pos.latitude, pos.longitude);
    await refreshDashboard();
  }

  Future<void> startLocationTracking() async {
    locationTracker ??= BackgroundLocationTracker(
      trackingService: tracking,
      onGeofenceAction: (msg) {
        lastGeofenceMessage = msg;
        notifyListeners();
      },
    );
    if (!locationTracker!.isRunning) {
      await locationTracker!.start();
    }
  }

  Future<void> stopLocationTracking() async {
    await locationTracker?.stop();
  }

  void setTrackingBackground(bool background) {
    locationTracker?.setBackground(background);
  }

  Future<void> ensureTrackingIfOnDuty() async {
    if (user?.trackingActive == true && locationTracker?.isRunning != true) {
      await startLocationTracking();
    }
  }
}
