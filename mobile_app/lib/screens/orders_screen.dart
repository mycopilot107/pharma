import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../core/theme/app_theme.dart';
import '../models/customer.dart';
import '../models/order.dart';
import '../models/product.dart';
import '../providers/app_state.dart';

// ─── Main list screen ─────────────────────────────────────────────────────────

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  List<Order> _orders = [];
  Map<String, dynamic> _summary = {};
  bool _loading = true;
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
          .orders
          .list(status: _statusFilter);
      if (mounted) {
        setState(() {
          _orders = result['orders'] as List<Order>;
          _summary = result['summary'] as Map<String, dynamic>;
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

  Future<void> _cancelOrder(Order order) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel order?'),
        content:
            Text('Order ${order.orderNumber} will be cancelled.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('No')),
          FilledButton(
            style:
                FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Cancel order'),
          ),
        ],
      ),
    );
    if (confirm != true) return;
    try {
      await context.read<AppState>().orders.cancel(order.id);
      await _load();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Order cancelled')));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    }
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'confirmed':
        return Colors.green;
      case 'delivered':
        return Colors.blue;
      case 'cancelled':
        return Colors.grey;
      default:
        return Colors.orange;
    }
  }

  @override
  Widget build(BuildContext context) {
    final total = _summary['total'] ?? 0;
    final pending = _summary['pending'] ?? 0;
    final confirmedValue =
        (_summary['confirmed_value'] as num?)?.toDouble() ?? 0;

    return Scaffold(
      appBar: AppBar(title: const Text('Orders')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          await Navigator.push(
            context,
            MaterialPageRoute(
                builder: (_) => const _OrderCreatePage()),
          );
          _load();
        },
        icon: const Icon(Icons.add),
        label: const Text('New Order'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: Column(
                children: [
                  // Summary cards
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      children: [
                        _SummaryCard(
                            label: 'Total',
                            value: '$total',
                            color: AppTheme.primary),
                        const SizedBox(width: 8),
                        _SummaryCard(
                            label: 'Pending',
                            value: '$pending',
                            color: Colors.orange),
                        const SizedBox(width: 8),
                        _SummaryCard(
                          label: 'Confirmed',
                          value:
                              '₹${confirmedValue.toStringAsFixed(0)}',
                          color: Colors.green,
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
                          ['confirmed', 'Confirmed'],
                          ['delivered', 'Delivered'],
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
                  // Orders list
                  Expanded(
                    child: _orders.isEmpty
                        ? const Center(
                            child: Text('No orders yet',
                                style: TextStyle(
                                    color: Color(0xFF94A3B8))),
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.fromLTRB(
                                16, 0, 16, 90),
                            itemCount: _orders.length,
                            itemBuilder: (context, i) {
                              final o = _orders[i];
                              return Card(
                                margin: const EdgeInsets.only(
                                    bottom: 8),
                                child: ListTile(
                                  contentPadding:
                                      const EdgeInsets.symmetric(
                                          horizontal: 16,
                                          vertical: 8),
                                  leading: CircleAvatar(
                                    backgroundColor:
                                        _statusColor(o.status)
                                            .withValues(alpha: 0.1),
                                    child: Icon(
                                        Icons.receipt_long,
                                        color:
                                            _statusColor(o.status),
                                        size: 20),
                                  ),
                                  title: Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          o.customerName ??
                                              'Unknown customer',
                                          style: const TextStyle(
                                              fontWeight:
                                                  FontWeight.w600),
                                        ),
                                      ),
                                      Chip(
                                        label: Text(
                                            o.statusLabel ??
                                                o.status,
                                            style: const TextStyle(
                                                fontSize: 11)),
                                        backgroundColor:
                                            _statusColor(o.status)
                                                .withValues(
                                                    alpha: 0.1),
                                        padding: EdgeInsets.zero,
                                        visualDensity:
                                            VisualDensity.compact,
                                      ),
                                    ],
                                  ),
                                  subtitle: Text(
                                    '${o.orderNumber} · ${o.orderDate} · ${o.currency} ${o.totalAmount.toStringAsFixed(2)}',
                                  ),
                                  trailing: o.isPending
                                      ? IconButton(
                                          icon: const Icon(
                                              Icons.cancel_outlined,
                                              color: Colors.red),
                                          onPressed: () =>
                                              _cancelOrder(o),
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

class _SummaryCard extends StatelessWidget {
  const _SummaryCard(
      {required this.label,
      required this.value,
      required this.color});
  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding:
            const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(12),
          border:
              Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label,
                style: TextStyle(fontSize: 11, color: color)),
            Text(value,
                style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: color)),
          ],
        ),
      ),
    );
  }
}

// ─── Create order page ────────────────────────────────────────────────────────

class _CartItem {
  _CartItem({required this.product, this.quantity = 1});
  final Product product;
  int quantity;
}

class _OrderCreatePage extends StatefulWidget {
  const _OrderCreatePage();

  @override
  State<_OrderCreatePage> createState() => _OrderCreatePageState();
}

class _OrderCreatePageState extends State<_OrderCreatePage> {
  Customer? _customer;
  DateTime _orderDate = DateTime.now();
  final _notesCtrl = TextEditingController();
  final List<_CartItem> _cart = [];
  bool _submitting = false;

  @override
  void dispose() {
    _notesCtrl.dispose();
    super.dispose();
  }

  double get _total =>
      _cart.fold(0, (sum, i) => sum + i.product.unitPrice * i.quantity);

  Future<void> _pickCustomer() async {
    final picked = await showDialog<Customer>(
      context: context,
      builder: (_) => const _CustomerPickerDialog(),
    );
    if (picked != null) setState(() => _customer = picked);
  }

  Future<void> _pickProduct() async {
    final picked = await showDialog<Product>(
      context: context,
      builder: (_) => const _ProductPickerDialog(),
    );
    if (picked == null) return;
    setState(() {
      final idx = _cart.indexWhere((i) => i.product.id == picked.id);
      if (idx >= 0) {
        _cart[idx].quantity++;
      } else {
        _cart.add(_CartItem(product: picked));
      }
    });
  }

  Future<void> _submit() async {
    if (_customer == null) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please select a customer')));
      return;
    }
    if (_cart.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Add at least one product')));
      return;
    }
    setState(() => _submitting = true);
    try {
      await context.read<AppState>().orders.create(
            customerId: _customer!.id,
            orderDate: DateFormat('yyyy-MM-dd').format(_orderDate),
            items: _cart
                .map((i) => <String, dynamic>{
                      'product_id': i.product.id,
                      'quantity': i.quantity,
                    })
                .toList(),
            notes: _notesCtrl.text.trim().isEmpty
                ? null
                : _notesCtrl.text.trim(),
          );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
                content: Text('Order placed successfully')));
        Navigator.pop(context);
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('New Order')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Customer selection
            Card(
              child: ListTile(
                leading: const Icon(Icons.person,
                    color: AppTheme.primary),
                title: Text(
                    _customer?.name ?? 'Select customer *',
                    style: TextStyle(
                        color: _customer == null
                            ? const Color(0xFF94A3B8)
                            : null,
                        fontWeight: _customer != null
                            ? FontWeight.w600
                            : null)),
                subtitle:
                    _customer != null ? Text(_customer!.type) : null,
                trailing: const Icon(Icons.chevron_right),
                onTap: _pickCustomer,
              ),
            ),
            const SizedBox(height: 8),
            // Date selection
            Card(
              child: ListTile(
                leading: const Icon(Icons.calendar_today,
                    color: AppTheme.primary),
                title: Text(
                    DateFormat('dd MMM yyyy').format(_orderDate),
                    style: const TextStyle(
                        fontWeight: FontWeight.w500)),
                subtitle: const Text('Order date'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () async {
                  final picked = await showDatePicker(
                    context: context,
                    initialDate: _orderDate,
                    firstDate: DateTime(2024),
                    lastDate: DateTime.now(),
                  );
                  if (picked != null) {
                    setState(() => _orderDate = picked);
                  }
                },
              ),
            ),
            const SizedBox(height: 8),
            // Notes
            TextField(
              controller: _notesCtrl,
              maxLines: 2,
              decoration: const InputDecoration(
                labelText: 'Notes (optional)',
                prefixIcon: Icon(Icons.notes),
              ),
            ),
            const SizedBox(height: 20),
            // Products header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Products',
                    style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold)),
                TextButton.icon(
                  onPressed: _pickProduct,
                  icon: const Icon(Icons.add),
                  label: const Text('Add product'),
                ),
              ],
            ),
            // Cart items
            ..._cart.map((item) => Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 8),
                    child: Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment:
                                CrossAxisAlignment.start,
                            children: [
                              Text(item.product.label,
                                  style: const TextStyle(
                                      fontWeight: FontWeight.w600)),
                              Text(
                                '₹ ${item.product.unitPrice.toStringAsFixed(2)} × ${item.quantity} = ₹ ${(item.product.unitPrice * item.quantity).toStringAsFixed(2)}',
                                style: const TextStyle(
                                    fontSize: 12,
                                    color: Color(0xFF64748B)),
                              ),
                            ],
                          ),
                        ),
                        // Quantity stepper
                        Row(
                          children: [
                            IconButton(
                              icon: const Icon(
                                  Icons.remove_circle_outline),
                              iconSize: 20,
                              onPressed: () => setState(() {
                                if (item.quantity > 1) {
                                  item.quantity--;
                                } else {
                                  _cart.remove(item);
                                }
                              }),
                            ),
                            Text('${item.quantity}',
                                style: const TextStyle(
                                    fontWeight: FontWeight.bold)),
                            IconButton(
                              icon: const Icon(
                                  Icons.add_circle_outline),
                              iconSize: 20,
                              onPressed: () =>
                                  setState(() => item.quantity++),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                )),
            if (_cart.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 16),
                child: Center(
                    child: Text('No products added yet',
                        style:
                            TextStyle(color: Color(0xFF94A3B8)))),
              ),
            if (_cart.isNotEmpty) ...[
              const Divider(),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Total',
                      style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16)),
                  Text(
                    '₹ ${_total.toStringAsFixed(2)}',
                    style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        color: AppTheme.primary),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: _submitting ? null : _submit,
              icon: _submitting
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.check),
              label:
                  Text(_submitting ? 'Placing order…' : 'Place Order'),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}

