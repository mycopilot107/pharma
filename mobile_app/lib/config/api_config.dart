/// API base URL — change for your environment.
/// Android emulator: http://10.0.2.2:8000/api/v1
/// iOS simulator: http://127.0.0.1:8000/api/v1
/// Physical device: http://YOUR_LAN_IP:8000/api/v1
class ApiConfig {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    //defaultValue: 'http://10.0.2.2:8000/api/v1',
    defaultValue: 'https://solapur.fleetprowms.com/log/api/v1',
  );
}
