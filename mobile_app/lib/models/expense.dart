class Expense {
  Expense({
    required this.id,
    required this.type,
    required this.amount,
    required this.currency,
    required this.status,
    required this.expenseDate,
    this.description,
  });

  final int id;
  final String type;
  final double amount;
  final String currency;
  final String status;
  final String expenseDate;
  final String? description;

  factory Expense.fromJson(Map<String, dynamic> json) {
    return Expense(
      id: json['id'] as int,
      type: json['type'] as String,
      amount: (json['amount'] as num).toDouble(),
      currency: json['currency'] as String? ?? 'USD',
      status: json['status'] as String,
      expenseDate: json['expense_date'] as String,
      description: json['description'] as String?,
    );
  }
}
