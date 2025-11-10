import 'package:flutter/material.dart';

class UnderConstructionPage extends StatelessWidget {
  final String title;

  const UnderConstructionPage({super.key, required this.title});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1E1E2C),
      appBar: AppBar(
        title: Text(title),
        backgroundColor: Colors.greenAccent,
        foregroundColor: Colors.black,
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: const [
            Icon(Icons.construction, color: Colors.greenAccent, size: 80),
            SizedBox(height: 20),
            Text(
              'Esta funcionalidade está em desenvolvimento 🚧',
              style: TextStyle(color: Colors.white70, fontSize: 18),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
