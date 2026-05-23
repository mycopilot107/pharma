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
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _submitExpense() async {
    final amountCtrl = TextEditingController();
    String type = 'fuel';
    String? receiptPath;

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          title: const Text('Submit expense'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                DropdownButtonFormField<String>(
                  initialValue: type,
                  items: const [
                    DropdownMenuItem(value: 'fuel', child: Text('Fuel')),
                    DropdownMenuItem(value: 'hotel', child: Text('Hotel')),
                    DropdownMenuItem(value: 'food', child: Text('Food')),
                  ],
                  onChanged: (v) => setDialogState(() => type = v ?? 'fuel'),
                  decoration: const InputDecoration(labelText: 'Type'),
                ),
                TextField(
                  controller: amountCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'Amount'),
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
                  icon: const Icon(Icons.receipt),
                  label: Text(receiptPath == null
                      ? 'Attach receipt'
                      : 'Receipt attached'),
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
                child: const Text('Submit')),
          ],
        ),
      ),
    );

    if (ok != true || amountCtrl.text.isEmpty || receiptPath == null) return;

    try {
      await context.read<AppState>().expenses.create(
            type: type,
            amount: double.parse(amountCtrl.text),
            expenseDate: DateFormat('yyyy-MM-dd').format(DateTime.now()),
            receiptPath: receiptPath!,
          );
      await _load();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
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
        onPressed: _submitExpense,
        icon: const Icon(Icons.add),
        label: const Text('Submit'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: _expenses.isEmpty
                  ? const ListTile(title: Text('No expenses yet'))
                  : ListView.builder(
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
                                '${e.type.toUpperCase()} · ${e.currency} ${e.amount.toStringAsFixed(2)}'),
                            subtitle: Text('${e.expenseDate} · ${e.status}'),
                            trailing: Chip(
                              label: Text(e.status,
                                  style: const TextStyle(fontSize: 11)),
                              backgroundColor: _statusColor(e.status)
                                  .withValues(alpha: 0.15),
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
