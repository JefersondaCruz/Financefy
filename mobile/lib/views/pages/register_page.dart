import 'package:flutter/material.dart';
import 'package:finance_ap/services/auth_service.dart';
import 'package:finance_ap/core/exceptions/api_exception.dart';
import 'package:flutter_masked_text2/flutter_masked_text2.dart';
import 'package:finance_ap/utils/validators.dart';

class RegisterPage extends StatefulWidget {
  final VoidCallback onLoginClicked;

  const RegisterPage({super.key, required this.onLoginClicked});

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final _formKey = GlobalKey<FormState>();

  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = MaskedTextController(mask: '(00) 00000-0000');
  final _passwordController = TextEditingController();

  final _authService = AuthService();
  bool _isLoading = false;

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      await _authService.register(
        _nameController.text.trim(),
        _emailController.text.trim(),
        _passwordController.text.trim(),
        _phoneController.text.trim(),
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Conta criada com sucesso! Faça login.'),
            backgroundColor: Colors.greenAccent,
          ),
        );
        widget.onLoginClicked();
      }
    } on ApiException catch (e) {
      _showError(e.message);
    } catch (_) {
      _showError("Erro inesperado. Tente novamente.");
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.redAccent,
      ),
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
                  'Crie sua conta',
                  style: TextStyle(
                    color: Colors.greenAccent,
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 40),

                TextFormField(
                  controller: _nameController,
                  validator: Validators.requiredField,
                  decoration: _input('Nome completo'),
                  style: const TextStyle(color: Colors.white),
                ),
                const SizedBox(height: 16),

                TextFormField(
                  controller: _emailController,
                  validator: Validators.email,
                  decoration: _input('Email'),
                  style: const TextStyle(color: Colors.white),
                  keyboardType: TextInputType.emailAddress,
                ),
                const SizedBox(height: 16),

                TextFormField(
                  controller: _phoneController,
                  validator: Validators.phone,
                  keyboardType: TextInputType.phone,
                  decoration: _input('Telefone'),
                  style: const TextStyle(color: Colors.white),
                ),
                const SizedBox(height: 16),

                TextFormField(
                  controller: _passwordController,
                  obscureText: true,
                  validator: Validators.password,
                  decoration: _input('Senha'),
                  style: const TextStyle(color: Colors.white),
                ),
                const SizedBox(height: 24),

                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _register,
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
                        : const Text('Registrar'),
                  ),
                ),

                const SizedBox(height: 16),

                TextButton(
                  onPressed: widget.onLoginClicked,
                  child: const Text(
                    'Já tem conta? Faça login',
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
