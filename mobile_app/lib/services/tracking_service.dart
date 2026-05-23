import '../core/api/api_client.dart';
import '../models/user.dart';

class TrackingService {
  TrackingService(this._api);

  final ApiClient _api;

  Future<Map<String, dynamic>> status() async {
    return await _api.get('/tracking/status') as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> config() async {
    return await _api.get('/tracking/config') as Map<String, dynamic>;
  }

  Future<User> clockIn(double lat, double lng) async {
    final data = await _api.post('/attendance/clock-in', body: {
      'latitude': lat,
      'longitude': lng,
    });
    return User.fromJson(data['user'] as Map<String, dynamic>);
  }

  Future<User> clockOut(double lat, double lng) async {
    final data = await _api.post('/attendance/clock-out', body: {
      'latitude': lat,
      'longitude': lng,
    });
    return User.fromJson(data['user'] as Map<String, dynamic>);
  }

  Future<void> ping({
    required double lat,
    required double lng,
    double? accuracy,
    double? speed,
    double? heading,
    double? altitude,
    int? batteryPercent,
    bool isBackground = false,
  }) async {
    await _api.post('/tracking/ping', body: {
      'latitude': lat,
      'longitude': lng,
      if (accuracy != null) 'accuracy': accuracy,
      if (speed != null) 'speed': speed,
      if (heading != null) 'heading': heading,
      if (altitude != null) 'altitude': altitude,
      if (batteryPercent != null) 'battery_percent': batteryPercent,
      'is_background': isBackground,
    });
  }

  Future<List<Map<String, dynamic>>> geofences() async {
    final data = await _api.get('/tracking/geofences');
    return List<Map<String, dynamic>>.from(data['zones'] as List);
  }

  Future<Map<String, dynamic>> geofenceEvent({
    required int customerId,
    required String eventType,
    required double lat,
    required double lng,
  }) async {
    return await _api.post('/tracking/geofence-event', body: {
      'customer_id': customerId,
      'event_type': eventType,
      'latitude': lat,
      'longitude': lng,
    }) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> routeHistory({String? date}) async {
    return await _api.get('/tracking/route-history', query: date != null ? {'date': date} : null)
        as Map<String, dynamic>;
  }

  Future<List<Map<String, dynamic>>> movementLog({String? date}) async {
    final data = await _api.get('/tracking/movement-log', query: date != null ? {'date': date} : null);
    return List<Map<String, dynamic>>.from(data['logs'] as List);
  }
}
