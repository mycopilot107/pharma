<?php

namespace App\Http\Controllers;

use App\Models\Plan;

class SeoController extends Controller
{
    public function mrTrackingApp()
    {
        $plans = Plan::where('is_active', true)->orderBy('user_limit')->get();
        return view('seo.mr-tracking-app', compact('plans'));
    }

    public function mrReportingSoftware()
    {
        $plans = Plan::where('is_active', true)->orderBy('user_limit')->get();
        return view('seo.mr-reporting-india', compact('plans'));
    }

    public function pharmaCrm()
    {
        $plans = Plan::where('is_active', true)->orderBy('user_limit')->get();
        return view('seo.pharma-crm', compact('plans'));
    }

    public function doctorVisitTracking()
    {
        $plans = Plan::where('is_active', true)->orderBy('user_limit')->get();
        return view('seo.doctor-visit-tracking', compact('plans'));
    }
}
