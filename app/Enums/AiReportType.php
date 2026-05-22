<?php

namespace App\Enums;

enum AiReportType: string
{
    case DailyReport = 'daily_report';
    case PerformanceAnalysis = 'performance_analysis';
    case SmartRecommendations = 'smart_recommendations';
    case SalesPrediction = 'sales_prediction';
    case DoctorEngagement = 'doctor_engagement';
    case VisitSummary = 'visit_summary';

    public function label(): string
    {
        return match ($this) {
            self::DailyReport => 'AI daily report',
            self::PerformanceAnalysis => 'Performance analysis',
            self::SmartRecommendations => 'Smart recommendations',
            self::SalesPrediction => 'Sales prediction',
            self::DoctorEngagement => 'Doctor engagement insights',
            self::VisitSummary => 'Visit summary',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DailyReport => 'End-of-day field activity digest for your team.',
            self::PerformanceAnalysis => 'Target achievement, visit completion, and MR rankings.',
            self::SmartRecommendations => 'Actionable next steps for managers and reps.',
            self::SalesPrediction => 'Forecast based on purchase patterns and prescriptions.',
            self::DoctorEngagement => 'High-priority doctors, missed visits, and engagement gaps.',
            self::VisitSummary => 'Auto-generated summary of a single field visit.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DailyReport => '📋',
            self::PerformanceAnalysis => '📈',
            self::SmartRecommendations => '💡',
            self::SalesPrediction => '🔮',
            self::DoctorEngagement => '👨‍⚕️',
            self::VisitSummary => '✍️',
        };
    }

    public function adminOnly(): bool
    {
        return match ($this) {
            self::VisitSummary => false,
            default => true,
        };
    }
}
