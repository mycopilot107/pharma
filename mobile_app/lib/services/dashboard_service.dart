import '../core/api/api_client.dart';
import '../models/dashboard_data.dart';

class DashboardService {
  DashboardService(this._api);

  final ApiClient _api;

  Future<DashboardData> fetch() async {
    final data = await _api.get('/dashboard');
    return DashboardData.fromJson(data as Map<String, dynamic>);
  }
}
