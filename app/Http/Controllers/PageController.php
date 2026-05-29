<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function faq(): View
    {
        return view('pages.faq');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function policy(): View
    {
        return view('pages.policy');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function pricing(): View
    {
        $plans = Plan::where('is_active', true)->orderBy('user_limit')->get();

        return view('pages.pricing', compact('plans'));
    }

    public function features(): View
    {
        return view('pages.features');
    }

    public function appDownload(): View
    {
        return view('pages.app');
    }
}
