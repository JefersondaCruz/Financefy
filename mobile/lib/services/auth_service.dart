import '../data/repositories/auth_repository.dart';

class AuthService {
  final AuthRepository _authRepository = AuthRepository();

  Future<void> login(String email, String password) async {
    final response = await _authRepository.login(email, password);
    print(response);
  }

  Future<void> register(String name, String email, String password) async {
    final response = await _authRepository.register(name, email, password);
    print(response);
  }
}
