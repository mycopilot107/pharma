import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme/app_theme.dart';
import '../models/product.dart';
import '../providers/app_state.dart';

class ProductsScreen extends StatefulWidget {
  const ProductsScreen({super.key});

  @override
  State<ProductsScreen> createState() => _ProductsScreenState();
}

class _ProductsScreenState extends State<ProductsScreen> {
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
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _showDetail(Product p) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => Padding(
        padding: const EdgeInsets.fromLTRB(24, 24, 24, 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(p.label,
                style: const TextStyle(
                    fontSize: 18, fontWeight: FontWeight.bold)),
            if (p.brand != null) ...[
              const SizedBox(height: 4),
              Text(p.brand!,
                  style: const TextStyle(color: Color(0xFF64748B))),
            ],
            const Divider(height: 24),
            if (p.sku != null) _detailRow('SKU', p.sku!),
            if (p.category != null) _detailRow('Category', p.category!),
            if (p.strength != null) _detailRow('Strength', p.strength!),
            if (p.packSize != null) _detailRow('Pack Size', p.packSize!),
            _detailRow(
                'Unit Price', '₹ ${p.unitPrice.toStringAsFixed(2)}'),
            if (p.mrp != null)
              _detailRow('MRP', '₹ ${p.mrp!.toStringAsFixed(2)}'),
            if (p.description != null) ...[
              const SizedBox(height: 12),
              Text(p.description!,
                  style: const TextStyle(color: Color(0xFF64748B))),
            ],
          ],
        ),
      ),
    );
  }

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(label,
                style: const TextStyle(
                    color: Color(0xFF94A3B8), fontSize: 13)),
          ),
          Expanded(
              child: Text(value,
                  style: const TextStyle(fontWeight: FontWeight.w500))),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Products'),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(56),
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
            child: TextField(
              controller: _searchCtrl,
              decoration: InputDecoration(
                hintText: 'Search by name, brand or SKU…',
                prefixIcon: const Icon(Icons.search),
                contentPadding:
                    const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                suffixIcon: _searchCtrl.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchCtrl.clear();
                          _load();
                        },
                      )
                    : null,
              ),
              onSubmitted: (v) => _load(search: v.trim()),
              textInputAction: TextInputAction.search,
            ),
          ),
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: _products.isEmpty
                  ? const Center(
                      child: Text('No products found',
                          style: TextStyle(color: Color(0xFF94A3B8))),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(8),
                      itemCount: _products.length,
                      itemBuilder: (context, i) {
                        final p = _products[i];
                        return Card(
                          margin: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor:
                                  AppTheme.primary.withValues(alpha: 0.1),
                              child: const Icon(Icons.medication,
                                  color: AppTheme.primary, size: 20),
                            ),
                            title: Text(p.label,
                                style: const TextStyle(
                                    fontWeight: FontWeight.w600)),
                            subtitle: Text([
                              if (p.brand != null) p.brand!,
                              if (p.sku != null) p.sku!,
                            ].join(' · ')),
                            trailing: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  '₹ ${p.unitPrice.toStringAsFixed(0)}',
                                  style: const TextStyle(
                                      fontWeight: FontWeight.bold),
                                ),
                                if (p.category != null)
                                  Text(p.category!,
                                      style: const TextStyle(
                                          fontSize: 11,
                                          color: Color(0xFF94A3B8))),
                              ],
                            ),
                            onTap: () => _showDetail(p),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
