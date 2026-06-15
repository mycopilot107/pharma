@extends('layouts.app')

@section('title', 'Doctor Visit Tracking App — GPS Proof, Photo & Signature | MR Visits Track')
@section('meta_description', 'Track every doctor visit your MRs make. GPS check-in with geo-fence, clinic photo proof, auto timestamp and doctor digital signature. Fake visits blocked automatically. Free trial for pharma companies.')
@section('canonical', route('seo.doctor-visit'))
@section('og_title', 'Doctor Visit Tracking App | MR Visits Track')

@push('schema')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How does doctor visit tracking work?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "When an MR arrives at a doctor's clinic, they check in on the app. The app verifies GPS location is within 200m of the registered clinic address, requires a photo upload, and records the exact check-in timestamp. Check-out is recorded when the MR leaves. All data is sent to the admin dashboard in real-time."
      }
    },
    {
      "@type": "Question",
      "name": "What proof is collected for each doctor visit?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Each visit collects: GPS coordinates and geo-fence verification, a clinic photo taken by the MR, auto-recorded check-in and check-out timestamps, products promoted and samples given, and an optional digital signature from the doctor."
      }
    },
    {
      "@type": "Question",
      "name": "Can MRs fake a doctor visit?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No. The app checks GPS distance from the doctor's address, detects mock location apps, blocks check-in if the MR is more than 200m away, and requires a photo. All three layers must pass for a visit to be accepted."
      }
    },
    {
      "@type": "Question",
      "name": "Is doctor signature mandatory?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Doctor signature is optional. You can configure it as required for specific visit types (e.g. hospital visits for compliance) or keep it optional. When enabled, the doctor signs on the MR's phone screen."
      }
    }
  ]
}
</script>
@endverbatim
@endpush

@section('content')

<div class="text-center">
    <span class="inline-block rounded-full bg-emerald-100 px-4 py-1 text-sm font-semibold text-emerald-700">GPS · Photo · Timestamp · Signature</span>
    <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
        Doctor Visit Tracking App
    </h1>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">
        GPS alone is not enough. MR Visits Track collects hard proof for every doctor visit — location, photo, timestamp, and signature. Every visit is verified. Every fake visit is blocked.
    </p>
    <div class="mt-8 flex flex-wrap justify-center gap-4">
        <a href="{{ route('companies.register') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-7 py-3.5 text-base font-bold text-white shadow-lg hover:bg-teal-700">
            Start 7-Day Free Demo →
        </a>
    </div>
    <p class="mt-3 text-sm text-slate-400">No credit card &nbsp;·&nbsp; Full system access &nbsp;·&nbsp; Setup in 30 minutes</p>
</div>

