class OrderItem {
  OrderItem({
    required this.productId,
    required this.quantity,
    this.productName,
    this.unitPrice,
    this.totalPrice,
  });

  final int productId;
  final int quantity;
  final String? productName;
  final double? unitPrice;
  final double? totalPrice;

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      productId: json['product_id'] as int,
      quantity: json['quantity'] as int,
      productName: json['product_name'] as String?,
      unitPrice: json['unit_price'] != null ? (json['unit_price'] as num).toDouble() : null,
      totalPrice: json['total_price'] != null ? (json['total_price'] as num).toDouble() : null,
    );
  }
}

class Order {
  Order({
    required this.id,
    required this.orderNumber,
    required this.orderDate,
    required this.status,
    required this.totalAmount,
    required this.currency,
    this.statusLabel,
    this.notes,
    this.managerNotes,
    this.customerName,
    this.customerType,
    this.items = const [],
    this.createdAt,
  });

  final int id;
  final String orderNumber;
  final String orderDate;
  final String status;
  final double totalAmount;
  final String currency;
  final String? statusLabel;
  final String? notes;
  final String? managerNotes;
  final String? customerName;
  final String? customerType;
  final List<OrderItem> items;
  final String? createdAt;

  bool get isPending => status == 'pending';

  factory Order.fromJson(Map<String, dynamic> json) {
    final customer = json['customer'] as Map<String, dynamic>?;
    final itemsRaw = json['items'] as List?;
    return Order(
      id: json['id'] as int,
      orderNumber: json['order_number'] as String? ?? '',
      orderDate: json['order_date'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      totalAmount: (json['total_amount'] as num?)?.toDouble() ?? 0.0,
      currency: json['currency'] as String? ?? 'INR',
      statusLabel: json['status_label'] as String?,
      notes: json['notes'] as String?,
      managerNotes: json['manager_notes'] as String?,
      customerName: customer?['name'] as String?,
      customerType: customer?['type'] as String?,
      items: itemsRaw
              ?.map((e) => OrderItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const [],
      createdAt: json['created_at'] as String?,
    );
  }
}
