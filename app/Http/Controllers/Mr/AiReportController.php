<?php

namespace App\Http\Controllers\Mr;

use App\Enums\AiReportType;
use App\Http\Controllers\Controller;
use App\Models\AiReport;
use App\Services\AiReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiReportController extends Controller
{
    public function __construct(protected AiReportingService $aiReporting) {}

    public function index()
    {
        $user = Auth::user();

        $reports = AiReport::where('company_id', $user->company_id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhereNull('user_id');
            })
            ->whereIn('type', [
                AiReportType::DailyReport,
                AiReportType::PerformanceAnalysis,
                AiReportType::SmartRecommendations,
            ])
            ->latest()
            ->paginate(10);

        return view('mr.ai-reports.index', [
            'reports' => $reports,
            'aiAvailable' => $this->aiReporting->isAvailable(),
        ]);
    }

    public function generate(Request $request)
    {
        if (! $this->aiReporting->isAvailable()) {
            return back()->with('error', 'AI reporting is not enabled. Contact your administrator.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:daily_report,performance_analysis,smart_recommendations'],
            'force' => ['boolean'],
        ]);

        $type = AiReportType::from($validated['type']);

        try {
            $report = $this->aiReporting->generate(
                $type,
                Auth::user()->company_id,
                Auth::id(),
                Auth::id(),
                force: $request->boolean('force'),
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'AI report failed: '.$e->getMessage());
        }

        return redirect()->route('mr.ai-reports.show', $report)
            ->with('success', 'Your AI report is ready.');
    }

    public function show(AiReport $aiReport)
    {
        $user = Auth::user();

        if ($aiReport->company_id !== $user->company_id) {
            abort(403);
        }

        if ($aiReport->user_id && $aiReport->user_id !== $user->id) {
            abort(403);
        }

        return view('mr.ai-reports.show', [
            'report' => $aiReport,
        ]);
    }
}
