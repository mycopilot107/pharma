import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/app_state.dart';
import 'customers_screen.dart';
import 'expenses_screen.dart';
import 'notifications_screen.dart';
import 'products_screen.dart';

class MoreScreen extends StatelessWidget {
  const MoreScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final unread = context.watch<AppState>().unreadCount;

    return Scaffold(
      appBar: AppBar(title: const Text('More')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _NavTile(
            icon: Icons.people,
            title: 'Customers',
            subtitle: 'Doctors, chemists & hospitals',
            color: Colors.blue,
            onTap: () => Navigator.push(context,
                MaterialPageRoute(
                    builder: (_) => const CustomersScreen())),
          ),
          const SizedBox(height: 8),
          _NavTile(
            icon: Icons.medication,
            title: 'Products',
            subtitle: 'Browse product catalog',
            color: Colors.purple,
            onTap: () => Navigator.push(context,
                MaterialPageRoute(
                    builder: (_) => const ProductsScreen())),
          ),
          const SizedBox(height: 8),
          _NavTile(
            icon: Icons.receipt_long,
            title: 'Expenses',
            subtitle: 'Track your field expenses',
            color: Colors.orange,
            onTap: () => Navigator.push(context,
                MaterialPageRoute(
                    builder: (_) => const ExpensesScreen())),
          ),
          const SizedBox(height: 8),
          _NavTile(
            icon: Icons.notifications,
            title: 'Notifications',
            subtitle:
                unread > 0 ? '$unread unread' : 'All caught up',
            color: unread > 0 ? Colors.red : const Color(0xFF64748B),
            badge: unread > 0 ? '$unread' : null,
            onTap: () => Navigator.push(context,
                MaterialPageRoute(
                    builder: (_) => const NotificationsScreen())),
          ),
        ],
      ),
    );
  }
}

class _NavTile extends StatelessWidget {
  const _NavTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.onTap,
    this.badge,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final VoidCallback onTap;
  final String? badge;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: color.withValues(alpha: 0.1),
          child: Icon(icon, color: color),
        ),
        title:
            Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(subtitle),
        trailing: badge != null
            ? Badge(
                label: Text(badge!),
                child: const Icon(Icons.chevron_right))
            : const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}
