<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CustomerType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OrderReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderReportController extends Controller
{
    public function __construct(protected OrderReportService $reports) {}

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $filters = $request->only(['user_id', 'status', 'date_from', 'date_to', 'customer_type']);

        $representatives = User::where('company_id', $companyId)
            ->where('role', 'representative')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.orders.reports', [
            'summary' => $this->reports->summary($companyId, $filters),
            'topProducts' => $this->reports->topProducts($companyId, $filters),
            'byRepresentative' => $this->reports->byRepresentative($companyId, $filters),
            'byCustomerType' => $this->reports->byCustomerType($companyId, $filters),
            'representatives' => $representatives,
            'customerTypes' => CustomerType::cases(),
            'filters' => $filters,
        ]);
    }
}
