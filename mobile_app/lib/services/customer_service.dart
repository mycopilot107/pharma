import '../core/api/api_client.dart';
import '../models/customer.dart';

class CustomerService {
  CustomerService(this._api);

  final ApiClient _api;

  Future<List<Customer>> list({String? type, String? search}) async {
    final query = <String, String>{};
    if (type != null) query['type'] = type;
    if (search != null && search.isNotEmpty) query['search'] = search;
    final data = await _api.get('/customers', query: query.isEmpty ? null : query);
    final items = (data['data'] as List?) ?? [];
    return items.map((e) => Customer.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<Customer> create(Map<String, dynamic> body) async {
    final data = await _api.post('/customers', body: body);
    return Customer.fromJson(data as Map<String, dynamic>);
  }

  Future<Map<String, dynamic>> show(int id) async {
    return await _api.get('/customers/$id') as Map<String, dynamic>;
  }
}
