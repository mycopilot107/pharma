@extends('layouts.app')

@section('title', 'MR Reporting Software India — DCR, Attendance & Expense Reports | MedRep Fleet')
@section('meta_description', 'Automate MR daily call reports (DCR), attendance, expense claims and WhatsApp summaries for pharma companies across India. No Excel. No manual filling. Reports ready in seconds.')
@section('canonical', route('seo.mr-reporting'))
@section('og_title', 'MR Reporting Software India | MedRep Fleet')

@push('schema')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "What is MR Reporting Software?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "MR Reporting Software automates Daily Call Reports (DCR), attendance, expense claims, leave management and performance summaries for medical representatives. It replaces paper DCRs and Excel sheets with a mobile app that fills reports automatically as MRs do their field visits."
      }
    },
    {
      "@type": "Question",
      "name": "Does MedRep Fleet work for Indian pharma companies?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. MedRep Fleet is built specifically for the Indian pharma market. It supports INR, works on affordable Android phones, sends WhatsApp reports in Hindi-friendly formats, and is priced for Indian company budgets."
      }
    },
    {
      "@type": "Question",
      "name": "Can MR reports be exported to Excel or PDF?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. All reports — DCR, attendance, expense summaries, tour plan vs. actual — can be exported as Excel or PDF for management review meetings or distributor reporting."
      }
    },
    {
      "@type": "Question",
      "name": "Does the app send WhatsApp reports automatically?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. At the end of each day (configurable time, typically 7 PM), the system sends an automatic WhatsApp message to the manager with a complete summary of every MR's visits, missed doctors, samples given, and any fraud alerts."
      }
    }
  ]
}
</script>
@endverbatim
@endpush

@section('content')

<div class="text-center">
    <span class="inline-block rounded-full bg-blue-100 px-4 py-1 text-sm font-semibold text-blue-700">DCR · Attendance · Expenses · WhatsApp Reports</span>
    <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
        MR Reporting Software for India
    </h1>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">
        Stop chasing MRs for reports. MedRep Fleet auto-generates DCR, attendance, expenses and WhatsApp summaries — without the MR typing a single word at end of day.
    </p>
    <div class="mt-8 flex flex-wrap justify-center gap-4">
        <a href="{{ route('companies.register') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-7 py-3.5 text-base font-bold text-white shadow-lg hover:bg-teal-700">
            Start 7-Day Free Demo →
        </a>
    </div>
    <p class="mt-3 text-sm text-slate-400">No credit card &nbsp;·&nbsp; Full system access &nbsp;·&nbsp; Setup in under 30 minutes</p>
</div>

