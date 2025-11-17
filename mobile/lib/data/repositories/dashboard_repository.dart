import '../../core/network/api_client.dart';

class DashboardRepository {
  final ApiClient _apiClient = ApiClient();

  Future<List<dynamic>> transactions() async {
    return await _apiClient.get('transactions');
  }

  Future<Map<String, dynamic>> createTransaction(Map<String, dynamic> transaction) async {
    return await _apiClient.post('transactions', transaction);
  }

  Future<List<dynamic>> getCategories() async {
    return await _apiClient.get('categories');
  }

}
