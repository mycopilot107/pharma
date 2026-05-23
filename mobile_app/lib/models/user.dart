class User {
  User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.trackingActive = false,
    this.companyName,
  });

  final int id;
  final String name;
  final String email;
  final String? phone;
  final bool trackingActive;
  final String? companyName;

  factory User.fromJson(Map<String, dynamic> json) {
    final company = json['company'] as Map<String, dynamic>?;
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String?,
      trackingActive: json['tracking_active'] as bool? ?? false,
      companyName: company?['name'] as String?,
    );
  }
}
