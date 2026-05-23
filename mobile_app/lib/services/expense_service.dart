import 'package:http/http.dart' as http;

import '../core/api/api_client.dart';
import '../models/expense.dart';

class ExpenseService {
  ExpenseService(this._api);

  final ApiClient _api;

  Future<List<Expense>> list() async {
    final data = await _api.get('/expenses');
    final items = (data['data'] as List?) ?? [];
    return items.map((e) => Expense.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<Expense> create({
    required String type,
    required double amount,
    required String expenseDate,
    String? description,
    required String receiptPath,
  }) async {
    final file = await http.MultipartFile.fromPath('receipt', receiptPath);
    final data = await _api.postMultipart(
      '/expenses',
      fields: {
        'type': type,
        'amount': amount.toString(),
        'expense_date': expenseDate,
        if (description != null) 'description': description,
      },
      files: [file],
    );
    return Expense.fromJson(data as Map<String, dynamic>);
  }

  Future<void> delete(int id) async {
    await _api.delete('/expenses/$id');
  }
}
