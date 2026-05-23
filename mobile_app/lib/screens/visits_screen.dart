import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/location/location_helper.dart';
import '../core/theme/app_theme.dart';
import '../models/visit.dart';
import '../providers/app_state.dart';
import 'visit_detail_screen.dart';

class VisitsScreen extends StatefulWidget {
  const VisitsScreen({super.key});

  @override
  State<VisitsScreen> createState() => _VisitsScreenState();
}

class _VisitsScreenState extends State<VisitsScreen> {
  List<Visit> _visits = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final list = await context.read<AppState>().visits.list();
      if (mounted) setState(() => _visits = list);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _quickStart() async {
    final state = context.read<AppState>();
    if (state.dashboardData?.activeVisit != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Complete your active visit first')),
      );
      return;
    }
    try {
      final pos = await LocationHelper.getCurrentPosition();
      final visit = await state.visits.create({
        'visit_type': 'doctor',
        'place_name': 'Field visit',
        'start_now': true,
        'latitude': pos.latitude,
        'longitude': pos.longitude,
      });
      await state.refreshDashboard();
      if (mounted) {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => VisitDetailScreen(visitId: visit.id)),
        );
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Visits')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _quickStart,
        icon: const Icon(Icons.add),
        label: const Text('Start visit'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: _visits.isEmpty
                  ? const ListTile(title: Text('No visits yet'))
                  : ListView.builder(
                      itemCount: _visits.length,
                      itemBuilder: (context, i) {
                        final v = _visits[i];
                        return Card(
                          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: AppTheme.primary.withValues(alpha: 0.15),
                              child: Icon(
                                v.isCompleted ? Icons.check : Icons.place,
                                color: AppTheme.primary,
                              ),
                            ),
                            title: Text(v.placeName),
                            subtitle: Text(v.status.replaceAll('_', ' ')),
                            trailing: const Icon(Icons.chevron_right),
                            onTap: () async {
                              await Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => VisitDetailScreen(visitId: v.id),
                                ),
                              );
                              _load();
                            },
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
