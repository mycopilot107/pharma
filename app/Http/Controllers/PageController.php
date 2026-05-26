<?php

namespace App\Http\Controllers;

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
}
