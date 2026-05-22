<?php

namespace App\Http\Controllers;

use App\Enums\CustomerType;
use App\Models\Customer;
use App\Services\ReminderService;
use App\Services\TargetStatsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected TargetStatsService $targetStats,
        protected ReminderService $reminders,
    ) {}

    public function index(Request $request)
    {
        $company = $request->user()->company->load('plan');
        $representatives = $company->users()
            ->where('role', 'representative')
            ->latest()
            ->get();

        $customerCounts = [];
        foreach (CustomerType::cases() as $type) {
            $customerCounts[$type->value] = Customer::where('company_id', $company->id)
                ->where('type', $type)
                ->where('is_active', true)
                ->count();
        }

        return view('dashboard.index', [
            'company' => $company,
            'plan' => $company->plan,
            'representatives' => $representatives,
            'usedSlots' => $company->representativesCount(),
            'remainingSlots' => $company->remainingSlots(),
            'targetStats' => $this->targetStats->forCompany($company->id),
            'customerCounts' => $customerCounts,
            'totalCustomers' => array_sum($customerCounts),
            'reminders' => $this->reminders->upcomingForUser($request->user()->id, 5),
            'notificationsRoute' => route('admin.notifications.index'),
        ]);
    }
}
