import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../providers/app_state.dart';

class MovementLogScreen extends StatefulWidget {
  const MovementLogScreen({super.key});

  @override
  State<MovementLogScreen> createState() => _MovementLogScreenState();
}

class _MovementLogScreenState extends State<MovementLogScreen> {
  List<Map<String, dynamic>> _logs = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final logs = await context.read<AppState>().tracking.movementLog();
      if (mounted) setState(() => _logs = logs);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Movement log')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: _logs.isEmpty
                  ? const Center(child: Text('No movement logs today'))
                  : ListView.builder(
                      itemCount: _logs.length,
                      itemBuilder: (context, i) {
                        final log = _logs[i];
                        final at = DateTime.tryParse(log['recorded_at'] as String? ?? '');
                        return ListTile(
                          leading: Icon(
                            log['is_background'] == true ? Icons.cloud_upload : Icons.gps_fixed,
                            color: log['is_background'] == true ? Colors.orange : Colors.teal,
                          ),
                          title: Text(
                            '${(log['lat'] as num).toStringAsFixed(5)}, ${(log['lng'] as num).toStringAsFixed(5)}',
                            style: const TextStyle(fontFamily: 'monospace', fontSize: 12),
                          ),
                          subtitle: Text(
                            [
                              if (at != null) DateFormat('HH:mm:ss').format(at),
                              if (log['speed'] != null) '${(log['speed'] as num).toStringAsFixed(1)} m/s',
                              if (log['accuracy'] != null) '±${(log['accuracy'] as num).toStringAsFixed(0)}m',
                              log['source'],
                            ].whereType<String>().join(' · '),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
