import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../core/theme/app_theme.dart';
import '../models/customer.dart';
import '../providers/app_state.dart';

class TourPlanScreen extends StatefulWidget {
  const TourPlanScreen({super.key});

  @override
  State<TourPlanScreen> createState() => _TourPlanScreenState();
}

class _TourPlanScreenState extends State<TourPlanScreen> {
  // Each plan entry: {customer, date, status}
  final List<_PlanEntry> _entries = [];
  List<Customer> _customers = [];
  bool _loadingCustomers = true;
  DateTime _weekStart = _getWeekStart(DateTime.now());

  static DateTime _getWeekStart(DateTime d) {
    final diff = d.weekday - 1; // Monday = 1
    return DateTime(d.year, d.month, d.day - diff);
  }

  @override
  void initState() {
    super.initState();
    _loadCustomers();
  }

  Future<void> _loadCustomers() async {
    try {
      final list = await context.read<AppState>().customers.list();
      if (mounted) setState(() => _customers = list);
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loadingCustomers = false);
    }
  }

  List<DateTime> get _weekDays =>
      List.generate(6, (i) => _weekStart.add(Duration(days: i))); // Mon–Sat

  List<_PlanEntry> _entriesForDay(DateTime day) =>
      _entries.where((e) => _isSameDay(e.date, day)).toList();

  bool _isSameDay(DateTime a, DateTime b) =>
      a.year == b.year && a.month == b.month && a.day == b.day;

  void _prevWeek() => setState(() => _weekStart = _weekStart.subtract(const Duration(days: 7)));
  void _nextWeek() => setState(() => _weekStart = _weekStart.add(const Duration(days: 7)));

  String _weekLabel() {
    final end = _weekStart.add(const Duration(days: 5));
    return '${DateFormat('d MMM').format(_weekStart)} – ${DateFormat('d MMM yyyy').format(end)}';
  }

  Future<void> _addEntry(DateTime day) async {
    if (_loadingCustomers || _customers.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No customers found. Add customers first.')),
      );
      return;
    }

    final result = await showDialog<Customer>(
      context: context,
      builder: (_) => _CustomerPickDialog(customers: _customers),
    );

    if (result == null) return;

    setState(() {
      _entries.add(_PlanEntry(
        customer: result,
        date: day,
        status: _PlanStatus.planned,
      ));
    });
  }

  void _removeEntry(_PlanEntry entry) {
    setState(() => _entries.remove(entry));
  }

  @override
  Widget build(BuildContext context) {
    final today = DateTime.now();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Tour Plan'),
        actions: [
          IconButton(
            icon: const Icon(Icons.today),
            tooltip: 'Go to current week',
            onPressed: () => setState(() => _weekStart = _getWeekStart(today)),
          ),
        ],
      ),
      body: Column(
        children: [
          // Week navigation
          Container(
            color: Theme.of(context).scaffoldBackgroundColor,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            child: Row(
              children: [
                IconButton(
                  icon: const Icon(Icons.chevron_left),
                  onPressed: _prevWeek,
                ),
                Expanded(
                  child: Text(
                    _weekLabel(),
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.chevron_right),
                  onPressed: _nextWeek,
                ),
              ],
            ),
          ),
          const Divider(height: 1),

          // Weekly summary chips
          _WeeklySummary(entries: _entries, weekDays: _weekDays),
          const Divider(height: 1),

          // Day rows
          Expanded(
            child: ListView.separated(
              padding: const EdgeInsets.only(bottom: 24),
              itemCount: _weekDays.length,
              separatorBuilder: (_, __) => const Divider(height: 1),
              itemBuilder: (context, i) {
                final day = _weekDays[i];
                final dayEntries = _entriesForDay(day);
                final isToday = _isSameDay(day, today);
                final isPast = day.isBefore(DateTime(today.year, today.month, today.day));

                return _DayRow(
                  day: day,
                  isToday: isToday,
                  isPast: isPast,
                  entries: dayEntries,
                  onAdd: () => _addEntry(day),
                  onRemove: _removeEntry,
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

// ── Weekly summary ─────────────────────────────────────────────────────────────

class _WeeklySummary extends StatelessWidget {
  const _WeeklySummary({required this.entries, required this.weekDays});
  final List<_PlanEntry> entries;
  final List<DateTime> weekDays;

  @override
  Widget build(BuildContext context) {
    int planned = 0, completed = 0, missed = 0;
    final today = DateTime.now();
    final todayDate = DateTime(today.year, today.month, today.day);
    for (final e in entries) {
      if (!weekDays.any((d) => d.year == e.date.year && d.month == e.date.month && d.day == e.date.day)) continue;
      if (e.status == _PlanStatus.completed) {
        completed++;
      } else if (e.date.isBefore(todayDate)) {
        missed++;
      } else {
        planned++;
      }
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
      color: const Color(0xFFF8FAFC),
      child: Row(
        children: [
          _SummaryChip(label: 'Planned', value: planned, color: Colors.blue),
          const SizedBox(width: 8),
          _SummaryChip(label: 'Done', value: completed, color: Colors.green),
          const SizedBox(width: 8),
          _SummaryChip(label: 'Missed', value: missed, color: Colors.red),
          const Spacer(),
          Text(
            '${planned + completed + missed} doctors',
            style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
          ),
        ],
      ),
    );
  }
}

class _SummaryChip extends StatelessWidget {
  const _SummaryChip({required this.label, required this.value, required this.color});
  final String label;
  final int value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text('$value', style: TextStyle(fontWeight: FontWeight.bold, color: color, fontSize: 13)),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(fontSize: 11, color: color)),
        ],
      ),
    );
  }
}

// ── Day row ───────────────────────────────────────────────────────────────────

class _DayRow extends StatelessWidget {
  const _DayRow({
    required this.day,
    required this.isToday,
    required this.isPast,
    required this.entries,
    required this.onAdd,
    required this.onRemove,
  });

  final DateTime day;
  final bool isToday;
  final bool isPast;
  final List<_PlanEntry> entries;
  final VoidCallback onAdd;
  final ValueChanged<_PlanEntry> onRemove;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: isToday ? const Color(0xFFF0FDF4) : null,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              // Day label
              SizedBox(
                width: 80,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      DateFormat('EEE').format(day),
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        color: isToday ? AppTheme.primary : const Color(0xFF334155),
                      ),
                    ),
                    Text(
                      DateFormat('d MMM').format(day),
                      style: TextStyle(
                        fontSize: 11,
                        color: isToday ? AppTheme.primary : const Color(0xFF94A3B8),
                      ),
                    ),
                  ],
                ),
              ),
              // Visit chips
              Expanded(
                child: entries.isEmpty
                    ? Text(
                        isPast ? 'No visits planned' : 'Add doctors',
                        style: const TextStyle(fontSize: 12, color: Color(0xFFCBD5E1)),
                      )
                    : Wrap(
                        spacing: 6,
                        runSpacing: 4,
                        children: entries
                            .map((e) => _EntryChip(entry: e, onRemove: () => onRemove(e)))
                            .toList(),
                      ),
              ),
              // Add button
              if (!isPast)
                IconButton(
                  icon: const Icon(Icons.add_circle_outline, size: 20),
                  color: AppTheme.primary,
                  onPressed: onAdd,
                  visualDensity: VisualDensity.compact,
                ),
            ],
          ),
        ],
      ),
    );
  }
}

