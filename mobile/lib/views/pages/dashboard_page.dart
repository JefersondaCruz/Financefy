import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:intl/intl.dart';
import 'package:finance_ap/views/pages/under_construction_page.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  String selectedPeriod = 'Novembro 2025';
  int touchedIndex = -1;

  final List<Map<String, dynamic>> transactions = [
    {
      'title': 'Mercado',
      'date': '2025-11-01',
      'amount': -120.50,
      'method': 'Pix',
      'category': 'Alimentação',
      'type': 'expense',
    },
    {
      'title': 'Salário',
      'date': '2025-11-05',
      'amount': 5000.00,
      'method': 'Transferência',
      'category': 'Salário',
      'type': 'income',
    },
    {
      'title': 'Netflix',
      'date': '2025-11-02',
      'amount': -39.90,
      'method': 'Cartão',
      'category': 'Lazer',
      'type': 'expense',
    },
    {
      'title': 'Gasolina',
      'date': '2025-11-03',
      'amount': -250.00,
      'method': 'Pix',
      'category': 'Transporte',
      'type': 'expense',
    },
    {
      'title': 'Freelance',
      'date': '2025-11-07',
      'amount': 1500.00,
      'method': 'Pix',
      'category': 'Freelance',
      'type': 'income',
    },
    {
      'title': 'Academia',
      'date': '2025-11-08',
      'amount': -120.00,
      'method': 'Cartão',
      'category': 'Saúde',
      'type': 'expense',
    },
    {
      'title': 'Restaurante',
      'date': '2025-11-09',
      'amount': -85.00,
      'method': 'Cartão',
      'category': 'Alimentação',
      'type': 'expense',
    },
  ];

  double get totalExpenses => transactions
      .where((t) => t['type'] == 'expense')
      .fold(0.0, (sum, t) => sum + (t['amount'] as double).abs());

  double get totalIncome => transactions
      .where((t) => t['type'] == 'income')
      .fold(0.0, (sum, t) => sum + (t['amount'] as double));

  double get balance => totalIncome - totalExpenses;

  double get savingsRate => totalIncome > 0 ? (balance / totalIncome) * 100 : 0;

  Map<String, double> get expensesByCategory {
    final Map<String, double> result = {};
    for (var t in transactions.where((t) => t['type'] == 'expense')) {
      final category = t['category'] as String;
      final amount = (t['amount'] as double).abs();
      result[category] = (result[category] ?? 0) + amount;
    }
    return result;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1A1D29),
      body: CustomScrollView(
        slivers: [
          _buildAppBar(),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildKPICards(),
                  const SizedBox(height: 24),
                  _buildEvolutionChart(),
                  const SizedBox(height: 24),
                  _buildCategoryBreakdown(),
                  const SizedBox(height: 24),
                  _buildRecentTransactions(),
                  const SizedBox(height: 80),
                ],
              ),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {},
        backgroundColor: const Color(0xFF10B981),
        icon: const Icon(Icons.add),
        label: const Text('Nova Transação'),
      ),
      drawer: Drawer(
        backgroundColor: const Color(0xFF1E1E2C),
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            const DrawerHeader(
              decoration: BoxDecoration(color: Colors.greenAccent),
              child: Text(
                'Financefy Menu',
                style: TextStyle(color: Colors.black, fontSize: 24),
              ),
            ),
            ListTile(
              leading: const Icon(Icons.dashboard, color: Colors.white70),
              title: const Text('Dashboard', style: TextStyle(color: Colors.white)),
              onTap: () => Navigator.pop(context),
            ),
            ListTile(
              leading: const Icon(Icons.account_balance_wallet, color: Colors.white70),
              title: const Text('Transações', style: TextStyle(color: Colors.white)),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => const UnderConstructionPage(title: 'Transações'),
                  ),
                );
              },
            ),
            ListTile(
              leading: const Icon(Icons.settings, color: Colors.white70),
              title: const Text('Configurações', style: TextStyle(color: Colors.white)),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => const UnderConstructionPage(title: 'Configurações'),
                  ),
                );
              },
            ),
            ListTile(
              leading: const Icon(Icons.logout, color: Colors.white70),
              title: const Text('Sair', style: TextStyle(color: Colors.white)),
              onTap: () {
                Navigator.pop(context);
              },
            ),
          ],
        ),
      ),
    );
    
  }

  Widget _buildAppBar() {
    return SliverAppBar(
      expandedHeight: 120,
      floating: false,
      pinned: true,
      backgroundColor: const Color(0xFF242837),
      flexibleSpace: FlexibleSpaceBar(
        title: const Text(
          'Dashboard Financeiro',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        background: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF10B981), Color(0xFF14B8A6)],
            ),
          ),
        ),
      ),
      actions: [
        PopupMenuButton<String>(
          icon: const Icon(Icons.calendar_month),
          onSelected: (value) => setState(() => selectedPeriod = value),
          itemBuilder: (context) => [
            'Novembro 2025',
            'Outubro 2025',
            'Setembro 2025',
            'Últimos 3 meses',
            'Últimos 6 meses',
            'Ano 2025'
          ].map((period) => PopupMenuItem(value: period, child: Text(period))).toList(),
        ),
        IconButton(
          icon: const Icon(Icons.file_download),
          onPressed: () {},
          tooltip: 'Exportar Relatório',
        ),
      ],
    );
  }

  Widget _buildKPICards() {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildKPICard(
                'Despesas',
                'R\$ ${totalExpenses.toStringAsFixed(2)}',
                '+12,5% vs mês anterior',
                Icons.trending_up,
                const Color(0xFFEF4444),
                false,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildKPICard(
                'Receitas',
                'R\$ ${totalIncome.toStringAsFixed(2)}',
                '+8,2% vs mês anterior',
                Icons.trending_up,
                const Color(0xFF10B981),
                true,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildKPICard(
                'Saldo',
                'R\$ ${balance.toStringAsFixed(2)}',
                balance > 0 ? 'Positivo' : 'Negativo',
                Icons.account_balance_wallet,
                balance > 0 ? const Color(0xFF10B981) : const Color(0xFFEF4444),
                balance > 0,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildKPICard(
                'Taxa de Economia',
                '${savingsRate.toStringAsFixed(1)}%',
                savingsRate >= 20 ? 'Excelente!' : 'Pode melhorar',
                Icons.savings,
                savingsRate >= 20 ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                savingsRate >= 20,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildKPICard(String title, String value, String subtitle, IconData icon, Color color, bool positive) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF242837),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const Spacer(),
              Icon(
                positive ? Icons.arrow_upward : Icons.arrow_downward,
                color: color,
                size: 16,
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            title,
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: TextStyle(
              color: color,
              fontSize: 11,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEvolutionChart() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF242837),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Evolução Financeira',
            style: TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Receitas vs Despesas (Últimos 6 meses)',
            style: TextStyle(color: Colors.white54, fontSize: 12),
          ),
          const SizedBox(height: 24),
          SizedBox(
            height: 220,
            child: LineChart(
              LineChartData(
                gridData: FlGridData(
                  show: true,
                  drawVerticalLine: false,
                  horizontalInterval: 1000,
                  getDrawingHorizontalLine: (value) => FlLine(
                    color: Colors.white10,
                    strokeWidth: 1,
                  ),
                ),
                titlesData: FlTitlesData(
                  leftTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 50,
                      interval: 2000,
                      getTitlesWidget: (value, meta) => Text(
                        'R\$ ${(value / 1000).toStringAsFixed(0)}k',
                        style: const TextStyle(color: Colors.white54, fontSize: 10),
                      ),
                    ),
                  ),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 30,
                      getTitlesWidget: (value, meta) {
                        const months = ['Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov'];
                        if (value.toInt() >= 0 && value.toInt() < months.length) {
                          return Padding(
                            padding: const EdgeInsets.only(top: 8),
                            child: Text(
                              months[value.toInt()],
                              style: const TextStyle(color: Colors.white54, fontSize: 10),
                            ),
                          );
                        }
                        return const SizedBox();
                      },
                    ),
                  ),
                  rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                ),
                borderData: FlBorderData(show: false),
                minX: 0,
                maxX: 5,
                minY: 0,
                maxY: 8000,
                lineBarsData: [
                  // Linha de Receitas
                  LineChartBarData(
                    spots: const [
                      FlSpot(0, 4500),
                      FlSpot(1, 5200),
                      FlSpot(2, 4800),
                      FlSpot(3, 5500),
                      FlSpot(4, 5000),
                      FlSpot(5, 6500),
                    ],
                    isCurved: true,
                    color: const Color(0xFF10B981),
                    barWidth: 3,
                    isStrokeCapRound: true,
                    dotData: const FlDotData(show: true),
                    belowBarData: BarAreaData(
                      show: true,
                      color: const Color(0xFF10B981).withOpacity(0.1),
                    ),
                  ),
                  // Linha de Despesas
                  LineChartBarData(
                    spots: const [
                      FlSpot(0, 3200),
                      FlSpot(1, 3800),
                      FlSpot(2, 3500),
                      FlSpot(3, 4200),
                      FlSpot(4, 3900),
                      FlSpot(5, 615),
                    ],
                    isCurved: true,
                    color: const Color(0xFFEF4444),
                    barWidth: 3,
                    isStrokeCapRound: true,
                    dotData: const FlDotData(show: true),
                    belowBarData: BarAreaData(
                      show: true,
                      color: const Color(0xFFEF4444).withOpacity(0.1),
                    ),
                  ),
                ],
                lineTouchData: LineTouchData(
                enabled: true,
                touchTooltipData: LineTouchTooltipData(
                  getTooltipItems: (touchedSpots) {
                    return touchedSpots.map((spot) {
                      return LineTooltipItem(
                        'R\$ ${spot.y.toStringAsFixed(0)}',
                        const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      );
                    }).toList();
                  },
                ),
              ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _buildLegendItem('Receitas', const Color(0xFF10B981)),
              const SizedBox(width: 24),
              _buildLegendItem('Despesas', const Color(0xFFEF4444)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildLegendItem(String label, Color color) {
    return Row(
      children: [
        Container(
          width: 16,
          height: 3,
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 6),
        Text(
          label,
          style: const TextStyle(color: Colors.white70, fontSize: 12),
        ),
      ],
    );
  }

  Widget _buildCategoryBreakdown() {
    final expenses = expensesByCategory;
    final total = expenses.values.fold(0.0, (sum, val) => sum + val);
    final sortedEntries = expenses.entries.toList()..sort((a, b) => b.value.compareTo(a.value));

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF242837),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Gastos por Categoria',
            style: TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 24),
          Row(
            children: [
              SizedBox(
                height: 180,
                width: 180,
                child: PieChart(
                  PieChartData(
                    pieTouchData: PieTouchData(
                      touchCallback: (event, response) {
                        setState(() {
                          if (!event.isInterestedForInteractions || response == null || response.touchedSection == null) {
                            touchedIndex = -1;
                            return;
                          }
                          touchedIndex = response.touchedSection!.touchedSectionIndex;
                        });
                      },
                    ),
                    sectionsSpace: 2,
                    centerSpaceRadius: 50,
                    sections: _buildPieChartSections(sortedEntries, total),
                  ),
                ),
              ),
              const SizedBox(width: 24),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: sortedEntries.take(5).map((entry) {
                    final percentage = (entry.value / total * 100);
                    final color = _getCategoryColor(entry.key);
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: Row(
                        children: [
                          Container(
                            width: 12,
                            height: 12,
                            decoration: BoxDecoration(
                              color: color,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  entry.key,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 12,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                                Text(
                                  'R\$ ${entry.value.toStringAsFixed(2)}',
                                  style: const TextStyle(
                                    color: Colors.white54,
                                    fontSize: 10,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Text(
                            '${percentage.toStringAsFixed(1)}%',
                            style: TextStyle(
                              color: color,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  List<PieChartSectionData> _buildPieChartSections(List<MapEntry<String, double>> entries, double total) {
    return entries.asMap().entries.map((item) {
      final index = item.key;
      final entry = item.value;
      final isTouched = index == touchedIndex;
      final radius = isTouched ? 65.0 : 55.0;
      final percentage = (entry.value / total * 100);

      return PieChartSectionData(
        color: _getCategoryColor(entry.key),
        value: entry.value,
        title: '${percentage.toStringAsFixed(0)}%',
        radius: radius,
        titleStyle: TextStyle(
          fontSize: isTouched ? 14 : 11,
          fontWeight: FontWeight.bold,
          color: Colors.white,
        ),
      );
    }).toList();
  }

  Color _getCategoryColor(String category) {
    final colors = {
      'Alimentação': const Color(0xFF10B981),
      'Transporte': const Color(0xFF3B82F6),
      'Lazer': const Color(0xFFEC4899),
      'Saúde': const Color(0xFFEF4444),
      'Educação': const Color(0xFF8B5CF6),
      'Moradia': const Color(0xFFF59E0B),
      'Outros': const Color(0xFF6B7280),
    };
    return colors[category] ?? const Color(0xFF6B7280);
  }

  Widget _buildRecentTransactions() {
    final recentTransactions = transactions.take(5).toList();

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF242837),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Transações Recentes',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              TextButton(
                onPressed: () {},
                child: const Text('Ver todas'),
              ),
            ],
          ),
          const SizedBox(height: 16),
          ...recentTransactions.map((t) => _buildTransactionItem(t)),
        ],
      ),
    );
  }

  Widget _buildTransactionItem(Map<String, dynamic> transaction) {
    final icon = _getCategoryIcon(transaction['category'] as String);
    final amount = transaction['amount'] as double;
    final isExpense = transaction['type'] == 'expense';
    final date = DateFormat('dd/MM/yyyy').format(DateTime.parse(transaction['date'] as String));

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFF2E2E46),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isExpense ? const Color(0xFFEF4444).withOpacity(0.2) : const Color(0xFF10B981).withOpacity(0.2),
        ),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: (isExpense ? const Color(0xFFEF4444) : const Color(0xFF10B981)).withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              icon,
              color: isExpense ? const Color(0xFFEF4444) : const Color(0xFF10B981),
              size: 20,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  transaction['title'] as String,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: _getCategoryColor(transaction['category'] as String).withOpacity(0.2),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        transaction['category'] as String,
                        style: TextStyle(
                          color: _getCategoryColor(transaction['category'] as String),
                          fontSize: 10,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      '• ${transaction['method']}',
                      style: const TextStyle(
                        color: Colors.white54,
                        fontSize: 11,
                      ),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      '• $date',
                      style: const TextStyle(
                        color: Colors.white54,
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            '${isExpense ? '-' : '+'} R\$ ${amount.abs().toStringAsFixed(2)}',
            style: TextStyle(
              color: isExpense ? const Color(0xFFEF4444) : const Color(0xFF10B981),
              fontWeight: FontWeight.bold,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

  IconData _getCategoryIcon(String category) {
    switch (category) {
      case 'Transporte':
        return FontAwesomeIcons.car;
      case 'Saúde':
        return FontAwesomeIcons.briefcaseMedical;
      case 'Salário':
        return FontAwesomeIcons.moneyBillWave;
      case 'Moradia':
        return FontAwesomeIcons.house;
      case 'Lazer':
        return FontAwesomeIcons.film;
      case 'Investimentos':
        return FontAwesomeIcons.chartLine;
      case 'Freelance':
        return FontAwesomeIcons.laptopCode;
      case 'Educação':
        return FontAwesomeIcons.book;
      case 'Alimentação':
        return FontAwesomeIcons.utensils;
      default:
        return FontAwesomeIcons.tag;
    }
  }
}
