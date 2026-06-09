import 'dart:convert';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../core/location/location_helper.dart';
import '../core/theme/app_theme.dart';
import '../models/product.dart';
import '../models/visit.dart';
import '../providers/app_state.dart';

class VisitDetailScreen extends StatefulWidget {
  const VisitDetailScreen({super.key, required this.visitId});

  final int visitId;

  @override
  State<VisitDetailScreen> createState() => _VisitDetailScreenState();
}

class _VisitDetailScreenState extends State<VisitDetailScreen> {
  Visit? _visit;
  bool _loading = true;
  bool _actionLoading = false;
  final _notesController = TextEditingController();

  // DCR state
  List<Product> _allProducts = [];
  final List<String> _selectedProducts = [];
  int _samplesGiven = 0;
  DateTime? _followUpDate;

  // Signature state
  final List<Offset?> _signaturePoints = [];
  bool _hasSignature = false;

  @override
  void initState() {
    super.initState();
    _load();
    _loadProducts();
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final v = await context.read<AppState>().visits.get(widget.visitId);
      if (mounted) {
        setState(() {
          _visit = v;
          _notesController.text = v.notes ?? '';
          if (v.samplesGiven != null) _samplesGiven = v.samplesGiven!;
          if (v.followUpDate != null) {
            try {
              _followUpDate = DateTime.parse(v.followUpDate!);
            } catch (_) {}
          }
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString().replaceFirst('ApiException: ', ''))),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _loadProducts() async {
    try {
      final list = await context.read<AppState>().products.list();
      if (mounted) setState(() => _allProducts = list);
    } catch (_) {}
  }

  Future<void> _checkIn() async {
    if (_actionLoading) return;
    setState(() => _actionLoading = true);
    try {
      final pos = await LocationHelper.getCurrentPosition();
      await context.read<AppState>().visits.checkIn(widget.visitId, pos.latitude, pos.longitude);
      await context.read<AppState>().refreshDashboard();
      await _load();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString().replaceFirst('ApiException: ', ''))));
    } finally {
      if (mounted) setState(() => _actionLoading = false);
    }
  }

  Future<void> _checkOut() async {
    if (_actionLoading) return;
    setState(() => _actionLoading = true);
    try {
      final pos = await LocationHelper.getCurrentPosition();
      final signatureB64 = _hasSignature ? await _captureSignatureBase64() : null;
      await context.read<AppState>().visits.checkOut(
            widget.visitId,
            pos.latitude,
            pos.longitude,
            notes: _notesController.text.isEmpty ? null : _notesController.text,
            productsPromoted: _selectedProducts.isEmpty ? null : List.from(_selectedProducts),
            samplesGiven: _samplesGiven > 0 ? _samplesGiven : null,
            followUpDate: _followUpDate != null
                ? DateFormat('yyyy-MM-dd').format(_followUpDate!)
                : null,
            signatureBase64: signatureB64,
          );
      await context.read<AppState>().refreshDashboard();
      if (mounted) Navigator.pop(context);
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString().replaceFirst('ApiException: ', ''))));
    } finally {
      if (mounted) setState(() => _actionLoading = false);
    }
  }

  Future<String?> _captureSignatureBase64() async {
    try {
      final recorder = ui.PictureRecorder();
      final canvas = Canvas(recorder, const Rect.fromLTWH(0, 0, 280, 140));
      canvas.drawColor(Colors.white, BlendMode.src);
      final paint = Paint()
        ..color = Colors.black
        ..strokeWidth = 2.0
        ..strokeCap = StrokeCap.round;
      for (int i = 0; i < _signaturePoints.length - 1; i++) {
        final p1 = _signaturePoints[i];
        final p2 = _signaturePoints[i + 1];
        if (p1 != null && p2 != null) {
          canvas.drawLine(p1, p2, paint);
        }
      }
      final picture = recorder.endRecording();
      final img = await picture.toImage(280, 140);
      final bytes = await img.toByteData(format: ui.ImageByteFormat.png);
      if (bytes == null) return null;
      return base64Encode(bytes.buffer.asUint8List());
    } catch (_) {
      return null;
    }
  }

  Future<void> _addPhoto() async {
    final picker = ImagePicker();
    final file = await picker.pickImage(source: ImageSource.camera, imageQuality: 80);
    if (file == null) return;
    try {
      await context.read<AppState>().visits.uploadPhotos(widget.visitId, [file.path]);
      await _load();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
    }
  }

  Future<void> _pickFollowUpDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _followUpDate ?? now.add(const Duration(days: 7)),
      firstDate: now,
      lastDate: now.add(const Duration(days: 365)),
    );
    if (picked != null) setState(() => _followUpDate = picked);
  }

  void _showProductPicker() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (_) => _ProductPickerSheet(
        products: _allProducts,
        selected: List.from(_selectedProducts),
        onDone: (selected) => setState(() {
          _selectedProducts
            ..clear()
            ..addAll(selected);
        }),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final v = _visit;

    return Scaffold(
      appBar: AppBar(title: Text(v?.placeName ?? 'Visit')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : v == null
              ? const Center(child: Text('Visit not found'))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _StatusBadge(status: v.status),
                      const SizedBox(height: 8),
                      if (v.isMockDetected)
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: Colors.red.shade50,
                            border: Border.all(color: Colors.red.shade300),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            children: [
                              Icon(Icons.warning_amber, color: Colors.red.shade700, size: 18),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Fake GPS detected — mock location app was active during this visit.',
                                  style: TextStyle(color: Colors.red.shade700, fontSize: 12),
                                ),
                              ),
                            ],
                          ),
                        ),
                      const SizedBox(height: 12),
                      if (v.address != null) Text(v.address!, style: const TextStyle(color: Color(0xFF64748B))),

                      // ── Visit Notes ──────────────────────────────────────
                      const SizedBox(height: 16),
                      TextField(
                        controller: _notesController,
                        maxLines: 3,
                        decoration: const InputDecoration(labelText: 'Visit notes'),
                      ),

                      // ── In-Progress: DCR fields + actions ─────────────
                      if (v.isInProgress) ...[
                        const SizedBox(height: 20),
                        _SectionHeader(title: 'Daily Call Report (DCR)'),
                        const SizedBox(height: 12),

                        // Products promoted
                        _DcrTile(
                          icon: Icons.medication,
                          label: 'Products promoted',
                          child: Wrap(
                            spacing: 6,
                            runSpacing: 4,
                            children: [
                              ..._selectedProducts.map(
                                (p) => Chip(
                                  label: Text(p, style: const TextStyle(fontSize: 12)),
                                  visualDensity: VisualDensity.compact,
                                  onDeleted: () => setState(() => _selectedProducts.remove(p)),
                                ),
                              ),
                              ActionChip(
                                avatar: const Icon(Icons.add, size: 16),
                                label: const Text('Add product', style: TextStyle(fontSize: 12)),
                                onPressed: _showProductPicker,
                                visualDensity: VisualDensity.compact,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 10),

                        // Samples given
                        _DcrTile(
                          icon: Icons.inventory_2,
                          label: 'Samples given',
                          child: Row(
                            children: [
                              IconButton(
                                icon: const Icon(Icons.remove_circle_outline),
                                onPressed: _samplesGiven > 0
                                    ? () => setState(() => _samplesGiven--)
                                    : null,
                                visualDensity: VisualDensity.compact,
                              ),
                              Text(
                                '$_samplesGiven',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              IconButton(
                                icon: const Icon(Icons.add_circle_outline),
                                onPressed: () => setState(() => _samplesGiven++),
                                visualDensity: VisualDensity.compact,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 10),

                        // Follow-up date
                        _DcrTile(
                          icon: Icons.event,
                          label: 'Follow-up date',
                          child: Row(
                            children: [
                              Text(
                                _followUpDate != null
                                    ? DateFormat('dd MMM yyyy').format(_followUpDate!)
                                    : 'Not set',
                                style: TextStyle(
                                  color: _followUpDate != null ? Colors.black87 : const Color(0xFF94A3B8),
                                ),
                              ),
                              const SizedBox(width: 8),
                              TextButton(
                                onPressed: _pickFollowUpDate,
                                style: TextButton.styleFrom(visualDensity: VisualDensity.compact),
                                child: const Text('Pick date'),
                              ),
                              if (_followUpDate != null)
                                IconButton(
                                  icon: const Icon(Icons.clear, size: 16),
                                  onPressed: () => setState(() => _followUpDate = null),
                                  visualDensity: VisualDensity.compact,
                                ),
                            ],
                          ),
                        ),

                        // Doctor signature
                        const SizedBox(height: 16),
                        _SectionHeader(title: 'Doctor Signature (optional)'),
                        const SizedBox(height: 8),
                        _SignaturePad(
                          points: _signaturePoints,
                          hasSignature: _hasSignature,
                          onChanged: (val) => setState(() => _hasSignature = val),
                        ),

                        // Action buttons
                        const SizedBox(height: 20),
                        FilledButton.icon(
                          onPressed: _actionLoading ? null : _checkOut,
                          icon: _actionLoading
                              ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : const Icon(Icons.logout),
                          label: const Text('Check out & submit DCR'),
                          style: FilledButton.styleFrom(backgroundColor: Colors.green),
                        ),
                        const SizedBox(height: 10),
                        OutlinedButton.icon(
                          onPressed: _actionLoading ? null : _addPhoto,
                          icon: const Icon(Icons.camera_alt),
                          label: const Text('Add photo proof'),
                        ),
                      ],

                      // ── Planned: check-in ────────────────────────────
                      if (v.isPlanned) ...[
                        const SizedBox(height: 24),
                        FilledButton.icon(
                          onPressed: _actionLoading ? null : _checkIn,
                          icon: _actionLoading
                              ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : const Icon(Icons.login),
                          label: const Text('Check in (GPS)'),
                          style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                        ),
                      ],

                      // ── Completed: DCR summary ───────────────────────
                      if (v.isCompleted) ...[
                        const SizedBox(height: 20),
                        _SectionHeader(title: 'Visit Summary'),
                        const SizedBox(height: 12),
                        _CompletedDcrCard(visit: v),
                        if (v.aiSummary != null) ...[
                          const SizedBox(height: 16),
                          Text('AI Summary', style: Theme.of(context).textTheme.titleSmall),
                          const SizedBox(height: 6),
                          Card(child: Padding(padding: const EdgeInsets.all(12), child: Text(v.aiSummary!))),
                        ],
                      ],

                      if (v.durationMinutes != null) ...[
                        const SizedBox(height: 12),
                        Text('Duration: ${v.durationMinutes} minutes',
                            style: const TextStyle(color: Color(0xFF64748B))),
                      ],
                    ],
                  ),
                ),
    );
  }
}

// ── Section header ────────────────────────────────────────────────────────────

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.title});
  final String title;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Text(title,
            style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold)),
        const SizedBox(width: 8),
        const Expanded(child: Divider()),
      ],
    );
  }
}