{{-- 4-layer proof --}}
<section class="mt-20">
    <h2 class="mb-3 text-center text-2xl font-bold text-slate-900">4-layer proof for every doctor visit</h2>
    <p class="mb-10 text-center text-slate-500">Each layer independently verifies the visit happened. All 4 together make fraud practically impossible.</p>

    <div class="relative mx-auto max-w-3xl">
        {{-- Connector line --}}
        <div class="absolute left-6 top-10 bottom-10 w-0.5 bg-teal-200 hidden sm:block"></div>

        <div class="space-y-6">
            @foreach([
                ['num'=>'1','color'=>'teal',  'title'=>'Geo-fenced GPS Check-in',     'body'=>'Check-in only accepted within 200 metres of the registered clinic address. MR standing outside a mall cannot check in at a clinic 3 km away. GPS coordinates logged server-side.'],
                ['num'=>'2','color'=>'violet','title'=>'Clinic Photo Upload',           'body'=>'MR must upload a photo at the clinic — entrance, nameplate, or selfie inside. Photo is timestamped and GPS-tagged by the camera. No photo = visit rejected.'],
                ['num'=>'3','color'=>'blue',  'title'=>'Server-side Auto Timestamp',   'body'=>'Check-in and check-out times are recorded on the server when the request arrives. The MR cannot change them from the phone. Visit duration is calculated automatically.'],
                ['num'=>'4','color'=>'amber', 'title'=>'Doctor Digital Signature',      'body'=>'Optional but powerful. Doctor signs on the MR\'s phone screen confirming the meeting. Signature is saved with the visit record and can be included in compliance reports.'],
            ] as $layer)
            <div class="flex gap-5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-{{ $layer['color'] }}-100 text-{{ $layer['color'] }}-700 font-extrabold text-lg border-2 border-{{ $layer['color'] }}-200 relative z-10">
                    {{ $layer['num'] }}
                </div>
                <div class="flex-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-bold text-slate-900">{{ $layer['title'] }}</h3>
                    <p class="mt-1.5 text-sm text-slate-500">{{ $layer['body'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- What admin sees --}}
<section class="mt-20">
    <h2 class="mb-8 text-center text-2xl font-bold text-slate-900">What the admin sees for each visit</h2>
    <div class="mx-auto max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <div class="border-b border-slate-100 bg-slate-50 px-5 py-4 flex items-center justify-between">
            <p class="font-bold text-slate-900">Visit Record</p>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Verified ✓</span>
        </div>
        <div class="divide-y divide-slate-50 px-5 py-1 text-sm">
            <div class="flex justify-between py-3"><span class="text-slate-500">MR</span><span class="font-medium text-slate-900">Ramesh Kumar</span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">Doctor</span><span class="font-medium text-slate-900">Dr. Anita Sharma, Cardiologist</span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">Clinic</span><span class="font-medium text-slate-900">City Medicare Clinic, Pune West</span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">Check-in</span><span class="font-medium text-slate-900">2:34 PM &nbsp;·&nbsp; <span class="text-emerald-600">Inside geofence ✓</span></span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">Check-out</span><span class="font-medium text-slate-900">2:51 PM &nbsp;·&nbsp; Duration: 17 min</span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">Photo proof</span><span class="font-medium text-emerald-600">3 photos uploaded ✓</span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">Doctor signature</span><span class="font-medium text-emerald-600">Signed ✓</span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">Products promoted</span><span class="font-medium text-slate-900">Cardivate 10mg, OmegaPlus</span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">Samples given</span><span class="font-medium text-slate-900">6 units</span></div>
            <div class="flex justify-between py-3"><span class="text-slate-500">GPS mock check</span><span class="font-medium text-emerald-600">No fake app detected ✓</span></div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="mt-20">
    <h2 class="mb-8 text-center text-2xl font-bold text-slate-900">Frequently Asked Questions</h2>
    <div class="mx-auto max-w-3xl divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
        @foreach([
            ['q'=>'How does doctor visit tracking work?','a'=>'The MR opens the app at the clinic, selects the doctor, and taps Check In. The app verifies GPS location is within 200m of the clinic address, requires a photo, and records the timestamp. Check-out is recorded when they leave. Everything syncs to the admin dashboard in real time.'],
            ['q'=>'What if the doctor refuses to sign?','a'=>'Doctor signature is optional and configurable. You can enable it for hospital or compliance visits and keep it optional for regular retail doctor calls. The other 3 layers of proof — GPS, photo, timestamp — are always collected regardless.'],
            ['q'=>'Can visits be tracked offline?','a'=>'Yes. If the MR loses internet connection during a visit, the app stores the visit locally and syncs it automatically when connection is restored. GPS and photo are recorded offline; server timestamp uses the sync time.'],
            ['q'=>'How many photos can an MR upload per visit?','a'=>'Up to 10 photos per visit. Typically MRs upload 1–3: entrance sign, meeting photo, product display. All photos are stored in the company\'s account and accessible to admin.'],
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
    <h2 class="text-2xl font-bold text-slate-900">Start verifying every doctor visit</h2>
    <p class="mt-2 text-slate-500">7-day full demo. No credit card. Setup in 30 minutes.</p>
    <a href="{{ route('companies.register') }}"
        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-8 py-4 text-base font-bold text-white shadow-lg hover:bg-teal-700">
        Start Free Demo →
    </a>
    <div class="mt-6 flex flex-wrap justify-center gap-6 text-sm text-slate-400">
        <a href="{{ route('seo.mr-tracking') }}" class="hover:text-teal-600">MR Tracking App →</a>
        <a href="{{ route('seo.mr-reporting') }}" class="hover:text-teal-600">MR Reporting Software →</a>
        <a href="{{ route('seo.pharma-crm') }}" class="hover:text-teal-600">Pharma CRM for MRs →</a>
    </div>
</section>

@endsection
