import 'package:flutter/material.dart';
import 'package:finance_ap/views/pages/dashboard_page.dart';
import 'package:finance_ap/services/auth_service.dart';
import 'package:finance_ap/core/exceptions/api_exception.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:finance_ap/utils/validators.dart';

class LoginPage extends StatefulWidget {
  final VoidCallback onRegisterClicked;

  const LoginPage({super.key, required this.onRegisterClicked});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _authService = AuthService();
  final _formKey = GlobalKey<FormState>();

  bool _isLoading = false;

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final response = await _authService.login(
        _emailController.text.trim(),
        _passwordController.text.trim(),
      );

      final token = response['data']['token'] as String;

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', token);

      if (mounted) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const DashboardPage()),
        );
      }
    } on ApiException catch (e) {
      _showError(e.message);
    } catch (e) {
      _showError('Erro inesperado. Tente novamente.');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.redAccent),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1E1E2C),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(32),
          child: Form(
            key: _formKey,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text(
                  'Financefy',
                  style: TextStyle(
                    color: Colors.greenAccent,
                    fontSize: 36,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 40),

                TextFormField(
                  controller: _emailController,
                  validator: Validators.requiredField,
                  decoration: _input('Email'),
                  style: const TextStyle(color: Colors.white),
                ),
                const SizedBox(height: 16),

                TextFormField(
                  controller: _passwordController,
                  obscureText: true,
                  validator: Validators.requiredField,
                  decoration: _input('Senha'),
                  style: const TextStyle(color: Colors.white),
                ),
                const SizedBox(height: 24),

                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _login,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.greenAccent,
                      foregroundColor: Colors.black,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _isLoading
                        ? const CircularProgressIndicator(color: Colors.black)
                        : const Text('Entrar'),
                  ),
                ),
                const SizedBox(height: 16),

                TextButton(
                  onPressed: widget.onRegisterClicked,
                  child: const Text(
                    'Não tem conta? Cadastre-se',
                    style: TextStyle(color: Colors.white70),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  InputDecoration _input(String label) {
    return InputDecoration(
      labelText: label,
      filled: true,
      fillColor: Colors.white12,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
      labelStyle: const TextStyle(color: Colors.white70),
    );
  }
}
