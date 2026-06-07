import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../core/theme/app_theme.dart';
import '../models/leave.dart';
import '../providers/app_state.dart';

class LeavesScreen extends StatefulWidget {
  const LeavesScreen({super.key});

  @override
  State<LeavesScreen> createState() => _LeavesScreenState();
}

class _LeavesScreenState extends State<LeavesScreen> {
  List<Leave> _leaves = [];
  Map<String, dynamic> _balance = {};
  bool _onLeaveToday = false;
  bool _loading = true;
  bool _submitting = false;
  String? _statusFilter;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final result = await context
          .read<AppState>()
          .leaves
          .list(status: _statusFilter);
      if (mounted) {
        setState(() {
          _leaves = result['leaves'] as List<Leave>;
          _balance = result['balance'] as Map<String, dynamic>;
          _onLeaveToday = result['on_leave_today'] as bool;
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _cancelLeave(Leave leave) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel leave?'),
        content:
            const Text('This leave request will be cancelled.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('No')),
          FilledButton(
            style:
                FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Cancel leave'),
          ),
        ],
      ),
    );
    if (confirm != true) return;
    try {
      await context.read<AppState>().leaves.cancel(leave.id);
      await _load();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Leave cancelled')));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    }
  }

  Future<void> _applyLeave() async {
    if (_submitting) return;

    String leaveType = 'casual';
    DateTime startDate =
        DateTime.now().add(const Duration(days: 1));
    DateTime endDate =
        DateTime.now().add(const Duration(days: 1));
    bool isHalfDay = false;
    String? halfDayPeriod;
    final reasonCtrl = TextEditingController();

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          title: const Text('Apply for Leave'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                DropdownButtonFormField<String>(
                  value: leaveType,
                  items: const [
                    DropdownMenuItem(
                        value: 'casual',
                        child: Text('Casual Leave')),
                    DropdownMenuItem(
                        value: 'sick',
                        child: Text('Sick Leave')),
                    DropdownMenuItem(
                        value: 'annual',
                        child: Text('Annual Leave')),
                    DropdownMenuItem(
                        value: 'unpaid',
                        child: Text('Unpaid Leave')),
                    DropdownMenuItem(
                        value: 'emergency',
                        child: Text('Emergency Leave')),
                  ],
                  onChanged: (v) =>
                      setDialogState(() => leaveType = v ?? 'casual'),
                  decoration:
                      const InputDecoration(labelText: 'Leave Type'),
                ),
                const SizedBox(height: 12),
                InkWell(
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: ctx,
                      initialDate: startDate,
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now()
                          .add(const Duration(days: 365)),
                    );
                    if (picked != null) {
                      setDialogState(() {
                        startDate = picked;
                        if (endDate.isBefore(startDate)) {
                          endDate = startDate;
                        }
                      });
                    }
                  },
                  child: InputDecorator(
                    decoration: const InputDecoration(
                      labelText: 'Start Date',
                      suffixIcon:
                          Icon(Icons.calendar_today, size: 18),
                    ),
                    child: Text(
                        DateFormat('dd MMM yyyy').format(startDate)),
                  ),
                ),
                const SizedBox(height: 12),
                InkWell(
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: ctx,
                      initialDate: endDate.isBefore(startDate)
                          ? startDate
                          : endDate,
                      firstDate: startDate,
                      lastDate: DateTime.now()
                          .add(const Duration(days: 365)),
                    );
                    if (picked != null) {
                      setDialogState(() => endDate = picked);
                    }
                  },
                  child: InputDecorator(
                    decoration: const InputDecoration(
                      labelText: 'End Date',
                      suffixIcon:
                          Icon(Icons.calendar_today, size: 18),
                    ),
                    child: Text(
                        DateFormat('dd MMM yyyy').format(endDate)),
                  ),
                ),
                const SizedBox(height: 12),
                // Half-day toggle only for single-day leave
                if (DateUtils.dateOnly(startDate) ==
                    DateUtils.dateOnly(endDate))
                  SwitchListTile(
                    value: isHalfDay,
                    title: const Text('Half Day'),
                    contentPadding: EdgeInsets.zero,
                    onChanged: (v) => setDialogState(() {
                      isHalfDay = v;
                      if (!v) halfDayPeriod = null;
                    }),
                  ),
                if (isHalfDay) ...[
                  const SizedBox(height: 8),
                  DropdownButtonFormField<String>(
                    value: halfDayPeriod,
                    hint: const Text('Select period'),
                    items: const [
                      DropdownMenuItem(
                          value: 'first_half',
                          child: Text('First Half')),
                      DropdownMenuItem(
                          value: 'second_half',
                          child: Text('Second Half')),
                    ],
                    onChanged: (v) =>
                        setDialogState(() => halfDayPeriod = v),
                    decoration:
                        const InputDecoration(labelText: 'Period'),
                  ),
                ],
                const SizedBox(height: 12),
                TextField(
                  controller: reasonCtrl,
                  maxLines: 3,
                  decoration: const InputDecoration(
                    labelText: 'Reason *',
                    hintText: 'Minimum 10 characters',
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx, false),
                child: const Text('Cancel')),
            FilledButton(
                onPressed: () => Navigator.pop(ctx, true),
                child: const Text('Apply')),
          ],
        ),
      ),
    );

    if (ok != true) return;

    final reason = reasonCtrl.text.trim();
    if (reason.length < 10) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content:
                Text('Reason must be at least 10 characters')));
      }
      return;
    }
    if (isHalfDay && halfDayPeriod == null) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('Please select half-day period')));
      }
      return;
    }

    setState(() => _submitting = true);
    try {
      await context.read<AppState>().leaves.create(
            leaveType: leaveType,
            startDate: DateFormat('yyyy-MM-dd').format(startDate),
            endDate: DateFormat('yyyy-MM-dd').format(endDate),
            reason: reason,
            isHalfDay: isHalfDay,
            halfDayPeriod: isHalfDay ? halfDayPeriod : null,
          );
      await _load();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('Leave application submitted')));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  String _balanceRemaining(String type) {
    final val = _balance[type];
    if (val == null) return '0';
    if (val is num) return val.toStringAsFixed(1);
    if (val is Map) {
      final remaining = val['remaining'];
      if (remaining is num) return remaining.toStringAsFixed(1);
    }
    return '0';
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'approved':
        return Colors.green;
      case 'rejected':
        return Colors.red;
      case 'cancelled':
        return Colors.grey;
      default:
        return Colors.orange;
    }
  }

  Color _typeColor(String type) {
    switch (type) {
      case 'sick':
        return Colors.red;
      case 'annual':
        return Colors.blue;
      case 'emergency':
        return Colors.deepOrange;
      case 'unpaid':
        return Colors.grey;
      default:
        return AppTheme.primary;
    }
  }

  IconData _typeIcon(String type) {
    switch (type) {
      case 'sick':
        return Icons.sick;
      case 'annual':
        return Icons.beach_access;
      case 'emergency':
        return Icons.warning;
      case 'unpaid':
        return Icons.money_off;
      default:
        return Icons.event_available;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Leaves')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _submitting ? null : _applyLeave,
        icon: _submitting
            ? const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(
                    strokeWidth: 2, color: Colors.white))
            : const Icon(Icons.add),
        label: const Text('Apply Leave'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: Column(
                children: [
                  // On leave today banner
                  if (_onLeaveToday)
                    Container(
                      width: double.infinity,
                      color: Colors.orange.withValues(alpha: 0.1),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 8),
                      child: const Row(
                        children: [
                          Icon(Icons.info_outline,
                              color: Colors.orange, size: 16),
                          SizedBox(width: 8),
                          Text(
                            'You are on approved leave today',
                            style: TextStyle(color: Colors.orange),
                          ),
                        ],
                      ),
                    ),
                  // Balance cards
                  if (_balance.isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          for (final type in [
                            'casual',
                            'sick',
                            'annual'
                          ])
                            if (_balance[type] != null)
                              Expanded(
                                child: Padding(
                                  padding:
                                      const EdgeInsets.only(right: 8),
                                  child: Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: _typeColor(type)
                                          .withValues(alpha: 0.08),
                                      borderRadius:
                                          BorderRadius.circular(12),
                                      border: Border.all(
                                          color: _typeColor(type)
                                              .withValues(alpha: 0.2)),
                                    ),
                                    child: Column(
                                      children: [
                                        Text(
                                          _balanceRemaining(type),
                                          style: TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.bold,
                                            color: _typeColor(type),
                                          ),
                                        ),
                                        Text(
                                          type[0].toUpperCase() +
                                              type.substring(1),
                                          style: TextStyle(
                                              fontSize: 11,
                                              color: _typeColor(type)),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                        ],
                      ),
                    ),
                  // Status filter chips
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    padding:
                        const EdgeInsets.symmetric(horizontal: 16),
                    child: Row(
                      children: [
                        for (final item in [
                          [null, 'All'],
                          ['pending', 'Pending'],
                          ['approved', 'Approved'],
                          ['rejected', 'Rejected'],
                          ['cancelled', 'Cancelled'],
                        ])
                          Padding(
                            padding: const EdgeInsets.only(right: 8),
                            child: FilterChip(
                              label: Text(item[1]!),
                              selected: _statusFilter == item[0],
                              onSelected: (_) {
                                setState(
                                    () => _statusFilter = item[0]);
                                _load();
                              },
                            ),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),
                  // Leave list
                  Expanded(
                    child: _leaves.isEmpty
                        ? const Center(
                            child: Text('No leave requests',
                                style: TextStyle(
                                    color: Color(0xFF94A3B8))),
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.fromLTRB(
                                16, 0, 16, 90),
                            itemCount: _leaves.length,
                            itemBuilder: (context, i) {
                              final l = _leaves[i];
                              return Card(
                                margin:
                                    const EdgeInsets.only(bottom: 8),
                                child: ListTile(
                                  leading: CircleAvatar(
                                    backgroundColor:
                                        _typeColor(l.leaveType)
                                            .withValues(alpha: 0.1),
                                    child: Icon(
                                        _typeIcon(l.leaveType),
                                        color:
                                            _typeColor(l.leaveType),
                                        size: 20),
                                  ),
                                  title: Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          l.leaveTypeLabel ??
                                              l.leaveType,
                                          style: const TextStyle(
                                              fontWeight:
                                                  FontWeight.w600),
                                        ),
                                      ),
                                      Chip(
                                        label: Text(
                                            l.statusLabel ?? l.status,
                                            style: const TextStyle(
                                                fontSize: 11)),
                                        backgroundColor:
                                            _statusColor(l.status)
                                                .withValues(alpha: 0.1),
                                        padding: EdgeInsets.zero,
                                        visualDensity:
                                            VisualDensity.compact,
                                      ),
                                    ],
                                  ),
                                  subtitle: Text(
                                    '${l.startDate} → ${l.endDate} · ${l.daysCount} day${l.daysCount != 1 ? 's' : ''}${l.isHalfDay ? ' (half)' : ''}',
                                  ),
                                  trailing: l.isPending
                                      ? IconButton(
                                          icon: const Icon(
                                              Icons.cancel_outlined,
                                              color: Colors.red),
                                          onPressed: () =>
                                              _cancelLeave(l),
                                          tooltip: 'Cancel',
                                        )
                                      : null,
                                ),
                              );
                            },
                          ),
                  ),
                ],
              ),
            ),
    );
  }
}
