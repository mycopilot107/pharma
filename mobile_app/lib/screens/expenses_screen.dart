import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../core/theme/app_theme.dart';
import '../models/expense.dart';
import '../providers/app_state.dart';

class ExpensesScreen extends StatefulWidget {
  const ExpensesScreen({super.key});

  @override
  State<ExpensesScreen> createState() => _ExpensesScreenState();
}

class _ExpensesScreenState extends State<ExpensesScreen> {
  List<Expense> _expenses = [];
  bool _loading = true;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final list = await context.read<AppState>().expenses.list();
      if (mounted) setState(() => _expenses = list);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _submitExpense() async {
    if (_submitting) return;

    final amountCtrl = TextEditingController();
    final descCtrl = TextEditingController();
    String type = 'fuel';
    String? receiptPath;
    DateTime selectedDate = DateTime.now();

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          title: const Text('Add Expense'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                DropdownButtonFormField<String>(
                  value: type,
                  items: const [
                    DropdownMenuItem(value: 'fuel',  child: Text('Fuel')),
                    DropdownMenuItem(value: 'hotel', child: Text('Hotel')),
                    DropdownMenuItem(value: 'food',  child: Text('Food')),
                  ],
                  onChanged: (v) => setDialogState(() => type = v ?? 'fuel'),
                  decoration: const InputDecoration(labelText: 'Type'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: amountCtrl,
                  keyboardType:
                      const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(
                    labelText: 'Amount *',
                    prefixText: '₹ ',
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: descCtrl,
                  maxLines: 2,
                  decoration: const InputDecoration(
                    labelText: 'Description',
                    hintText: 'Optional notes',
                  ),
                ),
                const SizedBox(height: 12),
                InkWell(
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: ctx,
                      initialDate: selectedDate,
                      firstDate: DateTime(2024),
                      lastDate: DateTime.now(),
                    );
                    if (picked != null) {
                      setDialogState(() => selectedDate = picked);
                    }
                  },
                  child: InputDecorator(
                    decoration: const InputDecoration(
                      labelText: 'Date',
                      suffixIcon: Icon(Icons.calendar_today, size: 18),
                    ),
                    child: Text(
                      DateFormat('dd MMM yyyy').format(selectedDate),
                      style: const TextStyle(fontSize: 14),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: () async {
                    final file = await ImagePicker()
                        .pickImage(source: ImageSource.gallery);
                    if (file != null) {
                      setDialogState(() => receiptPath = file.path);
                    }
                  },
                  icon: Icon(
                    receiptPath == null
                        ? Icons.upload_file
                        : Icons.check_circle,
                    color: receiptPath == null ? null : Colors.green,
                  ),
                  label: Text(
                    receiptPath == null
                        ? 'Attach receipt (optional)'
                        : 'Receipt attached ✓',
                    style: TextStyle(
                      color: receiptPath == null ? null : Colors.green,
                    ),
                  ),
                  style: receiptPath == null
                      ? null
                      : OutlinedButton.styleFrom(
                          side: const BorderSide(color: Colors.green),
                        ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Cancel'),
            ),
            FilledButton(
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Add'),
            ),
          ],
        ),
      ),
    );

    if (ok != true) return;

    final rawAmount = amountCtrl.text.trim().replaceAll(',', '.');
    if (rawAmount.isEmpty) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please enter an amount')),
        );
      }
      return;
    }

    final amount = double.tryParse(rawAmount);
    if (amount == null || amount <= 0) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please enter a valid amount')),
        );
      }
      return;
    }

    setState(() => _submitting = true);
    try {
      await context.read<AppState>().expenses.create(
            type: type,
            amount: amount,
            expenseDate: DateFormat('yyyy-MM-dd').format(selectedDate),
            description: descCtrl.text.trim().isEmpty
                ? null
                : descCtrl.text.trim(),
            receiptPath: receiptPath,
          );
      await _load();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Expense added successfully')),
        );
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

  Color _statusColor(String status) {
    switch (status) {
      case 'approved':
        return Colors.green;
      case 'rejected':
        return Colors.red;
      default:
        return Colors.orange;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Expenses')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _submitting ? null : _submitExpense,
        icon: _submitting
            ? const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(
                    strokeWidth: 2, color: Colors.white),
              )
            : const Icon(Icons.add),
        label: const Text('Add Expense'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: _expenses.isEmpty
                  ? const Center(
                      child: Padding(
                        padding: EdgeInsets.all(32),
                        child: Text(
                          'No expenses yet',
                          style: TextStyle(color: Color(0xFF94A3B8)),
                        ),
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.only(bottom: 90),
                      itemCount: _expenses.length,
                      itemBuilder: (context, i) {
                        final e = _expenses[i];
                        return Card(
                          margin: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 4),
                          child: ListTile(
                            leading: const Icon(Icons.receipt,
                                color: AppTheme.primary),
                            title: Text(
                              '${e.type.toUpperCase()} · ${e.currency} ${e.amount.toStringAsFixed(2)}',
                            ),
                            subtitle: Text(
                              [
                                e.expenseDate,
                                if (e.description != null) e.description!,
                              ].join(' · '),
                            ),
                            trailing: Chip(
                              label: Text(
                                e.status,
                                style: const TextStyle(fontSize: 11),
                              ),
                              backgroundColor:
                                  _statusColor(e.status).withValues(alpha: 0.15),
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