{{-- Reports overview --}}
<section class="mt-20">
    <h2 class="mb-8 text-center text-2xl font-bold text-slate-900">Every report. Automatically. Without follow-up.</h2>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['color'=>'blue',  'title'=>'Daily Call Report (DCR)',  'body'=>'Auto-generated from each visit. Doctor name, products promoted, samples given, follow-up date — all captured as the MR visits, not filled from memory later.'],
            ['color'=>'emerald','title'=>'Attendance & Clock-in/out','body'=>'GPS-verified clock-in from the field. Admin sees who is on duty and when. No proxy attendance. Works even on Xiaomi and OPPO phones.'],
            ['color'=>'orange', 'title'=>'Expense Reports',         'body'=>'MR uploads fuel, hotel, or food receipts from the app. Admin approves or rejects with one click. Full expense history in one place.'],
            ['color'=>'green',  'title'=>'WhatsApp Daily Summary',  'body'=>'Manager gets an automatic WhatsApp message every evening — visits, missed doctors, samples, alerts. No login to any dashboard required.'],
            ['color'=>'violet', 'title'=>'Tour Plan vs. Actual',    'body'=>'MR submits weekly tour plan. System tracks execution. Admin sees planned vs. actual visits per doctor, per area, per day.'],
            ['color'=>'rose',   'title'=>'Leave Management',        'body'=>'MR applies for leave from the app. Manager approves or rejects. Leave history maintained automatically with attendance records.'],
        ] as $r)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="h-2 w-10 rounded-full bg-{{ $r['color'] }}-500 mb-4"></div>
            <h3 class="font-bold text-slate-900">{{ $r['title'] }}</h3>
            <p class="mt-2 text-sm text-slate-500">{{ $r['body'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- Problem / solution --}}
<section class="mt-20 grid gap-8 lg:grid-cols-2 items-center">
    <div class="rounded-2xl bg-slate-100 p-8">
        <p class="text-xs font-bold uppercase tracking-widest text-rose-600 mb-3">❌ Before MedRep Fleet</p>
        <ul class="space-y-3 text-sm text-slate-700">
            @foreach(['MR fills DCR on paper or WhatsApp at night — from memory','Manager calls MRs at 5 PM asking for location update','Expense claims on WhatsApp — receipts lost in chat','Leave applications on calls — no record maintained','Monthly review: manager collates Excel sheets from 10 MRs','No way to verify if visits actually happened'] as $item)
            <li class="flex items-start gap-2"><span class="mt-0.5 text-rose-500">✗</span> {{ $item }}</li>
            @endforeach
        </ul>
    </div>
    <div class="rounded-2xl bg-teal-50 border border-teal-200 p-8">
        <p class="text-xs font-bold uppercase tracking-widest text-teal-600 mb-3">✅ After MedRep Fleet</p>
        <ul class="space-y-3 text-sm text-slate-700">
            @foreach(['DCR auto-filled from visit — zero effort by MR','Live GPS dashboard — see location without calling','Receipt photo in app — expense approved in one click','Leave applied and approved in app — history maintained','Monthly report auto-generated — download Excel in 10 seconds','GPS + photo proof for every single visit'] as $item)
            <li class="flex items-start gap-2"><span class="mt-0.5 text-teal-600">✓</span> {{ $item }}</li>
            @endforeach
        </ul>
    </div>
</section>

{{-- FAQ --}}
<section class="mt-20">
    <h2 class="mb-8 text-center text-2xl font-bold text-slate-900">Frequently Asked Questions</h2>
    <div class="mx-auto max-w-3xl divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
        @foreach([
            ['q'=>'What is MR Reporting Software?','a'=>'MR Reporting Software automates Daily Call Reports (DCR), attendance, expense claims, leave management and WhatsApp summaries for medical representatives in pharma companies — replacing paper forms and Excel sheets.'],
            ['q'=>'Does it replace manual DCR filling?','a'=>'Yes. As the MR checks in to a doctor visit, the app captures location, time, products selected and samples distributed. The DCR is built automatically — the MR does not need to write anything after the visit.'],
            ['q'=>'Can reports be exported to Excel or PDF?','a'=>'Yes. All reports — DCR, attendance, expenses, tour plan vs. actual — export to Excel or PDF with one click. Ready for review meetings or distributor reporting.'],
            ['q'=>'Does it work for small pharma companies in India?','a'=>'Yes. Plans start at a very low per-MR monthly cost. There is no setup fee and no minimum team size. A company with 3 MRs can use it as effectively as a company with 300 MRs.'],
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

{{-- CTA --}}
<section class="mt-16 text-center">
    <h2 class="text-2xl font-bold text-slate-900">See how much time your team saves</h2>
    <p class="mt-2 text-slate-500">7-day full demo. No credit card. Cancel anytime.</p>
    <a href="{{ route('companies.register') }}"
        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-8 py-4 text-base font-bold text-white shadow-lg hover:bg-teal-700">
        Start Free Demo →
    </a>
    <div class="mt-6 flex flex-wrap justify-center gap-6 text-sm text-slate-400">
        <a href="{{ route('seo.mr-tracking') }}" class="hover:text-teal-600">MR Tracking App →</a>
        <a href="{{ route('seo.pharma-crm') }}" class="hover:text-teal-600">Pharma CRM for MRs →</a>
        <a href="{{ route('seo.doctor-visit') }}" class="hover:text-teal-600">Doctor Visit Tracking →</a>
    </div>
</section>

@endsection
