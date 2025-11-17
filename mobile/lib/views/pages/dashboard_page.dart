import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:intl/intl.dart';
import 'package:finance_ap/views/pages/under_construction_page.dart';
import 'package:finance_ap/services/dashboard_service.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:finance_ap/views/pages/login_page.dart';
import 'package:finance_ap/views/widgets/transaction_modal.dart';


class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  final DashboardService _dashboardService = DashboardService();
  String selectedPeriod = 'Novembro 2025';
  int touchedIndex = -1;

  final List<Map<String, dynamic>> transactions = [];

  double get totalExpenses => transactions
      .where((t) => t['type'] == 'expense')
      .fold(0.0, (sum, t) => sum + (t['amount'] as double).abs());

  double get totalIncome => transactions
      .where((t) => t['type'] == 'income')
      .fold(0.0, (sum, t) => sum + (t['amount'] as double));

  Future<void> _loadTransactions() async {
    setState(() => isLoading = true);
    try {
      final list = await _dashboardService.transactions();

      final mapped = list.map((t) {
        return {
          "id": t["id"],
          "title": t["description"],
          "amount": double.parse(t["amount"].toString()),
          "date": t["transaction_date"],
          "method": t["payment_method"],
          "category": t["category"]["name"],
          "type": t["category"]["type"],
        };
      }).toList();

      setState(() {
        transactions
          ..clear()
          ..addAll(mapped);
      });
    } catch (e) {
      debugPrint('Erro ao carregar transações: $e');
    } finally {
      setState(() => isLoading = false);
    }
  }

Map<String, Map<String, double>> _groupTransactionsByMonth() {
  final Map<String, Map<String, double>> monthlyData = {};

  for (final t in transactions) {
    final date = DateTime.parse(t['date']);
    final monthKey = DateFormat('MM/yyyy').format(date);

    final isIncome = t['type'] == 'income';
    final amount = (t['amount'] as double);

    monthlyData.putIfAbsent(monthKey, () => {
      'income': 0.0,
      'expense': 0.0,
    });

    if (isIncome) {
      monthlyData[monthKey]!['income'] =
          monthlyData[monthKey]!['income']! + amount;
    } else {
      monthlyData[monthKey]!['expense'] =
          monthlyData[monthKey]!['expense']! + amount.abs();
    }
  }

  final sortedKeys = monthlyData.keys.toList()
    ..sort((a, b) {
      final da = DateFormat('MM/yyyy').parse(a);
      final db = DateFormat('MM/yyyy').parse(b);
      return da.compareTo(db);
    });

  final Map<String, Map<String, double>> sortedData = {};
  for (final key in sortedKeys) {
    sortedData[key] = monthlyData[key]!;
  }
  return sortedData;
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

  @override
  void initState() {
    super.initState();
    _loadTransactions();
  }

  bool isLoading = true;

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
                  _buildMonthlyBarChart(),
                  _buildRecentTransactions(),
                  const SizedBox(height: 80),
                ],
              ),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
      onPressed: () {
        showModalBottomSheet(
          context: context,
          isScrollControlled: true,
          builder: (context) => TransactionModal(
            onTransactionCreated: () async {
              await _loadTransactions();
            },
          ),
        );
      },
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
              onTap: () async {
                Navigator.pop(context);

                await _logoutUser();
                if (!mounted) return;
                Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(builder: (_) => const LoginPage()),
                  (route) => false,
                );
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
    );
  }

  Future<void> _logoutUser() async {
  final prefs = await SharedPreferences.getInstance();
  await prefs.remove('token');
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

  Widget _buildMonthlyBarChart() {
  if (transactions.isEmpty) {
    return const SizedBox();
  }

  final data = _groupTransactionsByMonth();
  final months = data.keys.toList();
  final incomes = months.map((m) => data[m]!['income']!).toList();
  final expenses = months.map((m) => data[m]!['expense']!).toList();


  final maxY = ([
  ...incomes,
  ...expenses
].fold<double>(0, (p, c) => c > p ? c : p)) * 1.3;

double _getNiceInterval(double maxY) {
  if (maxY <= 500) return 100;
  if (maxY <= 1000) return 200;
  if (maxY <= 5000) return 500;
  if (maxY <= 10000) return 1000;
  return maxY / 5;
}

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
          "Receitas x Despesas por mês",
          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 16),

        SizedBox(
          height: 260,
          child: BarChart(
            BarChartData(
              maxY: ([
                ...incomes,
                ...expenses
              ].fold<double>(0, (p, c) => c > p ? c : p)) * 1.3,

              barGroups: List.generate(months.length, (i) {
                return BarChartGroupData(
                  x: i,
                  barsSpace: 10,
                  barRods: [
                    BarChartRodData(
                      toY: incomes[i],
                      color: const Color(0xFF10B981),
                      width: 14,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    BarChartRodData(
                      toY: expenses[i],
                      color: const Color(0xFFEF4444),
                      width: 14,
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ],
                );
              }),

              titlesData: FlTitlesData(
                leftTitles: AxisTitles(
                  sideTitles: SideTitles(
                    showTitles: true,
                    reservedSize: 45,
                    interval: _getNiceInterval(maxY),
                    getTitlesWidget: (value, meta) {
                      if (value == 0 || value % _getNiceInterval(maxY) != 0) {
                        return const SizedBox();
                      }
                      return Text(
                        "R\$ ${value.toInt()}",
                        style: const TextStyle(color: Colors.white54, fontSize: 10),
                      );
                    },
                  ),
                ),

                bottomTitles: AxisTitles(
                  sideTitles: SideTitles(
                    showTitles: true,
                    getTitlesWidget: (value, meta) {
                      if (value < 0 || value >= months.length) return const SizedBox();
                      return Padding(
                        padding: const EdgeInsets.only(top: 6),
                        child: Text(
                          months[value.toInt()],
                          style: const TextStyle(color: Colors.white70, fontSize: 10),
                        ),
                      );
                    },
                  ),
                ),
                rightTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                topTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
              ),

              gridData: FlGridData(
                show: true,
                drawVerticalLine: false,
                getDrawingHorizontalLine: (value) => FlLine(
                  color: Colors.white12,
                  strokeWidth: 1,
                ),
              ),

              borderData: FlBorderData(show: false),
            ),
          ),
        ),

        const SizedBox(height: 20),

        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            _buildLegendItem("Receitas", const Color(0xFF10B981)),
            const SizedBox(width: 20),
            _buildLegendItem("Despesas", const Color(0xFFEF4444)),
          ],
        )
      ],
    ),
  );
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
