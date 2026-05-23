class Customer {
  Customer({
    required this.id,
    required this.name,
    required this.type,
    this.phone,
    this.address,
    this.specialty,
  });

  final int id;
  final String name;
  final String type;
  final String? phone;
  final String? address;
  final String? specialty;

  factory Customer.fromJson(Map<String, dynamic> json) {
    return Customer(
      id: json['id'] as int,
      name: json['name'] as String,
      type: json['type'] as String? ?? 'doctor',
      phone: json['phone'] as String?,
      address: json['address'] as String?,
      specialty: json['specialty'] as String?,
    );
  }
}
