import 'dart:async';
import 'dart:io';
import 'package:geolocator/geolocator.dart';

import '../../services/tracking_service.dart';
import 'geofence_monitor.dart';

/// Foreground + background GPS pings while on duty.
/// Uses a proper Android foreground service so the OS never kills location
/// updates on Xiaomi / OPPO / Vivo devices.
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

  // Server-side configurable, sensible defaults
  int _pingIntervalSec = 10;
  int _backgroundIntervalSec = 30;

  bool _isBackground = false;

  // Rate-limit: never ping the server faster than once every 5 s
  static const int _minPingGapSec = 5;
  DateTime? _lastPingTime;

  bool get isRunning => _positionSub != null;

  Future<void> start() async {
    if (isRunning) return;

    // Fetch server config (gracefully fall back to defaults)
    try {
      final config = await trackingService.config();
      _pingIntervalSec =
          (config['ping_interval_seconds'] as num?)?.toInt() ?? 10;
      _backgroundIntervalSec =
          (config['background_ping_interval_seconds'] as num?)?.toInt() ?? 30;
    } catch (_) {}

    // Geofence zones
    try {
      final zones = await trackingService.geofences();
      _geofenceMonitor = GeofenceMonitor(
        zones: zones,
        onEnter: (id, name) => _fireGeofence(id, 'enter', name),
        onExit: (id, name) => _fireGeofence(id, 'exit', name),
      );
    } catch (_) {}

    // Platform-specific location settings
    final LocationSettings settings = _buildLocationSettings();

    _positionSub = Geolocator.getPositionStream(locationSettings: settings)
        .listen(_onPosition, onError: (_) {});

    // Fallback timer: ensures pings even when the stream pauses
    _startFallbackTimer();

    // Send an immediate ping so the server knows tracking started
    await _sendCurrentPing();
  }

  void setBackground(bool value) {
    _isBackground = value;
    _startFallbackTimer();
  }

  Future<void> stop() async {
    await _positionSub?.cancel();
    _positionSub = null;
    _fallbackTimer?.cancel();
    _fallbackTimer = null;
    _geofenceMonitor = null;
  }

  // ── internals ──────────────────────────────────────────────────────────────

  LocationSettings _buildLocationSettings() {
    if (Platform.isAndroid) {
      return AndroidSettings(
        accuracy: LocationAccuracy.best,
        distanceFilter: 10,
        forceLocationManager: false,
        intervalDuration: const Duration(seconds: 5),
        foregroundNotificationConfig: const ForegroundNotificationConfig(
          notificationTitle: 'Live GPS tracking active',
          notificationText: 'MedRep Fleet is recording your route.',
          enableWakeLock: true,
        ),
      );
    } else if (Platform.isIOS || Platform.isMacOS) {
      return AppleSettings(
        accuracy: LocationAccuracy.best,
        activityType: ActivityType.automotiveNavigation,
        distanceFilter: 10,
        pauseLocationUpdatesAutomatically: false,
        showBackgroundLocationIndicator: true,
      );
    } else {
      return const LocationSettings(
        accuracy: LocationAccuracy.best,
        distanceFilter: 10,
      );
    }
  }

  void _startFallbackTimer() {
    _fallbackTimer?.cancel();
    _fallbackTimer = Timer.periodic(
      Duration(seconds: _isBackground ? _backgroundIntervalSec : _pingIntervalSec),
      (_) => _sendCurrentPing(background: _isBackground),
    );
  }

  void _onPosition(Position pos) {
    _geofenceMonitor?.evaluate(pos.latitude, pos.longitude);

    // Rate-limit: skip if we pinged less than _minPingGapSec ago
    final now = DateTime.now();
    if (_lastPingTime != null &&
        now.difference(_lastPingTime!).inSeconds < _minPingGapSec) {
      return;
    }

    _sendPingFromPosition(pos, background: _isBackground);
  }

  Future<void> _sendCurrentPing({bool background = false}) async {
    try {
      final pos = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(accuracy: LocationAccuracy.best),
      );
      await _sendPingFromPosition(pos, background: background);
    } catch (_) {}
  }

  Future<void> _sendPingFromPosition(
    Position pos, {
    bool background = false,
  }) async {
    try {
      _lastPingTime = DateTime.now();
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
      final pos = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(accuracy: LocationAccuracy.best),
      );
      final result = await trackingService.geofenceEvent(
        customerId: customerId,
        eventType: type,
        lat: pos.latitude,
        lng: pos.longitude,
      );
      if (result['ok'] == true && result['action'] != null) {
        onGeofenceAction(
          '${type == 'enter' ? 'Entered' : 'Left'} $name — ${result['action']}',
        );
      }
    } catch (_) {}
  }
}
