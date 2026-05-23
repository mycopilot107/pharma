import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../core/location/location_helper.dart';
import '../core/theme/app_theme.dart';
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
  final _notesController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
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
        });
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _checkIn() async {
    try {
      final pos = await LocationHelper.getCurrentPosition();
      await context.read<AppState>().visits.checkIn(widget.visitId, pos.latitude, pos.longitude);
      await context.read<AppState>().refreshDashboard();
      await _load();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
    }
  }

  Future<void> _checkOut() async {
    try {
      final pos = await LocationHelper.getCurrentPosition();
      await context.read<AppState>().visits.checkOut(
            widget.visitId,
            pos.latitude,
            pos.longitude,
            notes: _notesController.text.isEmpty ? null : _notesController.text,
          );
      await context.read<AppState>().refreshDashboard();
      if (mounted) Navigator.pop(context);
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
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
                      const SizedBox(height: 16),
                      if (v.address != null) Text(v.address!, style: const TextStyle(color: Color(0xFF64748B))),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _notesController,
                        maxLines: 4,
                        decoration: const InputDecoration(labelText: 'Visit notes'),
                      ),
                      const SizedBox(height: 24),
                      if (v.isPlanned)
                        FilledButton.icon(
                          onPressed: _checkIn,
                          icon: const Icon(Icons.login),
                          label: const Text('Check in (GPS)'),
                          style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                        ),
                      if (v.isInProgress) ...[
                        FilledButton.icon(
                          onPressed: _checkOut,
                          icon: const Icon(Icons.logout),
                          label: const Text('Check out & complete'),
                          style: FilledButton.styleFrom(backgroundColor: Colors.green),
                        ),
                        const SizedBox(height: 12),
                        OutlinedButton.icon(
                          onPressed: _addPhoto,
                          icon: const Icon(Icons.camera_alt),
                          label: const Text('Add photo'),
                        ),
                      ],
                      if (v.isCompleted && v.aiSummary != null) ...[
                        const SizedBox(height: 24),
                        Text('AI Summary', style: Theme.of(context).textTheme.titleMedium),
                        const SizedBox(height: 8),
                        Card(child: Padding(padding: const EdgeInsets.all(12), child: Text(v.aiSummary!))),
                      ],
                      if (v.durationMinutes != null) ...[
                        const SizedBox(height: 12),
                        Text('Duration: ${v.durationMinutes} minutes'),
                      ],
                    ],
                  ),
                ),
    );
  }
}

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
