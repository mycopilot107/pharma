import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme/app_theme.dart';
import '../models/app_notification.dart';
import '../providers/app_state.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<AppNotification> _items = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final list = await context.read<AppState>().notifications.list();
      if (mounted) setState(() => _items = list);
      await context.read<AppState>().refreshUnreadCount();
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  IconData _iconForType(String type) {
    switch (type) {
      case 'doctor_revisit':
        return Icons.medical_services;
      case 'target_alert':
        return Icons.track_changes;
      case 'meeting_reminder':
        return Icons.event;
      case 'follow_up':
        return Icons.phone_callback;
      default:
        return Icons.notifications;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Alerts & reminders'),
        actions: [
          TextButton(
            onPressed: () async {
              await context.read<AppState>().notifications.markAllRead();
              await _load();
            },
            child: const Text('Read all'),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: _items.isEmpty
                  ? const Center(child: Text('No alerts right now'))
                  : ListView.builder(
                      itemCount: _items.length,
                      itemBuilder: (context, i) {
                        final n = _items[i];
                        return Dismissible(
                          key: ValueKey(n.id),
                          direction: DismissDirection.endToStart,
                          onDismissed: (_) async {
                            await context.read<AppState>().notifications.dismiss(n.id);
                          },
                          background: Container(
                            color: Colors.red.shade100,
                            alignment: Alignment.centerRight,
                            padding: const EdgeInsets.only(right: 16),
                            child: const Icon(Icons.delete, color: Colors.red),
                          ),
                          child: Card(
                            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                            color: n.isUnread ? const Color(0xFFF0FDFA) : null,
                            child: ListTile(
                              leading: Icon(_iconForType(n.type), color: AppTheme.primary),
                              title: Text(
                                n.title,
                                style: TextStyle(
                                  fontWeight: n.isUnread ? FontWeight.bold : FontWeight.normal,
                                ),
                              ),
                              subtitle: Text(n.body),
                              onTap: () async {
                                if (n.isUnread) {
                                  await context.read<AppState>().notifications.markRead(n.id);
                                  await _load();
                                }
                              },
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
