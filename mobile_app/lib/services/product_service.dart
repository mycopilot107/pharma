import '../core/api/api_client.dart';
import '../models/product.dart';

class ProductService {
  ProductService(this._api);

  final ApiClient _api;

  Future<List<Product>> list({String? search, String? category}) async {
    final query = <String, String>{};
    if (search != null && search.isNotEmpty) query['search'] = search;
    if (category != null) query['category'] = category;
    final data = await _api.get('/products', query: query.isEmpty ? null : query);
    final items = (data['data'] as List?) ?? [];
    return items
        .map((e) => Product.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<Product> show(int id) async {
    final data = await _api.get('/products/$id');
    return Product.fromJson(data as Map<String, dynamic>);
  }
}
