import 'package:flutter/material.dart';

class TransactionCard extends StatelessWidget {
  final String title;
  final double value;
  final String date;

  const TransactionCard({
    super.key,
    required this.title,
    required this.value,
    required this.date,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      color: Colors.white10,
      margin: const EdgeInsets.symmetric(vertical: 6),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: ListTile(
        title: Text(title, style: const TextStyle(color: Colors.white)),
        subtitle: Text(date, style: const TextStyle(color: Colors.white54)),
        trailing: Text(
          'R\$ ${value.toStringAsFixed(2)}',
          style: const TextStyle(
            color: Colors.greenAccent,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }
}
