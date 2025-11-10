import 'package:flutter/material.dart';
import 'package:finance_ap/views/widgets/transaction_card.dart';

class DashboardPage extends StatelessWidget {
  const DashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    // Mock temporário — depois vai vir da API
    final transactions = [
      {'name': 'Mercado', 'value': 120.50, 'date': '2025-11-01'},
      {'name': 'Netflix', 'value': 39.90, 'date': '2025-11-02'},
      {'name': 'Gasolina', 'value': 250.00, 'date': '2025-11-03'},
    ];

    final total = transactions.fold<double>(0, (sum, t) => sum + (t['value'] as double));

    return Scaffold(
      backgroundColor: const Color(0xFF1E1E2C),
      appBar: AppBar(
        backgroundColor: Colors.greenAccent,
        foregroundColor: Colors.black,
        title: const Text('Dashboard'),
        centerTitle: true,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Resumo do mês',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 8),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white12,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                'Total gasto: R\$ ${total.toStringAsFixed(2)}',
                style: const TextStyle(color: Colors.greenAccent, fontSize: 20),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'Transações recentes',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.white),
            ),
            const SizedBox(height: 8),
            Expanded(
              child: ListView.builder(
                itemCount: transactions.length,
                itemBuilder: (context, index) {
                  final t = transactions[index];
                  return TransactionCard(
                    title: t['name'] as String,
                    value: t['value'] as double,
                    date: t['date'] as String,
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}