class _EntryChip extends StatelessWidget {
  const _EntryChip({required this.entry, required this.onRemove});
  final _PlanEntry entry;
  final VoidCallback onRemove;

  Color get _color {
    switch (entry.status) {
      case _PlanStatus.completed:
        return Colors.green;
      case _PlanStatus.missed:
        return Colors.red;
      default:
        return AppTheme.primary;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text(entry.customer.name, style: TextStyle(fontSize: 11, color: _color)),
      backgroundColor: _color.withValues(alpha: 0.1),
      side: BorderSide(color: _color.withValues(alpha: 0.3)),
      deleteIcon: const Icon(Icons.close, size: 14),
      onDeleted: entry.status == _PlanStatus.planned ? onRemove : null,
      visualDensity: VisualDensity.compact,
      padding: EdgeInsets.zero,
      labelPadding: const EdgeInsets.symmetric(horizontal: 6),
    );
  }
}

// ── Customer pick dialog ──────────────────────────────────────────────────────

class _CustomerPickDialog extends StatefulWidget {
  const _CustomerPickDialog({required this.customers});
  final List<Customer> customers;

  @override
  State<_CustomerPickDialog> createState() => _CustomerPickDialogState();
}

class _CustomerPickDialogState extends State<_CustomerPickDialog> {
  final _ctrl = TextEditingController();
  List<Customer> _filtered = [];

  @override
  void initState() {
    super.initState();
    _filtered = widget.customers;
    _ctrl.addListener(() {
      final q = _ctrl.text.toLowerCase();
      setState(() {
        _filtered = q.isEmpty
            ? widget.customers
            : widget.customers.where((c) => c.name.toLowerCase().contains(q)).toList();
      });
    });
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Add to tour plan',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _ctrl,
              autofocus: true,
              decoration: InputDecoration(
                hintText: 'Search doctor / chemist...',
                prefixIcon: const Icon(Icons.search, size: 18),
                isDense: true,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 8),
            SizedBox(
              height: 260,
              child: _filtered.isEmpty
                  ? const Center(child: Text('No results', style: TextStyle(color: Colors.grey)))
                  : ListView.separated(
                      shrinkWrap: true,
                      itemCount: _filtered.length,
                      separatorBuilder: (_, __) => const Divider(height: 1),
                      itemBuilder: (_, i) {
                        final c = _filtered[i];
                        final sub = [
                          c.type[0].toUpperCase() + c.type.substring(1),
                          if (c.specialty != null) c.specialty!,
                        ].join(' · ');
                        return ListTile(
                          dense: true,
                          title: Text(c.name, style: const TextStyle(fontSize: 13)),
                          subtitle: Text(sub, style: const TextStyle(fontSize: 11)),
                          onTap: () => Navigator.pop(context, c),
                        );
                      },
                    ),
            ),
            const SizedBox(height: 8),
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Data models ───────────────────────────────────────────────────────────────

enum _PlanStatus { planned, completed, missed }

class _PlanEntry {
  const _PlanEntry({
    required this.customer,
    required this.date,
    required this.status,
  });
  final Customer customer;
  final DateTime date;
  final _PlanStatus status;
}
