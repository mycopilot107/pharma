import '../core/api/api_client.dart';
import '../core/storage/token_storage.dart';
import '../models/user.dart';

class AuthService {
  AuthService(this._api, this._storage);

  final ApiClient _api;
  final TokenStorage _storage;

  Future<User> login(String email, String password) async {
    final data = await _api.post('/login', body: {
      'email': email,
      'password': password,
      'device_name': 'MedRep Fleet Mobile',
    });
    final token = data['token'] as String;
    await _storage.saveToken(token);
    return User.fromJson(data['user'] as Map<String, dynamic>);
  }

  Future<User> me() async {
    final data = await _api.get('/me');
    return User.fromJson(data as Map<String, dynamic>);
  }

  Future<void> logout() async {
    try {
      await _api.post('/logout');
    } catch (_) {}
    await _storage.clear();
  }

  Future<bool> hasToken() async {
    final t = await _storage.getToken();
    return t != null && t.isNotEmpty;
  }
}
