<?php

namespace App\Services;

use App\Enums\AiReportType;
use App\Models\AiReport;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AiReportingService
{
    public function __construct(
        protected OpenAiService $openAi,
        protected AiInsightsDataService $insights,
    ) {}

    public function isAvailable(): bool
    {
        return $this->openAi->isConfigured();
    }

    public function generate(
        AiReportType $type,
        int $companyId,
        int $generatedBy,
        ?int $mrUserId = null,
        ?Carbon $reportDate = null,
        bool $force = false,
    ): AiReport {
        $reportDate ??= today();

        if (! $force) {
            $existing = AiReport::where('company_id', $companyId)
                ->where('type', $type)
                ->where('report_date', $reportDate)
                ->when($mrUserId, fn ($q) => $q->where('user_id', $mrUserId))
                ->when(! $mrUserId, fn ($q) => $q->whereNull('user_id'))
                ->latest()
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $days = match ($type) {
            AiReportType::DailyReport => 1,
            AiReportType::SalesPrediction => 30,
            default => 7,
        };

        $context = $this->insights->companyContext($companyId, $days, $mrUserId);
        $detected = $this->insights->detectInsights($context);

        $content = $this->openAi->chat([
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user', 'content' => $this->buildUserPrompt($type, $context, $detected)],
        ]);

        return AiReport::create([
            'company_id' => $companyId,
            'user_id' => $mrUserId,
            'generated_by' => $generatedBy,
            'type' => $type,
            'title' => $type->label().' · '.$reportDate->format('d M Y'),
            'report_date' => $reportDate,
            'context_snapshot' => $context,
            'detected_insights' => $detected,
            'content' => $content,
        ]);
    }

    public function summarizeVisit(Visit $visit, int $generatedBy): string
    {
        $context = $this->insights->visitContext($visit);

        $summary = $this->openAi->chat([
            ['role' => 'system', 'content' => 'You are a pharma field-force assistant. Write concise, professional visit summaries for medical representatives. Use markdown with short sections: Summary, Key discussion points, Follow-up actions. Keep under 200 words.'],
            ['role' => 'user', 'content' => "Generate a visit summary from this JSON data:\n".json_encode($context, JSON_PRETTY_PRINT)],
        ], 500);

        $visit->update(['ai_summary' => $summary]);

        AiReport::create([
            'company_id' => $visit->company_id,
            'user_id' => $visit->user_id,
            'generated_by' => $generatedBy,
            'type' => AiReportType::VisitSummary,
            'title' => 'Visit summary · '.$visit->place_name,
            'report_date' => today(),
            'context_snapshot' => $context,
            'detected_insights' => [],
            'content' => $summary,
        ]);

        return $summary;
    }

    public function tryAutoSummarizeVisit(Visit $visit): void
    {
        if (! $this->isAvailable() || filled($visit->ai_summary)) {
            return;
        }

        try {
            $this->summarizeVisit($visit, $visit->user_id);
        } catch (\Throwable $e) {
            Log::warning('Auto visit summary failed', [
                'visit_id' => $visit->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an AI analytics assistant for a pharmaceutical medical representative (MR) management platform called MedRep Fleet.

Analyze the JSON data provided and produce clear, actionable reports for sales managers and MRs.

Rules:
- Use markdown formatting (headings, bullet lists, bold for emphasis).
- Lead with 2-4 bullet "Key alerts" when issues exist (e.g. missed doctor visits, overdue follow-ups, underperforming targets).
- Be specific: name MRs, doctors, products, and numbers from the data.
- For doctor engagement, highlight high-priority doctors not visited and quantify gaps.
- For sales prediction, extrapolate cautiously from purchase and prescription trends; state assumptions.
- Keep tone professional and concise.
- Do not invent data not present in the JSON.
PROMPT;
    }

    protected function buildUserPrompt(AiReportType $type, array $context, array $detected): string
    {
        $detectedText = $detected
            ? "Pre-detected alerts (incorporate and expand on these):\n- ".implode("\n- ", $detected)
            : 'No pre-detected alerts.';

        $focus = match ($type) {
            AiReportType::DailyReport => 'Generate a DAILY REPORT covering today/period activity: visits completed, routes, follow-ups due, and priorities for tomorrow.',
            AiReportType::PerformanceAnalysis => 'Generate a PERFORMANCE ANALYSIS: MR rankings, target progress, visit completion rates, strengths and weaknesses.',
            AiReportType::SmartRecommendations => 'Generate SMART RECOMMENDATIONS: prioritized actions for the sales manager and each MR for the next 7 days.',
            AiReportType::SalesPrediction => 'Generate a SALES PREDICTION: forecast next 30 days based on purchase patterns, prescriptions, and visit momentum. Include confidence level.',
            AiReportType::DoctorEngagement => 'Generate DOCTOR ENGAGEMENT INSIGHTS: high-priority doctors, missed visits this week, engagement scores, and doctors at risk of churn. Use examples like "MR X missed N high-priority doctor visits this week."',
            AiReportType::VisitSummary => 'Summarize the visit.',
        };

        return "{$focus}\n\n{$detectedText}\n\nData JSON:\n".json_encode($context, JSON_PRETTY_PRINT);
    }
}
