import '../core/api/api_client.dart';
import '../models/order.dart';

class OrderService {
  OrderService(this._api);

  final ApiClient _api;

  Future<Map<String, dynamic>> list({String? status}) async {
    final query = status != null ? {'status': status} : null;
    final raw =
        await _api.get('/orders', query: query) as Map<String, dynamic>;
    final items = (raw['data'] as List?) ?? [];
    return {
      'orders': items
          .map((e) => Order.fromJson(e as Map<String, dynamic>))
          .toList(),
      'summary': raw['summary'] as Map<String, dynamic>? ?? {},
    };
  }

  Future<Order> show(int id) async {
    final data = await _api.get('/orders/$id');
    return Order.fromJson(data as Map<String, dynamic>);
  }

  Future<Order> create({
    required int customerId,
    required String orderDate,
    required List<Map<String, dynamic>> items,
    String? notes,
    int? visitId,
  }) async {
    final data = await _api.post('/orders', body: {
      'customer_id': customerId,
      'order_date': orderDate,
      'items': items,
      if (notes != null && notes.isNotEmpty) 'notes': notes,
      if (visitId != null) 'visit_id': visitId,
    });
    return Order.fromJson(data as Map<String, dynamic>);
  }

  Future<void> cancel(int id) async {
    await _api.delete('/orders/$id');
  }
}
