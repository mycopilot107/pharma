import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../core/location/location_helper.dart';
import '../core/theme/app_theme.dart';
import '../models/customer.dart';
import '../models/visit.dart';
import '../providers/app_state.dart';
import '../services/customer_service.dart';
import 'visit_detail_screen.dart';

class VisitsScreen extends StatefulWidget {
  const VisitsScreen({super.key});

  @override
  State<VisitsScreen> createState() => _VisitsScreenState();
}

class _VisitsScreenState extends State<VisitsScreen> {
  List<Visit> _visits = [];
  bool _loading = true;
  bool _starting = false;

  late DateTime _selectedDate;
  String? _selectedStatus;

  static DateTime get _today =>
      DateTime(DateTime.now().year, DateTime.now().month, DateTime.now().day);

  static const _statuses = [
    _StatusOption(label: 'All',         value: null,          color: Colors.grey),
    _StatusOption(label: 'Planned',     value: 'planned',     color: Colors.blue),
    _StatusOption(label: 'In Progress', value: 'in_progress', color: Colors.orange),
    _StatusOption(label: 'Completed',   value: 'completed',   color: Colors.green),
  ];

  @override
  void initState() {
    super.initState();
    _selectedDate = _today;
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
      final list = await context
          .read<AppState>()
          .visits
          .list(date: dateStr, status: _selectedStatus);
      if (mounted) setState(() => _visits = list);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: _today,
    );
    if (picked != null) {
      setState(() => _selectedDate = picked);
      _load();
    }
  }

  void _prevDay() {
    setState(() => _selectedDate = _selectedDate.subtract(const Duration(days: 1)));
    _load();
  }

  void _nextDay() {
    if (_selectedDate.isBefore(_today)) {
      setState(() => _selectedDate = _selectedDate.add(const Duration(days: 1)));
      _load();
    }
  }

  void _goToday() {
    if (_selectedDate != _today) {
      setState(() => _selectedDate = _today);
      _load();
    }
  }

  void _setStatus(String? status) {
    if (_selectedStatus == status) return;
    setState(() => _selectedStatus = status);
    _load();
  }

  Future<void> _quickStart() async {
    if (_starting) return;

    final state = context.read<AppState>();

    // Refresh dashboard first so the activeVisit check is current
    await state.refreshDashboard();

    if (!mounted) return;

    if (state.dashboardData?.activeVisit != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Complete your active visit first')),
      );
      return;
    }

    // Show customer selection sheet — instant UI, no GPS wait yet
    final result = await showModalBottomSheet<_StartVisitResult>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _StartVisitSheet(customers: state.customers),
    );

    if (result == null || !mounted) return; // user cancelled

    // GPS + visit creation — show spinner on FAB
    setState(() => _starting = true);
    try {
      final pos = await LocationHelper.getCurrentPosition();
      final visit = await state.visits.create({
        'visit_type': result.visitType,
        if (result.customerId != null) 'customer_id': result.customerId,
        if (result.customerId == null) 'place_name': 'Field visit',
        'start_now': true,
        'latitude': pos.latitude,
        'longitude': pos.longitude,
      });
      await state.refreshDashboard();
      if (mounted) {
        // Await the push so we can refresh when the user returns
        await Navigator.push(
          context,
          MaterialPageRoute(
              builder: (_) => VisitDetailScreen(visitId: visit.id)),
        );
        // Refresh after returning from visit detail
        await state.refreshDashboard();
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => _starting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Visits')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _starting ? null : _quickStart,
        icon: _starting
            ? const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(
                    strokeWidth: 2, color: Colors.white),
              )
            : const Icon(Icons.add),
        label: const Text('Start visit'),
      ),
      body: Column(
        children: [
          _FilterBar(
            selectedDate: _selectedDate,
            today: _today,
            selectedStatus: _selectedStatus,
            statuses: _statuses,
            onPickDate: _pickDate,
            onPrevDay: _prevDay,
            onNextDay: _nextDay,
            onGoToday: _goToday,
            onStatusChanged: _setStatus,
          ),
          const Divider(height: 1),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _load,
                    child: _visits.isEmpty
                        ? const Center(
                            child: Padding(
                              padding: EdgeInsets.all(32),
                              child: Text(
                                'No visits found',
                                style: TextStyle(color: Color(0xFF94A3B8)),
                              ),
                            ),
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.only(bottom: 90),
                            itemCount: _visits.length,
                            itemBuilder: (context, i) => _VisitTile(
                              visit: _visits[i],
                              onTap: () async {
                                await Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => VisitDetailScreen(
                                        visitId: _visits[i].id),
                                  ),
                                );
                                _load();
                              },
                            ),
                          ),
                  ),
          ),
        ],
      ),
    );
  }
}

