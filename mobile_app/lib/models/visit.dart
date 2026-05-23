class Visit {
  Visit({
    required this.id,
    required this.placeName,
    required this.status,
    this.visitType,
    this.address,
    this.notes,
    this.checkedInAt,
    this.checkedOutAt,
    this.durationMinutes,
    this.customerName,
    this.aiSummary,
  });

  final int id;
  final String placeName;
  final String status;
  final String? visitType;
  final String? address;
  final String? notes;
  final String? checkedInAt;
  final String? checkedOutAt;
  final int? durationMinutes;
  final String? customerName;
  final String? aiSummary;

  bool get isPlanned => status == 'planned';
  bool get isInProgress => status == 'in_progress';
  bool get isCompleted => status == 'completed';

  factory Visit.fromJson(Map<String, dynamic> json) {
    final customer = json['customer'] as Map<String, dynamic>?;
    return Visit(
      id: json['id'] as int,
      placeName: json['place_name'] as String? ?? 'Visit',
      status: json['status'] as String? ?? 'planned',
      visitType: json['visit_type'] as String?,
      address: json['address'] as String?,
      notes: json['notes'] as String?,
      checkedInAt: json['checked_in_at'] as String?,
      checkedOutAt: json['checked_out_at'] as String?,
      durationMinutes: json['duration_minutes'] as int?,
      customerName: customer?['name'] as String?,
      aiSummary: json['ai_summary'] as String?,
    );
  }
}