// ── DCR tile wrapper ──────────────────────────────────────────────────────────

class _DcrTile extends StatelessWidget {
  const _DcrTile({required this.icon, required this.label, required this.child});
  final IconData icon;
  final String label;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: AppTheme.primary),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label,
                    style: const TextStyle(
                        fontSize: 12,
                        color: Color(0xFF64748B),
                        fontWeight: FontWeight.w500)),
                const SizedBox(height: 4),
                child,
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── Signature pad ─────────────────────────────────────────────────────────────

class _SignaturePad extends StatelessWidget {
  const _SignaturePad({
    required this.points,
    required this.hasSignature,
    required this.onChanged,
  });

  final List<Offset?> points;
  final bool hasSignature;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 140,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: hasSignature ? AppTheme.primary : const Color(0xFFCBD5E1),
          width: hasSignature ? 1.5 : 1,
        ),
      ),
      child: Stack(
        children: [
          GestureDetector(
            onPanUpdate: (d) {
              points.add(d.localPosition);
              onChanged(true);
            },
            onPanEnd: (_) => points.add(null),
            child: CustomPaint(
              painter: _SignaturePainter(points),
              child: const SizedBox.expand(),
            ),
          ),
          if (!hasSignature)
            const Center(
              child: Text(
                'Doctor signs here',
                style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 13),
              ),
            ),
          if (hasSignature)
            Positioned(
              top: 6,
              right: 6,
              child: GestureDetector(
                onTap: () {
                  points.clear();
                  onChanged(false);
                },
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Icon(Icons.refresh, size: 16, color: Colors.grey),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _SignaturePainter extends CustomPainter {
  _SignaturePainter(this.points);
  final List<Offset?> points;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.black87
      ..strokeWidth = 2.0
      ..strokeCap = StrokeCap.round;
    for (int i = 0; i < points.length - 1; i++) {
      final p1 = points[i];
      final p2 = points[i + 1];
      if (p1 != null && p2 != null) {
        canvas.drawLine(p1, p2, paint);
      }
    }
  }

  @override
  bool shouldRepaint(_SignaturePainter old) => true;
}

// ── Product picker bottom sheet ───────────────────────────────────────────────

class _ProductPickerSheet extends StatefulWidget {
  const _ProductPickerSheet({
    required this.products,
    required this.selected,
    required this.onDone,
  });
  final List<Product> products;
  final List<String> selected;
  final ValueChanged<List<String>> onDone;

  @override
  State<_ProductPickerSheet> createState() => _ProductPickerSheetState();
}

class _ProductPickerSheetState extends State<_ProductPickerSheet> {
  late List<String> _selected;
  final _searchCtrl = TextEditingController();
  List<Product> _filtered = [];

  @override
  void initState() {
    super.initState();
    _selected = List.from(widget.selected);
    _filtered = widget.products;
    _searchCtrl.addListener(_filter);
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void _filter() {
    final q = _searchCtrl.text.toLowerCase();
    setState(() {
      _filtered = q.isEmpty
          ? widget.products
          : widget.products.where((p) => p.name.toLowerCase().contains(q)).toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height * 0.65,
      padding: const EdgeInsets.only(top: 12),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      child: Column(
        children: [
          Container(
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 10),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                const Expanded(
                  child: Text(
                    'Select products promoted',
                    style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
                  ),
                ),
                TextButton(
                  onPressed: () {
                    widget.onDone(_selected);
                    Navigator.pop(context);
                  },
                  child: const Text('Done'),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: TextField(
              controller: _searchCtrl,
              decoration: InputDecoration(
                hintText: 'Search products...',
                prefixIcon: const Icon(Icons.search, size: 18),
                isDense: true,
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: _filtered.isEmpty
                ? const Center(
                    child: Text('No products', style: TextStyle(color: Colors.grey)),
                  )
                : ListView.separated(
                    itemCount: _filtered.length,
                    separatorBuilder: (_, __) => const Divider(height: 1, indent: 56),
                    itemBuilder: (_, i) {
                      final p = _filtered[i];
                      final isSelected = _selected.contains(p.name);
                      return ListTile(
                        dense: true,
                        title: Text(p.name, style: const TextStyle(fontSize: 14)),
                        subtitle: p.brand != null
                            ? Text(p.brand!, style: const TextStyle(fontSize: 12))
                            : null,
                        trailing: isSelected
                            ? const Icon(Icons.check_circle, color: AppTheme.primary)
                            : const Icon(Icons.circle_outlined, color: Colors.grey),
                        onTap: () => setState(() {
                          if (isSelected) {
                            _selected.remove(p.name);
                          } else {
                            _selected.add(p.name);
                          }
                        }),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

// ── Completed DCR summary card ────────────────────────────────────────────────

class _CompletedDcrCard extends StatelessWidget {
  const _CompletedDcrCard({required this.visit});
  final Visit visit;

  @override
  Widget build(BuildContext context) {
    final rows = <Widget>[];

    if (visit.productsPromoted.isNotEmpty) {
      rows.add(_Row(
        label: 'Products promoted',
        child: Wrap(
          spacing: 6,
          runSpacing: 4,
          children: visit.productsPromoted
              .map((p) => Chip(
                    label: Text(p, style: const TextStyle(fontSize: 11)),
                    visualDensity: VisualDensity.compact,
                    backgroundColor: const Color(0xFFEDE9FE),
                  ))
              .toList(),
        ),
      ));
    }

    if (visit.samplesGiven != null) {
      rows.add(_Row(
        label: 'Samples given',
        child: Text('${visit.samplesGiven} units'),
      ));
    }

    if (visit.followUpDate != null) {
      String label;
      try {
        label = DateFormat('dd MMM yyyy').format(DateTime.parse(visit.followUpDate!));
      } catch (_) {
        label = visit.followUpDate!;
      }
      rows.add(_Row(
        label: 'Follow-up',
        child: Text(label, style: const TextStyle(color: Colors.orange)),
      ));
    }

    if (visit.signatureUrl != null) {
      rows.add(_Row(
        label: 'Doctor signature',
        child: Row(
          children: [
            const Icon(Icons.draw, size: 16, color: Colors.green),
            const SizedBox(width: 4),
            const Text('Signed', style: TextStyle(color: Colors.green)),
          ],
        ),
      ));
    }

    if (rows.isEmpty) {
      return const SizedBox.shrink();
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(children: rows),
      ),
    );
  }
}

class _Row extends StatelessWidget {
  const _Row({required this.label, required this.child});
  final String label;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 130,
            child: Text(label,
                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
          ),
          Expanded(child: child),
        ],
      ),
    );
  }
}

// ── Status badge ──────────────────────────────────────────────────────────────

class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    Color color;
    switch (status) {
      case 'in_progress':
        color = Colors.orange;
      case 'completed':
        color = Colors.green;
      default:
        color = Colors.blue;
    }
    return Align(
      alignment: Alignment.centerLeft,
      child: Chip(
        label: Text(status.replaceAll('_', ' ').toUpperCase()),
        backgroundColor: color.withValues(alpha: 0.15),
        labelStyle: TextStyle(color: color, fontWeight: FontWeight.bold),
      ),
    );
  }
}
