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
    String? receiptPath,
  }) async {
    late Map<String, dynamic> data;

    if (receiptPath != null) {
      final file = await http.MultipartFile.fromPath('receipt', receiptPath);
      data = (await _api.postMultipart(
        '/expenses',
        fields: {
          'type': type,
          'amount': amount.toString(),
          'expense_date': expenseDate,
          if (description != null) 'description': description,
        },
        files: [file],
      )) as Map<String, dynamic>;
    } else {
      data = (await _api.post('/expenses', body: {
        'type': type,
        'amount': amount,
        'expense_date': expenseDate,
        if (description != null) 'description': description,
      })) as Map<String, dynamic>;
    }

    return Expense.fromJson(data);
  }

  Future<void> delete(int id) async {
    await _api.delete('/expenses/$id');
  }
}
