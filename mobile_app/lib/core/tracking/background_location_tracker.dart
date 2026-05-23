import 'dart:async';
import 'package:geolocator/geolocator.dart';

import '../../services/tracking_service.dart';
import 'geofence_monitor.dart';

/// Foreground + background GPS pings while on duty.
class BackgroundLocationTracker {
  BackgroundLocationTracker({
    required this.trackingService,
    required this.onGeofenceAction,
  });

  final TrackingService trackingService;
  final void Function(String message) onGeofenceAction;

  StreamSubscription<Position>? _positionSub;
  Timer? _fallbackTimer;
  GeofenceMonitor? _geofenceMonitor;
  int _pingIntervalSec = 30;
  int _backgroundIntervalSec = 60;
  bool _isBackground = false;

  bool get isRunning => _positionSub != null;

  Future<void> start() async {
    if (isRunning) return;

    final config = await trackingService.config();
    _pingIntervalSec = (config['ping_interval_seconds'] as num?)?.toInt() ?? 30;
    _backgroundIntervalSec =
        (config['background_ping_interval_seconds'] as num?)?.toInt() ?? 60;

    final zones = await trackingService.geofences();
    _geofenceMonitor = GeofenceMonitor(
      zones: zones,
      onEnter: (id, name) => _fireGeofence(id, 'enter', name),
      onExit: (id, name) => _fireGeofence(id, 'exit', name),
    );

    const settings = LocationSettings(
      accuracy: LocationAccuracy.high,
      distanceFilter: 15,
    );

    _positionSub =
        Geolocator.getPositionStream(locationSettings: settings).listen(
      _onPosition,
      onError: (_) {},
    );

    _fallbackTimer = Timer.periodic(
      Duration(
          seconds: _isBackground ? _backgroundIntervalSec : _pingIntervalSec),
      (_) => _sendCurrentPing(background: _isBackground),
    );

    await _sendCurrentPing();
  }

  void setBackground(bool value) {
    _isBackground = value;
    _fallbackTimer?.cancel();
    _fallbackTimer = Timer.periodic(
      Duration(
          seconds: _isBackground ? _backgroundIntervalSec : _pingIntervalSec),
      (_) => _sendCurrentPing(background: _isBackground),
    );
  }

  Future<void> stop() async {
    await _positionSub?.cancel();
    _positionSub = null;
    _fallbackTimer?.cancel();
    _fallbackTimer = null;
    _geofenceMonitor = null;
  }

  void _onPosition(Position pos) {
    _geofenceMonitor?.evaluate(pos.latitude, pos.longitude);
    _sendPingFromPosition(pos, background: _isBackground);
  }

  Future<void> _sendCurrentPing({bool background = false}) async {
    try {
      final pos = await Geolocator.getCurrentPosition(
        locationSettings:
            const LocationSettings(accuracy: LocationAccuracy.medium),
      );
      await _sendPingFromPosition(pos, background: background);
    } catch (_) {}
  }

  Future<void> _sendPingFromPosition(Position pos,
      {bool background = false}) async {
    try {
      await trackingService.ping(
        lat: pos.latitude,
        lng: pos.longitude,
        accuracy: pos.accuracy,
        speed: pos.speed >= 0 ? pos.speed : null,
        heading: pos.heading >= 0 ? pos.heading : null,
        altitude: pos.altitude,
        isBackground: background,
      );
    } catch (_) {}
  }

  Future<void> _fireGeofence(int customerId, String type, String name) async {
    try {
      final pos = await Geolocator.getCurrentPosition();
      final result = await trackingService.geofenceEvent(
        customerId: customerId,
        eventType: type,
        lat: pos.latitude,
        lng: pos.longitude,
      );
      if (result['ok'] == true && result['action'] != null) {
        onGeofenceAction(
            '${type == 'enter' ? 'Entered' : 'Left'} $name — ${result['action']}');
      }
    } catch (_) {}
  }
}
