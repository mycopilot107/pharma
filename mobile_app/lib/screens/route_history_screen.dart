import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:intl/intl.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';

import '../core/theme/app_theme.dart';
import '../providers/app_state.dart';

class RouteHistoryScreen extends StatefulWidget {
  const RouteHistoryScreen({super.key});

  @override
  State<RouteHistoryScreen> createState() => _RouteHistoryScreenState();
}

class _RouteHistoryScreenState extends State<RouteHistoryScreen> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  DateTime _date = DateTime.now();

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_date);
      final data = await context.read<AppState>().tracking.routeHistory(date: dateStr);
      if (mounted) setState(() => _data = data);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final pings = (_data?['pings'] as List?) ?? [];
    final analytics = _data?['analytics'] as Map<String, dynamic>?;
    final stops = (analytics?['stops'] as List?) ?? [];
    final visits = (_data?['visits'] as List?) ?? [];

    final points = pings
        .map((p) => LatLng((p['lat'] as num).toDouble(), (p['lng'] as num).toDouble()))
        .toList();

    LatLng center = const LatLng(20.5937, 78.9629);
    if (points.isNotEmpty) {
      center = points.first;
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Route history'),
        actions: [
          IconButton(icon: const Icon(Icons.calendar_today), onPressed: () async {
            final picked = await showDatePicker(
              context: context,
              initialDate: _date,
              firstDate: DateTime.now().subtract(const Duration(days: 90)),
              lastDate: DateTime.now(),
            );
            if (picked != null) {
              setState(() => _date = picked);
              _load();
            }
          }),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                if (analytics != null)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    color: const Color(0xFFF0FDFA),
                    child: Wrap(
                      spacing: 16,
                      runSpacing: 8,
                      children: [
                        _chip('${analytics['distance_km']} km', Icons.route),
                        _chip('${analytics['ping_count']} pings', Icons.gps_fixed),
                        _chip('${stops.length} stops', Icons.pause_circle),
                        _chip('${analytics['moving_minutes']} min moving', Icons.directions_walk),
                      ],
                    ),
                  ),
                Expanded(
                  child: points.isEmpty
                      ? const Center(child: Text('No GPS trail for this date'))
                      : FlutterMap(
                          options: MapOptions(
                            initialCenter: center,
                            initialZoom: 14,
                          ),
                          children: [
                            TileLayer(
                              urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                              userAgentPackageName: 'com.medrep.fleet',
                            ),
                            if (points.length > 1)
                              PolylineLayer(
                                polylines: [
                                  Polyline(points: points, color: AppTheme.primary, strokeWidth: 4),
                                ],
                              ),
                            MarkerLayer(
                              markers: [
                                if (points.isNotEmpty)
                                  Marker(
                                    point: points.first,
                                    width: 40,
                                    height: 40,
                                    child: const Icon(Icons.play_circle, color: Colors.green, size: 32),
                                  ),
                                if (points.length > 1)
                                  Marker(
                                    point: points.last,
                                    width: 40,
                                    height: 40,
                                    child: const Icon(Icons.stop_circle, color: Colors.red, size: 32),
                                  ),
                                ...stops.map((s) => Marker(
                                      point: LatLng(
                                        (s['lat'] as num).toDouble(),
                                        (s['lng'] as num).toDouble(),
                                      ),
                                      width: 36,
                                      height: 36,
                                      child: const Icon(Icons.pause, color: Colors.orange, size: 28),
                                    )),
                                ...visits.where((v) => v['check_in'] != null).map((v) {
                                  final c = v['check_in'] as Map;
                                  return Marker(
                                    point: LatLng(
                                      (c['lat'] as num).toDouble(),
                                      (c['lng'] as num).toDouble(),
                                    ),
                                    width: 36,
                                    height: 36,
                                    child: Icon(
                                      Icons.place,
                                      color: (v['validation']?['risk_score'] ?? 0) >= 50
                                          ? Colors.red
                                          : AppTheme.primary,
                                      size: 28,
                                    ),
                                  );
                                }),
                              ],
                            ),
                          ],
                        ),
                ),
              ],
            ),
    );
  }

  Widget _chip(String label, IconData icon) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 16, color: AppTheme.primary),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
      ],
    );
  }
}
