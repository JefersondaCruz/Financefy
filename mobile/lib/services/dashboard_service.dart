import '../data/repositories/dashboard_repository.dart';

class DashboardService {
  final DashboardRepository _dashboardRepository = DashboardRepository();

  Future<List<dynamic>> transactions() async {
    return await _dashboardRepository.transactions();
  }

  Future<Map<String, dynamic>> createTransaction(Map<String, dynamic> transaction) async {
    return await _dashboardRepository.createTransaction(transaction);
  }

  Future<List<dynamic>> getCategories() async {
    return await _dashboardRepository.getCategories();
  }
}
