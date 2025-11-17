import 'package:flutter/material.dart';
import 'package:finance_ap/services/dashboard_service.dart';
import 'package:intl/intl.dart';

class Category {
  final int id;
  final String name;
  final String type;

  Category({required this.id, required this.name, required this.type});
}

class TransactionModal extends StatefulWidget {
  final VoidCallback? onTransactionCreated;

  const TransactionModal({super.key, this.onTransactionCreated});

  @override
  State<TransactionModal> createState() => _TransactionModalState();
}

class _TransactionModalState extends State<TransactionModal> {
  final DashboardService _dashboardService = DashboardService();

  final _formKey = GlobalKey<FormState>();
  List<Category> _categories = [];
  Category? _selectedCategory;
  final _descriptionController = TextEditingController();
  final _amountController = TextEditingController();
  DateTime _selectedDate = DateTime.now();
  String _paymentMethod = 'pix';
  bool _isRecurring = false;
  String _recurrenceType = '';

  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
  try {
    final List<dynamic> cats = await _dashboardService.getCategories();
    setState(() {
      _categories = cats.map((c) => Category(
        id: c['id'],
        name: c['name'],
        type: c['type'],
      )).toList();
    });
  } catch (e) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Erro ao carregar categorias: $e')),
    );
  }
}




  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      await _dashboardService.createTransaction({
        'description': _descriptionController.text.trim(),
        'amount': double.parse(_amountController.text.trim()),
        'transaction_date': _selectedDate.toIso8601String(),
        'category_id': _selectedCategory!.id,
        'payment_method': _paymentMethod,
        'is_recurring': _isRecurring,
        'recurrence_type': _recurrenceType,
      });

      if (widget.onTransactionCreated != null) {
        widget.onTransactionCreated!();
      }

      Navigator.pop(context);
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erro ao criar transação: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      backgroundColor: const Color(0xFF1E1E2C),
      title: const Text('Nova Transação', style: TextStyle(color: Colors.white)),
      content: SingleChildScrollView(
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              DropdownButtonFormField<Category>(
                value: _selectedCategory,
                items: _categories.map((c) {
                  return DropdownMenuItem(
                    value: c,
                    child: Text(c.name, style: const TextStyle(color: Colors.white)),
                  );
                }).toList(),
                onChanged: (value) => setState(() => _selectedCategory = value),
                validator: (value) => value == null ? 'Selecione uma categoria' : null,
                decoration: const InputDecoration(
                  labelText: 'Categoria',
                  filled: true,
                  fillColor: Colors.white12,
                  labelStyle: TextStyle(color: Colors.white70),
                ),
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _descriptionController,
                decoration: const InputDecoration(
                  labelText: 'Descrição',
                  filled: true,
                  fillColor: Colors.white12,
                  labelStyle: TextStyle(color: Colors.white70),
                ),
                validator: (v) => v == null || v.isEmpty ? 'Informe a descrição' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _amountController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Valor',
                  filled: true,
                  fillColor: Colors.white12,
                  labelStyle: TextStyle(color: Colors.white70),
                ),
                validator: (v) => v == null || v.isEmpty ? 'Informe o valor' : null,
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  const Text('Data', style: TextStyle(color: Colors.white70)),
                  const SizedBox(width: 12),
                  TextButton(
                    onPressed: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: _selectedDate,
                        firstDate: DateTime(2000),
                        lastDate: DateTime(2100),
                      );
                      if (picked != null) setState(() => _selectedDate = picked);
                    },
                    child: Text(DateFormat('dd/MM/yyyy').format(_selectedDate)),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _paymentMethod,
                items: const [
                  DropdownMenuItem(value: 'pix', child: Text('Pix')),
                  DropdownMenuItem(value: 'credit_card', child: Text('Cartão de Crédito')),
                  DropdownMenuItem(value: 'money', child: Text('Dinheiro')),
                  DropdownMenuItem(value: 'others', child: Text('Outros')),
                ],
                onChanged: (v) => setState(() => _paymentMethod = v!),
                decoration: const InputDecoration(
                  labelText: 'Método de Pagamento',
                  filled: true,
                  fillColor: Colors.white12,
                  labelStyle: TextStyle(color: Colors.white70),
                ),
              ),
              const SizedBox(height: 12),
              CheckboxListTile(
                title: const Text('Recorrente?', style: TextStyle(color: Colors.white70)),
                value: _isRecurring,
                onChanged: (v) => setState(() => _isRecurring = v!),
              ),
              if (_isRecurring)
                TextFormField(
                  decoration: const InputDecoration(
                    labelText: 'Tipo de Recorrência',
                    filled: true,
                    fillColor: Colors.white12,
                    labelStyle: TextStyle(color: Colors.white70),
                  ),
                  onChanged: (v) => _recurrenceType = v,
                ),
            ],
          ),
        ),
      ),
      actions: [
        TextButton(
          onPressed: _isLoading ? null : () => Navigator.pop(context),
          child: const Text('Cancelar'),
        ),
        ElevatedButton(
          onPressed: _isLoading ? null : _submit,
          child: _isLoading
              ? const CircularProgressIndicator()
              : const Text('Salvar'),
        ),
      ],
    );
  }
}
