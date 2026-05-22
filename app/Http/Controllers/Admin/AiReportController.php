<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AiReportType;
use App\Http\Controllers\Controller;
use App\Models\AiReport;
use App\Models\User;
use App\Services\AiReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiReportController extends Controller
{
    public function __construct(protected AiReportingService $aiReporting) {}

    public function index(Request $request)
    {
        $company = Auth::user()->company;

        $reports = AiReport::where('company_id', $company->id)
            ->whereIn('type', [
                AiReportType::DailyReport,
                AiReportType::PerformanceAnalysis,
                AiReportType::SmartRecommendations,
                AiReportType::SalesPrediction,
                AiReportType::DoctorEngagement,
            ])
            ->with(['user:id,name', 'generator:id,name'])
            ->latest()
            ->paginate(15);

        $representatives = User::where('company_id', $company->id)
            ->where('role', 'representative')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.ai-reports.index', [
            'reports' => $reports,
            'representatives' => $representatives,
            'reportTypes' => array_filter(
                AiReportType::cases(),
                fn ($t) => $t !== AiReportType::VisitSummary
            ),
            'aiAvailable' => $this->aiReporting->isAvailable(),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:'.implode(',', array_column(AiReportType::cases(), 'value'))],
            'user_id' => ['nullable', 'exists:users,id'],
            'force' => ['boolean'],
        ]);

        if (! $this->aiReporting->isAvailable()) {
            return back()->with('error', 'OpenAI is not configured. Add OPENAI_API_KEY and OPENAI_ENABLED=true to .env');
        }

        $type = AiReportType::from($validated['type']);

        if ($type === AiReportType::VisitSummary) {
            return back()->with('error', 'Visit summaries are generated from individual visits.');
        }

        $companyId = Auth::user()->company_id;
        $mrUserId = $validated['user_id'] ?? null;

        if ($mrUserId) {
            $belongs = User::where('id', $mrUserId)
                ->where('company_id', $companyId)
                ->where('role', 'representative')
                ->exists();

            if (! $belongs) {
                abort(403);
            }
        }

        try {
            $report = $this->aiReporting->generate(
                $type,
                $companyId,
                Auth::id(),
                $mrUserId,
                force: $request->boolean('force'),
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'AI report failed: '.$e->getMessage());
        }

        return redirect()->route('admin.ai-reports.show', $report)
            ->with('success', 'AI report generated.');
    }

    public function show(AiReport $aiReport)
    {
        if ($aiReport->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $aiReport->load(['user:id,name', 'generator:id,name']);

        return view('admin.ai-reports.show', [
            'report' => $aiReport,
            'aiAvailable' => $this->aiReporting->isAvailable(),
        ]);
    }
}
