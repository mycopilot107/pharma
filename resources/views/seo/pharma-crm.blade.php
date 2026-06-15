@extends('layouts.app')

@section('title', 'Pharma CRM for Medical Representatives — Doctor Management & Visit History | MR Visits Track')
@section('meta_description', 'Complete Pharma CRM for medical representatives. Manage doctors, chemists and hospitals. Track visit history, product promotion, follow-up dates and relationship notes. Built for Indian pharma companies.')
@section('canonical', route('seo.pharma-crm'))
@section('og_title', 'Pharma CRM for Medical Representatives | MR Visits Track')

@push('schema')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "What is Pharma CRM?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Pharma CRM is customer relationship management software built for pharmaceutical companies. It helps medical representatives manage their doctor, chemist and hospital contacts, track visit history, log products promoted, set follow-up reminders, and maintain relationship notes — all from a mobile app."
      }
    },
    {
      "@type": "Question",
      "name": "How is Pharma CRM different from regular CRM?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Regular CRM is built for sales calls and email follow-ups. Pharma CRM is built for face-to-face doctor visits. It includes GPS check-in, visit photo proof, sample distribution tracking, product promotion logging, and compliance-ready reports — features no generic CRM provides."
      }
    },
    {
      "@type": "Question",
      "name": "Can MR Visits Track manage doctors, chemists and hospitals separately?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. The CRM module has separate customer types: Doctor, Chemist (retail pharmacy), and Hospital/Institution. Each type has relevant fields, visit frequency targets, and area mapping."
      }
    }
  ]
}
</script>
@endverbatim
@endpush

@section('content')

<div class="text-center">
    <span class="inline-block rounded-full bg-violet-100 px-4 py-1 text-sm font-semibold text-violet-700">Doctor · Chemist · Hospital Management</span>
    <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
        Pharma CRM for Medical Representatives
    </h1>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">
        Manage every doctor, chemist and hospital your MRs visit. Full visit history, product promotion tracking, follow-up reminders and relationship notes — in one place.
    </p>
    <div class="mt-8 flex flex-wrap justify-center gap-4">
        <a href="{{ route('companies.register') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-7 py-3.5 text-base font-bold text-white shadow-lg hover:bg-teal-700">
            Start 7-Day Free Demo →
        </a>
    </div>
    <p class="mt-3 text-sm text-slate-400">No credit card &nbsp;·&nbsp; Full access &nbsp;·&nbsp; Setup in 30 minutes</p>
</div>

