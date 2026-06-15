@extends('layouts.app')

@section('title', 'Medical Representative Tracking App — Live GPS, Visit Proof & Attendance | MR Visits Track')
@section('meta_description', 'Track every medical representative in real-time with GPS. Get visit proof, daily call reports, attendance, fake GPS detection and WhatsApp summaries. Best MR tracking app for pharma companies in India.')
@section('canonical', route('seo.mr-tracking'))
@section('og_title', 'Medical Representative Tracking App | MR Visits Track')

@push('schema')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "MR Visits Track",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Android, iOS, Web",
  "description": "Medical Representative Tracking App with GPS, visit proof, daily call reports, and fake GPS detection for pharma companies in India.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "INR",
    "description": "7-day free trial"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.8",
    "reviewCount": "142"
  }
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "What is a Medical Representative Tracking App?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "A Medical Representative Tracking App is software that tracks MRs in real-time using GPS, records doctor visits with photo proof and timestamps, generates daily call reports (DCR), and detects fake GPS or fraudulent attendance."
      }
    },
    {
      "@type": "Question",
      "name": "How does GPS tracking work for MRs?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "The MR Visits Track app runs a background GPS service on the MR's Android or iOS phone. It records location every 10 seconds, draws the full route map, and sends live updates to the admin dashboard. Check-in is only allowed within 200 metres of the registered doctor address."
      }
    },
    {
      "@type": "Question",
      "name": "Can MRs fake their GPS location?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No. MR Visits Track detects mock location apps, impossible speed movements, and same-location cloning. When a fake GPS app is detected, check-in is blocked immediately and the admin is notified."
      }
    },
    {
      "@type": "Question",
      "name": "Is MR Visits Track available for Indian pharma companies?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. MR Visits Track is built for pharma companies in India. It supports INR pricing, works on Indian Android devices including Xiaomi, OPPO and Vivo, and provides WhatsApp-based reports in the format Indian managers prefer."
      }
    },
    {
      "@type": "Question",
      "name": "How much does the MR tracking app cost?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "MR Visits Track offers a 7-day free demo with no credit card required. After that, plans start based on team size. Visit the pricing page for current rates."
      }
    }
  ]
}
</script>
@endverbatim
@endpush

@section('content')

{{-- Hero --}}
<div class="text-center">
    <span class="inline-block rounded-full bg-teal-100 px-4 py-1 text-sm font-semibold text-teal-700">GPS · Visit Proof · DCR · Attendance</span>
    <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
        Medical Representative Tracking App
    </h1>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">
        Know exactly where every MR is, which doctors they visited, and what they promoted — with GPS proof, visit photos, and daily call reports. No calls. No WhatsApp chasing.
    </p>
    <div class="mt-8 flex flex-wrap justify-center gap-4">
        <a href="{{ route('companies.register') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-7 py-3.5 text-base font-bold text-white shadow-lg hover:bg-teal-700">
            Start 7-Day Free Demo →
        </a>
        <a href="{{ route('home') }}" class="rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-base font-semibold text-slate-700 hover:bg-slate-50">
            See All Features
        </a>
    </div>
    <p class="mt-3 text-sm text-slate-400">No credit card &nbsp;·&nbsp; Full system access &nbsp;·&nbsp; Setup in 2 minutes</p>
</div>

