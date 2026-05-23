import '../core/api/api_client.dart';
import '../models/app_notification.dart';

class NotificationService {
  NotificationService(this._api);

  final ApiClient _api;

  Future<List<AppNotification>> list({String? filter}) async {
    final query = filter != null ? {'filter': filter} : null;
    final data = await _api.get('/notifications', query: query);
    final items = (data['data'] as List?) ?? [];
    return items
        .map((e) => AppNotification.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<int> unreadCount() async {
    final data = await _api.get('/notifications/count');
    return (data['count'] as num?)?.toInt() ?? 0;
  }

  Future<void> markRead(int id) async {
    await _api.patch('/notifications/$id/read');
  }

  Future<void> markAllRead() async {
    await _api.post('/notifications/read-all');
  }

  Future<void> dismiss(int id) async {
    await _api.delete('/notifications/$id');
  }
}
