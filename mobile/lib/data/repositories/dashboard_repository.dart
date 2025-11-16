import '../../core/network/api_client.dart';

class DashboardRepository {
  final ApiClient _apiClient = ApiClient();

  Future<Map<String, dynamic>> transactions() async {
    return await _apiClient.get('/transactions');
  }

}
