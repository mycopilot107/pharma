import '../core/api/api_client.dart';
import '../models/leave.dart';

class LeaveService {
  LeaveService(this._api);

  final ApiClient _api;

  Future<Map<String, dynamic>> list({String? status}) async {
    final query = status != null ? {'status': status} : null;
    final raw =
        await _api.get('/leaves', query: query) as Map<String, dynamic>;
    final items = (raw['data'] as List?) ?? [];
    return {
      'leaves': items
          .map((e) => Leave.fromJson(e as Map<String, dynamic>))
          .toList(),
      'balance': raw['balance'] as Map<String, dynamic>? ?? {},
      'on_leave_today': raw['on_leave_today'] as bool? ?? false,
    };
  }

  Future<Map<String, dynamic>> balance() async {
    return await _api.get('/leaves/balance') as Map<String, dynamic>;
  }

  Future<Leave> create({
    required String leaveType,
    required String startDate,
    required String endDate,
    required String reason,
    bool isHalfDay = false,
    String? halfDayPeriod,
  }) async {
    final data = await _api.post('/leaves', body: {
      'leave_type': leaveType,
      'start_date': startDate,
      'end_date': endDate,
      'reason': reason,
      'is_half_day': isHalfDay,
      if (halfDayPeriod != null) 'half_day_period': halfDayPeriod,
    });
    return Leave.fromJson(data as Map<String, dynamic>);
  }

  Future<Leave> show(int id) async {
    final data = await _api.get('/leaves/$id');
    return Leave.fromJson(data as Map<String, dynamic>);
  }

  Future<Leave> cancel(int id) async {
    final data = await _api.delete('/leaves/$id');
    return Leave.fromJson(data as Map<String, dynamic>);
  }
}
