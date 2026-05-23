import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme/app_theme.dart';
import '../providers/app_state.dart';
import 'movement_log_screen.dart';
import 'route_history_screen.dart';
import 'visit_detail_screen.dart';

class DashboardTab extends StatelessWidget {
  const DashboardTab({super.key});

  @override
  Widget build(BuildContext context) {
    final state = context.watch<AppState>();
    final user = state.user;
    final dash = state.dashboardData;

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Hello, ${user?.name.split(' ').first ?? 'Rep'}'),
            if (user?.companyName != null)
              Text(
                user!.companyName!,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: const Color(0xFF64748B),
                    ),
              ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () async {
              await state.refreshDashboard();
            },
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () => state.logout(),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: state.refreshDashboard,
        child: dash == null
            ? const Center(child: CircularProgressIndicator())
            : ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _AttendanceCard(state: state, dash: dash),
                  if (state.lastGeofenceMessage != null) ...[
                    const SizedBox(height: 8),
                    Card(
                      color: const Color(0xFFEFF6FF),
                      child: ListTile(
                        leading: const Icon(Icons.fence, color: Colors.blue),
                        title: Text(state.lastGeofenceMessage!, style: const TextStyle(fontSize: 13)),
                      ),
                    ),
                  ],
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => const RouteHistoryScreen()),
                          ),
                          icon: const Icon(Icons.map),
                          label: const Text('Route map'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => const MovementLogScreen()),
                          ),
                          icon: const Icon(Icons.list_alt),
                          label: const Text('GPS log'),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  if (dash.activeVisit != null) ...[
                    _ActiveVisitCard(visit: dash.activeVisit!),
                    const SizedBox(height: 16),
                  ],
                  _StatsRow(stats: dash.stats),
                  const SizedBox(height: 16),
                  if (dash.reminders.isNotEmpty) ...[
                    Text('Reminders', style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 8),
                    ...dash.reminders.map(
                      (n) => Card(
                        child: ListTile(
                          leading: const Icon(Icons.notifications_active, color: AppTheme.primary),
                          title: Text(n.title, maxLines: 1, overflow: TextOverflow.ellipsis),
                          subtitle: Text(n.body, maxLines: 2, overflow: TextOverflow.ellipsis),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                  Text("Today's visits", style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 8),
                  if (dash.todayVisits.isEmpty)
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(24),
                        child: Center(child: Text('No visits today yet')),
                      ),
                    )
                  else
                    ...dash.todayVisits.map(
                      (v) => Card(
                        child: ListTile(
                          title: Text(v.placeName),
                          subtitle: Text(v.status.replaceAll('_', ' ')),
                          trailing: const Icon(Icons.chevron_right),
                          onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => VisitDetailScreen(visitId: v.id),
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
      ),
    );
  }
}

class _AttendanceCard extends StatelessWidget {
  const _AttendanceCard({required this.state, required this.dash});

  final AppState state;
  final dynamic dash;

  @override
  Widget build(BuildContext context) {
    final onDuty = dash.trackingActive;

    return Card(
      color: onDuty ? const Color(0xFFECFDF5) : null,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              children: [
                Icon(
                  onDuty ? Icons.gps_fixed : Icons.gps_off,
                  color: onDuty ? AppTheme.primary : Colors.grey,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    onDuty ? 'On duty — live tracking active' : 'Off duty',
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            FilledButton.icon(
              onPressed: () async {
                try {
                  if (onDuty) {
                    await state.clockOut();
                  } else {
                    await state.clockIn();
                  }
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(onDuty ? 'Clocked out' : 'Clocked in'),
                      ),
                    );
                  }
                } catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(e.toString())),
                    );
                  }
                }
              },
              icon: Icon(onDuty ? Icons.logout : Icons.login),
              label: Text(onDuty ? 'Clock out' : 'Clock in'),
              style: FilledButton.styleFrom(
                backgroundColor: onDuty ? Colors.orange : AppTheme.primary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ActiveVisitCard extends StatelessWidget {
  const _ActiveVisitCard({required this.visit});

  final dynamic visit;

  @override
  Widget build(BuildContext context) {
    return Card(
      color: const Color(0xFFFFF7ED),
      child: ListTile(
        leading: const Icon(Icons.play_circle, color: Colors.orange),
        title: const Text('Active visit', style: TextStyle(fontWeight: FontWeight.bold)),
        subtitle: Text(visit.placeName),
        trailing: const Icon(Icons.chevron_right),
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => VisitDetailScreen(visitId: visit.id)),
        ),
      ),
    );
  }
}

class _StatsRow extends StatelessWidget {
  const _StatsRow({required this.stats});

  final Map<String, int> stats;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(child: _StatChip(label: 'Planned', value: stats['planned'] ?? 0, color: Colors.blue)),
        const SizedBox(width: 8),
        Expanded(child: _StatChip(label: 'Active', value: stats['in_progress'] ?? 0, color: Colors.orange)),
        const SizedBox(width: 8),
        Expanded(child: _StatChip(label: 'Done', value: stats['completed'] ?? 0, color: Colors.green)),
      ],
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({required this.label, required this.value, required this.color});

  final String label;
  final int value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        child: Column(
          children: [
            Text('$value', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
            Text(label, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
          ],
        ),
      ),
    );
  }
}
