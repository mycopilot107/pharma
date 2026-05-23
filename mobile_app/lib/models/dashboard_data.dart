import 'app_notification.dart';
import 'visit.dart';

class DashboardData {
  DashboardData({
    required this.stats,
    this.activeVisit,
    required this.todayVisits,
    required this.trackingActive,
    required this.attendanceActive,
    required this.unreadNotifications,
    required this.reminders,
    this.targetStats,
  });

  final Map<String, int> stats;
  final Visit? activeVisit;
  final List<Visit> todayVisits;
  final bool trackingActive;
  final bool attendanceActive;
  final int unreadNotifications;
  final List<AppNotification> reminders;
  final Map<String, dynamic>? targetStats;

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    final statsRaw = json['stats'] as Map<String, dynamic>? ?? {};
    final visitsRaw = json['today_visits'] as List? ?? [];
    final remindersRaw = json['reminders'] as List? ?? [];
    final attendance = json['attendance'] as Map<String, dynamic>? ?? {};

    Visit? active;
    if (json['active_visit'] != null) {
      active = Visit.fromJson(json['active_visit'] as Map<String, dynamic>);
    }

    return DashboardData(
      stats: statsRaw.map((k, v) => MapEntry(k, (v as num).toInt())),
      activeVisit: active,
      todayVisits: visitsRaw
          .map((e) => Visit.fromJson(e as Map<String, dynamic>))
          .toList(),
      trackingActive: json['tracking_active'] as bool? ?? false,
      attendanceActive: attendance['active'] as bool? ?? false,
      unreadNotifications: json['unread_notifications'] as int? ?? 0,
      reminders: remindersRaw
          .map((e) => AppNotification.fromJson(e as Map<String, dynamic>))
          .toList(),
      targetStats: json['target_stats'] as Map<String, dynamic>?,
    );
  }
}