// ─── Customer picker dialog ───────────────────────────────────────────────────

class _CustomerPickerDialog extends StatefulWidget {
  const _CustomerPickerDialog();

  @override
  State<_CustomerPickerDialog> createState() =>
      _CustomerPickerDialogState();
}

class _CustomerPickerDialogState extends State<_CustomerPickerDialog> {
  List<Customer> _customers = [];
  bool _loading = true;
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load({String? search}) async {
    setState(() => _loading = true);
    try {
      final list =
          await context.read<AppState>().customers.list(search: search);
      if (mounted) setState(() => _customers = list);
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: SizedBox(
        height: 420,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: TextField(
                controller: _searchCtrl,
                autofocus: true,
                decoration: const InputDecoration(
                    hintText: 'Search customer…',
                    prefixIcon: Icon(Icons.search)),
                onChanged: (v) => _load(search: v),
              ),
            ),
            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : _customers.isEmpty
                      ? const Center(child: Text('No customers found'))
                      : ListView.builder(
                          itemCount: _customers.length,
                          itemBuilder: (context, i) {
                            final c = _customers[i];
                            return ListTile(
                              title: Text(c.name),
                              subtitle: Text(c.type),
                              onTap: () =>
                                  Navigator.pop(context, c),
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

// ─── Product picker dialog ────────────────────────────────────────────────────

class _ProductPickerDialog extends StatefulWidget {
  const _ProductPickerDialog();

  @override
  State<_ProductPickerDialog> createState() =>
      _ProductPickerDialogState();
}

class _ProductPickerDialogState extends State<_ProductPickerDialog> {
  List<Product> _products = [];
  bool _loading = true;
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load({String? search}) async {
    setState(() => _loading = true);
    try {
      final list =
          await context.read<AppState>().products.list(search: search);
      if (mounted) setState(() => _products = list);
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: SizedBox(
        height: 420,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: TextField(
                controller: _searchCtrl,
                autofocus: true,
                decoration: const InputDecoration(
                    hintText: 'Search product…',
                    prefixIcon: Icon(Icons.search)),
                onChanged: (v) => _load(search: v),
              ),
            ),
            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : _products.isEmpty
                      ? const Center(child: Text('No products found'))
                      : ListView.builder(
                          itemCount: _products.length,
                          itemBuilder: (context, i) {
                            final p = _products[i];
                            return ListTile(
                              title: Text(p.label),
                              subtitle: Text([
                                if (p.brand != null) p.brand!,
                                '₹ ${p.unitPrice.toStringAsFixed(0)}',
                              ].join(' · ')),
                              onTap: () =>
                                  Navigator.pop(context, p),
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