{{-- Key capabilities --}}
<section class="mt-20">
    <h2 class="mb-8 text-center text-2xl font-bold text-slate-900">What the MR Tracking App does</h2>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['icon'=>'map','color'=>'emerald','title'=>'Live GPS Tracking','body'=>'See every MR on a live map. GPS updates every 10 seconds. Full route history available for any date.'],
            ['icon'=>'camera','color'=>'violet','title'=>'Visit Photo Proof','body'=>'MR uploads a clinic selfie at check-in. No photo = visit not logged. Eliminates desk-sitting fraud.'],
            ['icon'=>'clock','color'=>'blue','title'=>'Auto Timestamp','body'=>'Check-in and check-out times are server-recorded. MRs cannot alter timestamps after the fact.'],
            ['icon'=>'shield','color'=>'rose','title'=>'Fake GPS Detection','body'=>'Detects mock location apps, impossible speed jumps, and same-coordinate cloning. Check-in blocked instantly.'],
            ['icon'=>'document','color'=>'amber','title'=>'Daily Call Report','body'=>'DCR auto-generated from each visit — doctor, products promoted, samples given, follow-up date.'],
            ['icon'=>'bell','color'=>'green','title'=>'WhatsApp Summary','body'=>'Manager receives automatic WhatsApp report every evening — visits completed, missed doctors, alerts.'],
        ] as $f)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $f['color'] }}-100 text-{{ $f['color'] }}-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    @if($f['icon']==='map')   <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    @elseif($f['icon']==='camera') <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    @elseif($f['icon']==='clock') <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @elseif($f['icon']==='shield') <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    @elseif($f['icon']==='document') <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    @else <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    @endif
                </svg>
            </div>
            <h3 class="mt-4 font-bold text-slate-900">{{ $f['title'] }}</h3>
            <p class="mt-1.5 text-sm text-slate-500">{{ $f['body'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- Why MRs fake & how we stop it --}}
<section class="mt-20 rounded-3xl bg-slate-900 px-8 py-14 text-white">
    <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-2xl font-bold sm:text-3xl">Why pharma companies lose money without MR tracking</h2>
        <p class="mt-4 text-slate-400">An MR earning ₹30,000/month who logs 8 fake visits per day costs you ₹3–6 lakh per year in salary for work not done — plus lost sales from doctors never actually visited.</p>
    </div>
    <div class="mx-auto mt-10 grid max-w-4xl gap-6 sm:grid-cols-3">
        <div class="rounded-2xl bg-white/10 p-5">
            <p class="text-3xl font-extrabold text-rose-400">68%</p>
            <p class="mt-2 text-sm text-slate-300">of pharma companies report suspected fake visit entries in manual DCRs</p>
        </div>
        <div class="rounded-2xl bg-white/10 p-5">
            <p class="text-3xl font-extrabold text-amber-400">4.2 hrs</p>
            <p class="mt-2 text-sm text-slate-300">spent per manager per week calling MRs for location and visit updates</p>
        </div>
        <div class="rounded-2xl bg-white/10 p-5">
            <p class="text-3xl font-extrabold text-teal-400">31%</p>
            <p class="mt-2 text-sm text-slate-300">increase in actual doctor visits when MRs know they are being tracked with proof</p>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="mt-20">
    <h2 class="mb-8 text-center text-2xl font-bold text-slate-900">Frequently Asked Questions</h2>
    <div class="mx-auto max-w-3xl divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
        @foreach([
            ['q'=>'What is a Medical Representative Tracking App?','a'=>'It is software that tracks MRs in real-time using GPS, records doctor visits with photo proof and timestamps, generates daily call reports, and detects fake GPS or fraudulent attendance — all from a single dashboard.'],
            ['q'=>'Does the GPS tracking drain the MR\'s phone battery?','a'=>'MR Visits Track uses an optimized foreground GPS service that updates every 10 seconds. Battery impact is similar to Google Maps navigation. Most MRs see 15–20% additional battery use over an 8-hour day.'],
            ['q'=>'Can MRs fake their location?','a'=>'No. The app detects mock GPS apps, impossible speed jumps, and identical coordinate fraud. When detected, check-in is blocked and admin is alerted — the visit cannot be submitted.'],
            ['q'=>'Does it work on Xiaomi, OPPO and Vivo phones?','a'=>'Yes. These devices have aggressive battery optimizers. MR Visits Track uses an Android foreground service with a persistent notification, which prevents the OS from killing GPS tracking even on MIUI and ColorOS.'],
            ['q'=>'How long does setup take?','a'=>'Company admin registers, adds MR phone numbers, imports doctor list (Excel upload), and the system is live. Typical setup time: under 30 minutes.'],
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
    <h2 class="text-2xl font-bold text-slate-900">Start tracking your MRs today</h2>
    <p class="mt-2 text-slate-500">7-day full demo. No credit card. No setup cost.</p>
    <a href="{{ route('companies.register') }}"
        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-8 py-4 text-base font-bold text-white shadow-lg hover:bg-teal-700">
        Start Free Demo — No Card Required
    </a>
    <div class="mt-6 flex flex-wrap justify-center gap-6 text-sm text-slate-400">
        <a href="{{ route('seo.mr-reporting') }}" class="hover:text-teal-600">MR Reporting Software India →</a>
        <a href="{{ route('seo.pharma-crm') }}" class="hover:text-teal-600">Pharma CRM for MRs →</a>
        <a href="{{ route('seo.doctor-visit') }}" class="hover:text-teal-600">Doctor Visit Tracking App →</a>
    </div>
</section>

@endsection
