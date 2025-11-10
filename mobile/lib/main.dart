import 'package:flutter/material.dart';
import 'views/pages/auth_wrapper.dart';

void main() {
  runApp(const FinancefyApp());
}

class FinancefyApp extends StatelessWidget {
  const FinancefyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Financefy',
      debugShowCheckedModeBanner: false,
      theme: ThemeData.dark().copyWith(
        colorScheme: const ColorScheme.dark(
          primary: Colors.greenAccent,
        ),
      ),
      home: const AuthWrapper(),
    );
  }
}
