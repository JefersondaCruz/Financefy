import '../data/repositories/auth_repository.dart';

class AuthService {
  final AuthRepository _authRepository = AuthRepository();

  Future<Map<String, dynamic>>login(String email, String password) async {
    return await _authRepository.login(email, password);
  }

  Future<void> register(String name, String email, String password, String phone) async {
    await _authRepository.register(name, email, password, phone);
  }
}
