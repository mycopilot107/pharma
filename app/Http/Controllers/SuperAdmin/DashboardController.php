<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $companies = Company::with('plan')
            ->withCount([
                'users as admins_count' => fn ($q) => $q->where('role', UserRole::CompanyAdmin),
                'users as reps_count' => fn ($q) => $q->where('role', UserRole::Representative),
            ])
            ->latest()
            ->paginate(20);

        $stats = [
            'companies_total' => Company::count(),
            'companies_active' => Company::where('status', Company::STATUS_ACTIVE)->count(),
            'companies_inactive' => Company::where('status', Company::STATUS_SUSPENDED)->count(),
            'companies_pending' => Company::where('status', Company::STATUS_PENDING)->count(),
            'representatives' => User::where('role', UserRole::Representative)->count(),
            'plans' => Plan::count(),
        ];

        return view('super-admin.dashboard', [
            'companies' => $companies,
            'stats' => $stats,
        ]);
    }
}
