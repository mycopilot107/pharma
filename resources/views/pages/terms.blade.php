@extends('layouts.app')

@section('title', 'Terms of Service — ' . config('pharma.app_name'))

@section('content')
<div class="mx-auto max-w-3xl">

    <div class="text-center">
        <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-700">Legal</span>
        <h1 class="mt-4 text-3xl font-bold text-slate-900">Terms of Service</h1>
        <p class="mt-2 text-sm text-slate-500">Last updated: {{ date('F d, Y') }}</p>
    </div>

    <div class="mt-10 space-y-8 text-slate-700 leading-relaxed">

        <section>
            <h2 class="text-lg font-semibold text-slate-900">1. Acceptance of Terms</h2>
            <p class="mt-3">By registering a company account or using the {{ config('pharma.app_name') }} platform (including the web portal and mobile application), you agree to be bound by these Terms of Service. If you do not agree, please do not use the service.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">2. Description of Service</h2>
            <p class="mt-3">{{ config('pharma.app_name') }} provides a cloud-based SaaS platform for pharmaceutical companies to manage field medical representatives (MRs), track GPS-based visits, manage customers, expenses, orders, leave, and generate AI-powered reports. The platform is accessible via web browser and mobile application (Android and iOS).</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">3. Accounts & Registration</h2>
            <ul class="mt-3 space-y-2 list-disc list-inside text-sm">
                <li>You must provide accurate and complete information during registration.</li>
                <li>Each company account is managed by a Company Admin who is responsible for all activity under that account.</li>
                <li>You are responsible for maintaining the security of your login credentials.</li>
                <li>One person or entity may not create multiple company accounts to circumvent plan limits.</li>
                <li>We reserve the right to suspend or terminate accounts that violate these terms.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">4. Subscription & Billing</h2>
            <ul class="mt-3 space-y-2 list-disc list-inside text-sm">
                <li>Paid plans are billed on a monthly basis at <strong>${{ config('pharma.price_per_user_usd', 3) }} per user per month</strong>.</li>
                <li>Subscriptions are activated upon successful payment via Razorpay.</li>
                <li>The Free plan is available at no cost and includes 1 user slot.</li>
                <li>Paid subscriptions are valid for 30 days from the date of payment.</li>
                <li>We do not offer refunds for partial months or unused periods.</li>
                <li>If a subscription expires, the account will be deactivated until renewed.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">5. Acceptable Use</h2>
            <p class="mt-3">You agree not to:</p>
            <ul class="mt-2 space-y-2 list-disc list-inside text-sm">
                <li>Use the platform for any unlawful, fraudulent, or harmful purpose.</li>
                <li>Attempt to reverse engineer, hack, or disrupt the service.</li>
                <li>Upload malicious content, viruses, or spam.</li>
                <li>Share your account credentials with unauthorised persons.</li>
                <li>Use the GPS tracking features to monitor employees in violation of applicable Indian labour laws.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">6. Employee Location Tracking</h2>
            <p class="mt-3">{{ config('pharma.app_name') }} provides GPS tracking tools intended solely for business field visit management. By using these features:</p>
            <ul class="mt-2 space-y-2 list-disc list-inside text-sm">
                <li>You confirm that your employees (medical representatives) have been informed and have consented to location tracking during work hours.</li>
                <li>Tracking is active only when the representative clocks in for the day.</li>
                <li>You are solely responsible for compliance with Indian labour laws and data protection regulations regarding employee monitoring.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">7. Data Ownership</h2>
            <p class="mt-3">You retain ownership of all data you input into the platform (company data, customer records, visit logs, etc.). By using the service, you grant us a limited, non-exclusive licence to process and store this data solely to operate and improve the platform.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">8. Service Availability</h2>
            <p class="mt-3">We strive for 99% uptime but do not guarantee uninterrupted access. Scheduled maintenance, force majeure events, or third-party outages may temporarily affect availability. We are not liable for losses resulting from downtime.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">9. Limitation of Liability</h2>
            <p class="mt-3">To the maximum extent permitted by law, {{ config('pharma.app_name') }} shall not be liable for any indirect, incidental, or consequential damages arising from your use or inability to use the service, including loss of data or business interruption.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">10. Changes to Terms</h2>
            <p class="mt-3">We may update these Terms from time to time. Continued use of the platform after changes are posted constitutes acceptance of the updated Terms. We will notify you of significant changes via email.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">11. Governing Law</h2>
            <p class="mt-3">These Terms are governed by the laws of India. Any disputes shall be subject to the exclusive jurisdiction of the courts of India.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">12. Contact</h2>
            <p class="mt-3">For questions about these Terms, contact us at <a href="mailto:support@webauditaitool.com" class="text-teal-600 hover:underline">support@webauditaitool.com</a>.</p>
        </section>

    </div>

    <div class="mt-10 flex gap-4 text-sm">
        <a href="{{ route('policy') }}" class="text-teal-600 hover:underline">Privacy Policy →</a>
        <a href="{{ route('contact') }}" class="text-teal-600 hover:underline">Contact Us →</a>
    </div>

</div>
@endsection
