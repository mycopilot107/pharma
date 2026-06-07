class Product {
  Product({
    required this.id,
    required this.name,
    required this.unitPrice,
    this.sku,
    this.brand,
    this.strength,
    this.packSize,
    this.category,
    this.mrp,
    this.description,
    this.displayLabel,
  });

  final int id;
  final String name;
  final double unitPrice;
  final String? sku;
  final String? brand;
  final String? strength;
  final String? packSize;
  final String? category;
  final double? mrp;
  final String? description;
  final String? displayLabel;

  String get label => displayLabel ?? name;

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'] as int,
      name: json['name'] as String,
      unitPrice: (json['unit_price'] as num).toDouble(),
      sku: json['sku'] as String?,
      brand: json['brand'] as String?,
      strength: json['strength'] as String?,
      packSize: json['pack_size'] as String?,
      category: json['category'] as String?,
      mrp: json['mrp'] != null ? (json['mrp'] as num).toDouble() : null,
      description: json['description'] as String?,
      displayLabel: json['display_label'] as String?,
    );
  }
}
