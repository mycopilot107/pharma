class AppNotification {
  AppNotification({
    required this.id,
    required this.title,
    required this.body,
    required this.type,
    required this.priority,
    this.isUnread = true,
  });

  final int id;
  final String title;
  final String body;
  final String type;
  final String priority;
  final bool isUnread;

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: json['id'] as int,
      title: json['title'] as String,
      body: json['body'] as String? ?? '',
      type: json['type'] as String? ?? '',
      priority: json['priority'] as String? ?? 'normal',
      isUnread: json['is_unread'] as bool? ?? true,
    );
  }
}
