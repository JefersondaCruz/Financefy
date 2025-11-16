import '../data/repositories/dashboard_repository.dart';

class DashboardService {
  final DashboardRepository _dashboardRepository = DashboardRepository();

  Future<Map<String, dynamic>> transactions() async {
    return await _dashboardRepository.transactions();
  }
}