{{-- CRM features grid --}}
<section class="mt-20">
    <h2 class="mb-8 text-center text-2xl font-bold text-slate-900">Built for how pharma field sales actually works</h2>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['color'=>'violet','title'=>'Doctor Profiles','body'=>'Full profile: name, speciality, clinic address, prescription behaviour, preferred brands. MRs see the history before every visit.'],
            ['color'=>'teal',  'title'=>'Chemist & Hospital Records','body'=>'Separate modules for retail pharmacies and hospitals. Track stocking patterns, prescription volumes, and institution-level contacts.'],
            ['color'=>'blue',  'title'=>'Visit History Timeline','body'=>'Every visit is recorded — when, by which MR, what was promoted, how many samples were given. Full history visible to admin and MR.'],
            ['color'=>'amber', 'title'=>'Follow-up Reminders','body'=>'MR sets next visit date per doctor. App sends push notification before the date. Admin sees which doctors are overdue for a visit.'],
            ['color'=>'emerald','title'=>'Product Promotion Tracking','body'=>'See which products are being promoted to which doctors, by which MR. Identify gaps — doctors who never heard about a specific product.'],
            ['color'=>'rose',  'title'=>'Area & Route Mapping','body'=>'Customers grouped by area. MR plans visits by territory. Admin can see doctor density per zone and assign areas to specific MRs.'],
        ] as $f)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="h-2 w-10 rounded-full bg-{{ $f['color'] }}-500 mb-4"></div>
            <h3 class="font-bold text-slate-900">{{ $f['title'] }}</h3>
            <p class="mt-2 text-sm text-slate-500">{{ $f['body'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- Doctor card mockup --}}
<section class="mt-20">
    <h2 class="mb-8 text-center text-2xl font-bold text-slate-900">What an MR sees before every doctor visit</h2>
    <div class="mx-auto max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="bg-violet-600 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-violet-200">Doctor Profile</p>
            <p class="mt-1 text-lg font-bold text-white">Dr. Anita Sharma</p>
            <p class="text-sm text-violet-200">Cardiologist · City Medicare Clinic, Pune West</p>
        </div>
        <div class="divide-y divide-slate-100 px-5 py-1">
            <div class="flex justify-between py-3 text-sm"><span class="text-slate-500">Last visited</span><span class="font-medium text-slate-900">3 days ago by Ramesh Kumar</span></div>
            <div class="flex justify-between py-3 text-sm"><span class="text-slate-500">Visit frequency</span><span class="font-medium text-slate-900">Every 7 days (target)</span></div>
            <div class="flex justify-between py-3 text-sm"><span class="text-slate-500">Products promoted</span><span class="font-medium text-slate-900">Cardivate, OmegaPlus</span></div>
            <div class="flex justify-between py-3 text-sm"><span class="text-slate-500">Samples given (this month)</span><span class="font-medium text-slate-900">18 units</span></div>
            <div class="flex justify-between py-3 text-sm"><span class="text-slate-500">Prescription potential</span><span class="font-medium text-emerald-600">High — 40+ patients/week</span></div>
            <div class="flex justify-between py-3 text-sm"><span class="text-slate-500">Follow-up due</span><span class="font-medium text-amber-600">Tomorrow</span></div>
            <div class="py-3">
                <p class="text-xs font-semibold text-slate-500 mb-1">Notes from last visit</p>
                <p class="text-sm text-slate-700">"Interested in OmegaPlus trial. Wants clinical data. Send brochure."</p>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="mt-20">
    <h2 class="mb-8 text-center text-2xl font-bold text-slate-900">Frequently Asked Questions</h2>
    <div class="mx-auto max-w-3xl divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
        @foreach([
            ['q'=>'What is Pharma CRM?','a'=>'Pharma CRM is customer relationship management built for pharmaceutical field teams. It manages doctor, chemist and hospital contacts, tracks visit history, logs product promotion, sets follow-up reminders, and maintains compliance-ready visit records — replacing paper doctor lists and Excel call planners.'],
            ['q'=>'How is it different from regular CRM like Salesforce?','a'=>'Regular CRM is built for B2B sales and email follow-ups. Pharma CRM has GPS check-in, visit photo proof, sample distribution logging, tour plan management and product-level promotion tracking — features pharma field teams need that no generic CRM provides.'],
            ['q'=>'Can I import my existing doctor list?','a'=>'Yes. You can upload an Excel file with your existing doctor, chemist and hospital list. MRs start seeing the full database on day one without having to add contacts manually.'],
            ['q'=>'Does it track which products each doctor is being pitched?','a'=>'Yes. For every visit, the MR selects which products were promoted from your company\'s product catalogue. Admin can see product-doctor coverage reports — identifying doctors who have never been shown a specific product.'],
        ] as $faq)
        <details class="group px-6 py-5">
            <summary class="flex cursor-pointer items-center justify-between font-semibold text-slate-900 marker:content-['']">
                {{ $faq['q'] }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-slate-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $faq['a'] }}</p>
        </details>
        @endforeach
    </div>
</section>

<section class="mt-16 text-center">
    <h2 class="text-2xl font-bold text-slate-900">Give your MRs a CRM built for pharma</h2>
    <p class="mt-2 text-slate-500">7-day full demo. No credit card. Full doctor database included.</p>
    <a href="{{ route('companies.register') }}"
        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-8 py-4 text-base font-bold text-white shadow-lg hover:bg-teal-700">
        Start Free Demo →
    </a>
    <div class="mt-6 flex flex-wrap justify-center gap-6 text-sm text-slate-400">
        <a href="{{ route('seo.mr-tracking') }}" class="hover:text-teal-600">MR Tracking App →</a>
        <a href="{{ route('seo.mr-reporting') }}" class="hover:text-teal-600">MR Reporting Software →</a>
        <a href="{{ route('seo.doctor-visit') }}" class="hover:text-teal-600">Doctor Visit Tracking →</a>
    </div>
</section>

@endsection
