import 'dart:math';

/// Client-side geofence enter/exit detection (server validates).
class GeofenceMonitor {
  GeofenceMonitor({
    required this.zones,
    required this.onEnter,
    required this.onExit,
  });

  final List<Map<String, dynamic>> zones;
  final void Function(int customerId, String name) onEnter;
  final void Function(int customerId, String name) onExit;

  final Map<int, bool> _inside = {};

  void evaluate(double lat, double lng) {
    for (final zone in zones) {
      if (zone['auto_checkin'] != true) continue;

      final id = zone['customer_id'] as int;
      final name = zone['name'] as String;
      final zLat = (zone['latitude'] as num).toDouble();
      final zLng = (zone['longitude'] as num).toDouble();
      final radius = (zone['radius_meters'] as num?)?.toInt() ?? 150;

      final inside = _distanceM(lat, lng, zLat, zLng) <= radius;
      final wasInside = _inside[id] ?? false;

      if (inside && !wasInside) {
        _inside[id] = true;
        onEnter(id, name);
      } else if (!inside && wasInside) {
        _inside[id] = false;
        onExit(id, name);
      } else if (inside) {
        _inside[id] = true;
      }
    }
  }

  double _distanceM(double lat1, double lng1, double lat2, double lng2) {
    const r = 6371000.0;
    final dLat = _rad(lat2 - lat1);
    final dLng = _rad(lng2 - lng1);
    final a = sin(dLat / 2) * sin(dLat / 2) +
        cos(_rad(lat1)) * cos(_rad(lat2)) * sin(dLng / 2) * sin(dLng / 2);
    return 2 * r * asin(min(1, sqrt(a)));
  }

  double _rad(double deg) => deg * pi / 180;
}
