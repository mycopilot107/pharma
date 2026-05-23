import 'package:http/http.dart' as http;

import '../core/api/api_client.dart';
import '../models/visit.dart';

class VisitService {
  VisitService(this._api);

  final ApiClient _api;

  Future<List<Visit>> list({String? date, String? status}) async {
    final query = <String, String>{};
    if (date != null) query['date'] = date;
    if (status != null) query['status'] = status;
    final data = await _api.get('/visits', query: query.isEmpty ? null : query);
    final items = (data['data'] as List?) ?? (data as List? ?? []);
    return items.map((e) => Visit.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<Visit> get(int id) async {
    final data = await _api.get('/visits/$id');
    return Visit.fromJson(data as Map<String, dynamic>);
  }

  Future<Visit> create(Map<String, dynamic> body) async {
    final data = await _api.post('/visits', body: body);
    return Visit.fromJson(data as Map<String, dynamic>);
  }

  Future<Visit> checkIn(int id, double lat, double lng) async {
    final data = await _api.post('/visits/$id/check-in', body: {
      'latitude': lat,
      'longitude': lng,
    });
    return Visit.fromJson(data as Map<String, dynamic>);
  }

  Future<Visit> checkOut(int id, double lat, double lng, {String? notes}) async {
    final data = await _api.post('/visits/$id/check-out', body: {
      'latitude': lat,
      'longitude': lng,
      if (notes != null) 'notes': notes,
    });
    return Visit.fromJson(data as Map<String, dynamic>);
  }

  Future<void> uploadPhotos(int id, List<String> filePaths) async {
    final files = <http.MultipartFile>[];
    for (var i = 0; i < filePaths.length; i++) {
      files.add(await http.MultipartFile.fromPath('photos[$i]', filePaths[i]));
    }
    await _api.postMultipart('/visits/$id/photos', fields: {}, files: files);
  }
}
