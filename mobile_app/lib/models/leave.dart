class Leave {
  Leave({
    required this.id,
    required this.leaveType,
    required this.startDate,
    required this.endDate,
    required this.daysCount,
    required this.status,
    required this.reason,
    this.leaveTypeLabel,
    this.statusLabel,
    this.isHalfDay = false,
    this.halfDayPeriod,
    this.managerNotes,
    this.reviewedAt,
    this.createdAt,
  });

  final int id;
  final String leaveType;
  final String startDate;
  final String endDate;
  final double daysCount;
  final String status;
  final String reason;
  final String? leaveTypeLabel;
  final String? statusLabel;
  final bool isHalfDay;
  final String? halfDayPeriod;
  final String? managerNotes;
  final String? reviewedAt;
  final String? createdAt;

  bool get isPending => status == 'pending';
  bool get isApproved => status == 'approved';

  factory Leave.fromJson(Map<String, dynamic> json) {
    return Leave(
      id: json['id'] as int,
      leaveType: json['leave_type'] as String? ?? 'casual',
      startDate: json['start_date'] as String? ?? '',
      endDate: json['end_date'] as String? ?? '',
      daysCount: (json['days_count'] as num?)?.toDouble() ?? 0,
      status: json['status'] as String? ?? 'pending',
      reason: json['reason'] as String? ?? '',
      leaveTypeLabel: json['leave_type_label'] as String?,
      statusLabel: json['status_label'] as String?,
      isHalfDay: json['is_half_day'] as bool? ?? false,
      halfDayPeriod: json['half_day_period'] as String?,
      managerNotes: json['manager_notes'] as String?,
      reviewedAt: json['reviewed_at'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
