import 'package:flutter/material.dart';
import 'login_page.dart';
import 'register_page.dart';

class AuthWrapper extends StatefulWidget {
  const AuthWrapper({super.key});

  @override
  State<AuthWrapper> createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  bool showLogin = true;

  void toggleView() {
    setState(() => showLogin = !showLogin);
  }

  @override
  Widget build(BuildContext context) {
    return showLogin
        ? LoginPage(onRegisterClicked: toggleView)
        : RegisterPage(onLoginClicked: toggleView);
  }
}