// ── Start visit result ────────────────────────────────────────────────────────

class _StartVisitResult {
  const _StartVisitResult({this.customerId, this.visitType = 'doctor'});
  final int? customerId;
  final String visitType;
}

// ── Customer selection bottom sheet ──────────────────────────────────────────

class _StartVisitSheet extends StatefulWidget {
  const _StartVisitSheet({required this.customers});
  final CustomerService customers;

  @override
  State<_StartVisitSheet> createState() => _StartVisitSheetState();
}

class _StartVisitSheetState extends State<_StartVisitSheet> {
  final _searchCtrl = TextEditingController();
  List<Customer> _all = [];
  List<Customer> _filtered = [];
  bool _loading = true;
  String _visitType = 'doctor';

  static const _types = [
    ('doctor',   'Doctor',   Icons.person),
    ('chemist',  'Chemist',  Icons.medication),
    ('hospital', 'Hospital', Icons.local_hospital),
  ];

  @override
  void initState() {
    super.initState();
    _searchCtrl.addListener(_filter);
    _loadCustomers();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadCustomers() async {
    try {
      final list = await widget.customers.list();
      if (mounted) {
        setState(() {
          _all = list;
          _filtered = list;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _filter() {
    final q = _searchCtrl.text.trim().toLowerCase();
    setState(() {
      _filtered = q.isEmpty
          ? _all
          : _all.where((c) {
              return c.name.toLowerCase().contains(q) ||
                  (c.specialty?.toLowerCase().contains(q) ?? false) ||
                  (c.phone?.contains(q) ?? false);
            }).toList();
    });
  }

  void _pick(int? customerId) {
    Navigator.pop(
        context, _StartVisitResult(customerId: customerId, visitType: _visitType));
  }

  @override
  Widget build(BuildContext context) {
    final maxH = MediaQuery.of(context).size.height * 0.82;
    return Container(
      height: maxH,
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Handle
          Center(
            child: Container(
              margin: const EdgeInsets.only(top: 10, bottom: 4),
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey[300],
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          // Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            child: Row(
              children: [
                const Text(
                  'Start visit',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w600),
                ),
                const Spacer(),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                  visualDensity: VisualDensity.compact,
                ),
              ],
            ),
          ),
          // Visit type selector
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: _types.map((t) {
                final (value, label, icon) = t;
                final selected = _visitType == value;
                return Expanded(
                  child: Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: GestureDetector(
                      onTap: () => setState(() => _visitType = value),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 150),
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        decoration: BoxDecoration(
                          color: selected
                              ? AppTheme.primary.withValues(alpha: 0.12)
                              : Colors.grey[100],
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: selected
                                ? AppTheme.primary
                                : Colors.transparent,
                            width: 1.5,
                          ),
                        ),
                        child: Column(
                          children: [
                            Icon(icon,
                                size: 20,
                                color: selected
                                    ? AppTheme.primary
                                    : Colors.grey[600]),
                            const SizedBox(height: 3),
                            Text(
                              label,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: selected
                                    ? FontWeight.w600
                                    : FontWeight.normal,
                                color: selected
                                    ? AppTheme.primary
                                    : Colors.grey[700],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 12),
          // Search
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: TextField(
              controller: _searchCtrl,
              autofocus: false,
              decoration: InputDecoration(
                hintText: 'Search customers...',
                prefixIcon: const Icon(Icons.search, size: 20),
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(
                    horizontal: 12, vertical: 10),
                isDense: true,
              ),
            ),
          ),
          const SizedBox(height: 8),
          // "Skip customer" option
          ListTile(
            dense: true,
            leading: CircleAvatar(
              radius: 20,
              backgroundColor: Colors.grey[100],
              child: const Icon(Icons.person_off_outlined,
                  size: 18, color: Colors.grey),
            ),
            title: const Text('Start without customer'),
            subtitle: const Text('Free / unlinked visit',
                style: TextStyle(fontSize: 12)),
            trailing:
                const Icon(Icons.arrow_forward_ios, size: 13, color: Colors.grey),
            onTap: () => _pick(null),
          ),
          const Divider(height: 1),
          // Customer list
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _filtered.isEmpty
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Text(
                            _all.isEmpty
                                ? 'No customers added yet'
                                : 'No results for "${_searchCtrl.text}"',
                            style: const TextStyle(color: Colors.grey),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      )
                    : ListView.separated(
                        itemCount: _filtered.length,
                        separatorBuilder: (_, __) =>
                            const Divider(height: 1, indent: 60),
                        itemBuilder: (_, i) {
                          final c = _filtered[i];
                          final subtitle = [
                            c.type[0].toUpperCase() + c.type.substring(1),
                            if (c.specialty != null) c.specialty!,
                            if (c.phone != null) c.phone!,
                          ].join(' · ');
                          return ListTile(
                            dense: true,
                            leading: CircleAvatar(
                              radius: 20,
                              backgroundColor:
                                  AppTheme.primary.withValues(alpha: 0.1),
                              child: Text(
                                c.name[0].toUpperCase(),
                                style: const TextStyle(
                                  color: AppTheme.primary,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                              ),
                            ),
                            title: Text(c.name,
                                style: const TextStyle(fontSize: 14)),
                            subtitle: Text(subtitle,
                                style: const TextStyle(fontSize: 12)),
                            trailing: const Icon(Icons.arrow_forward_ios,
                                size: 13, color: Colors.grey),
                            onTap: () => _pick(c.id),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}

// ── Filter bar ────────────────────────────────────────────────────────────────

class _StatusOption {
  const _StatusOption({
    required this.label,
    required this.value,
    required this.color,
  });
  final String label;
  final String? value;
  final Color color;
}

class _FilterBar extends StatelessWidget {
  const _FilterBar({
    required this.selectedDate,
    required this.today,
    required this.selectedStatus,
    required this.statuses,
    required this.onPickDate,
    required this.onPrevDay,
    required this.onNextDay,
    required this.onGoToday,
    required this.onStatusChanged,
  });

  final DateTime selectedDate;
  final DateTime today;
  final String? selectedStatus;
  final List<_StatusOption> statuses;
  final VoidCallback onPickDate;
  final VoidCallback onPrevDay;
  final VoidCallback onNextDay;
  final VoidCallback onGoToday;
  final ValueChanged<String?> onStatusChanged;

  bool get _isToday => selectedDate.year == today.year &&
      selectedDate.month == today.month &&
      selectedDate.day == today.day;

  String get _dateLabel {
    if (_isToday) return 'Today';
    final yesterday = today.subtract(const Duration(days: 1));
    if (selectedDate.year == yesterday.year &&
        selectedDate.month == yesterday.month &&
        selectedDate.day == yesterday.day) return 'Yesterday';
    return DateFormat('dd MMM yyyy').format(selectedDate);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Theme.of(context).scaffoldBackgroundColor,
      padding: const EdgeInsets.fromLTRB(12, 10, 12, 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Date navigation row
          Row(
            children: [
              // Prev day arrow
              _NavArrow(
                icon: Icons.chevron_left,
                onTap: onPrevDay,
                enabled: true,
              ),
              const SizedBox(width: 4),
              // Date label — tap to open calendar
              Expanded(
                child: GestureDetector(
                  onTap: onPickDate,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.calendar_today,
                          size: 14, color: Color(0xFF64748B)),
                      const SizedBox(width: 6),
                      Text(
                        _dateLabel,
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: _isToday
                              ? AppTheme.primary
                              : const Color(0xFF1E293B),
                        ),
                      ),
                      const SizedBox(width: 4),
                      const Icon(Icons.arrow_drop_down,
                          size: 18, color: Color(0xFF94A3B8)),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 4),
              // Next day arrow (disabled on today)
              _NavArrow(
                icon: Icons.chevron_right,
                onTap: _isToday ? null : onNextDay,
                enabled: !_isToday,
              ),
              // "Today" shortcut — only shown when not on today
              if (!_isToday) ...[
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: onGoToday,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppTheme.primary.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'Today',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.primary,
                      ),
                    ),
                  ),
                ),
              ],
            ],
          ),
          const SizedBox(height: 8),
          // Status filter chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: statuses.map((s) {
                final selected = selectedStatus == s.value;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: FilterChip(
                    label: Text(s.label),
                    selected: selected,
                    onSelected: (_) => onStatusChanged(s.value),
                    selectedColor: s.color.withValues(alpha: 0.15),
                    checkmarkColor: s.color,
                    labelStyle: TextStyle(
                      fontSize: 12,
                      color: selected ? s.color : const Color(0xFF475569),
                      fontWeight:
                          selected ? FontWeight.w600 : FontWeight.normal,
                    ),
                    side: BorderSide(
                      color: selected ? s.color : const Color(0xFFCBD5E1),
                    ),
                    padding: const EdgeInsets.symmetric(horizontal: 4),
                    visualDensity: VisualDensity.compact,
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }
}

class _NavArrow extends StatelessWidget {
  const _NavArrow({
    required this.icon,
    required this.onTap,
    required this.enabled,
  });

  final IconData icon;
  final VoidCallback? onTap;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 32,
        height: 32,
        decoration: BoxDecoration(
          color: enabled
              ? const Color(0xFFF1F5F9)
              : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: const Color(0xFFE2E8F0)),
        ),
        child: Icon(
          icon,
          size: 20,
          color: enabled
              ? const Color(0xFF475569)
              : const Color(0xFFCBD5E1),
        ),
      ),
    );
  }
}

// ── Visit list tile ───────────────────────────────────────────────────────────

class _VisitTile extends StatelessWidget {
  const _VisitTile({required this.visit, required this.onTap});

  final Visit visit;
  final VoidCallback onTap;

  static const _statusColors = {
    'planned': Colors.blue,
    'in_progress': Colors.orange,
    'completed': Colors.green,
    'cancelled': Colors.grey,
  };

  static const _statusIcons = {
    'planned': Icons.schedule,
    'in_progress': Icons.play_circle,
    'completed': Icons.check_circle,
    'cancelled': Icons.cancel,
  };

  @override
  Widget build(BuildContext context) {
    final color = _statusColors[visit.status] ?? Colors.grey;
    final icon = _statusIcons[visit.status] ?? Icons.place;
    final label = visit.status.replaceAll('_', ' ').toUpperCase();

    String? subtitle;
    if (visit.customerName != null) subtitle = visit.customerName;
    if (visit.checkedInAt != null) {
      final time = _formatTime(visit.checkedInAt!);
      subtitle = subtitle != null ? '$subtitle  ·  $time' : time;
    }
    if (visit.durationMinutes != null && visit.isCompleted) {
      subtitle = (subtitle ?? '') + '  ·  ${visit.durationMinutes} min';
    }

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: color.withValues(alpha: 0.12),
          child: Icon(icon, color: color, size: 20),
        ),
        title: Text(visit.placeName,
            maxLines: 1, overflow: TextOverflow.ellipsis),
        subtitle: subtitle != null
            ? Text(subtitle,
                style: const TextStyle(
                    fontSize: 12, color: Color(0xFF64748B)))
            : null,
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                label,
                style: TextStyle(
                    fontSize: 10,
                    color: color,
                    fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(width: 4),
            const Icon(Icons.chevron_right, color: Color(0xFFCBD5E1)),
          ],
        ),
        onTap: onTap,
      ),
    );
  }

  String _formatTime(String iso) {
    try {
      final dt = DateTime.parse(iso).toLocal();
      return DateFormat('dd MMM, hh:mm a').format(dt);
    } catch (_) {
      return iso;
    }
  }
}
