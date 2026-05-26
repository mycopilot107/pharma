@extends('layouts.app')

@section('title', 'Privacy Policy — ' . config('pharma.app_name'))

@section('content')
<div class="mx-auto max-w-3xl">

    <div class="text-center">
        <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-700">Legal</span>
        <h1 class="mt-4 text-3xl font-bold text-slate-900">Privacy Policy</h1>
        <p class="mt-2 text-sm text-slate-500">Last updated: {{ date('F d, Y') }}</p>
    </div>

    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
        Your privacy matters to us. This policy explains what data we collect, why we collect it, and how it is protected.
    </div>

    <div class="mt-8 space-y-8 text-slate-700 leading-relaxed">

        <section>
            <h2 class="text-lg font-semibold text-slate-900">1. Who We Are</h2>
            <p class="mt-3">{{ config('pharma.app_name') }} is operated by the {{ config('pharma.app_name') }} team. For privacy-related queries, contact us at <a href="mailto:shobha.solanki107@gmail.com" class="text-teal-600 hover:underline">shobha.solanki107@gmail.com</a>.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">2. Data We Collect</h2>

            <h3 class="mt-4 font-medium text-slate-800">Company & Account Data</h3>
            <ul class="mt-2 space-y-1 list-disc list-inside text-sm">
                <li>Company name, email address, phone number, address</li>
                <li>Admin name, email address, and hashed password</li>
                <li>Subscription plan and payment status</li>
            </ul>

            <h3 class="mt-4 font-medium text-slate-800">Medical Representative Data</h3>
            <ul class="mt-2 space-y-1 list-disc list-inside text-sm">
                <li>Name, email address, phone number</li>
                <li>GPS location data (latitude, longitude, timestamp) during active work sessions</li>
                <li>Attendance clock-in/clock-out records</li>
                <li>Visit records, photos, and notes</li>
                <li>Expense reports and receipts</li>
                <li>Order submissions and leave requests</li>
            </ul>

            <h3 class="mt-4 font-medium text-slate-800">Customer (Doctor/Chemist) Data</h3>
            <ul class="mt-2 space-y-1 list-disc list-inside text-sm">
                <li>Name, specialisation, clinic/shop name, contact details</li>
                <li>Address and GPS coordinates for geofencing</li>
                <li>Visit history, prescriptions, and purchase records</li>
            </ul>

            <h3 class="mt-4 font-medium text-slate-800">Technical Data</h3>
            <ul class="mt-2 space-y-1 list-disc list-inside text-sm">
                <li>Device type and operating system (mobile app)</li>
                <li>Session logs and error logs</li>
                <li>IP address and browser type (web portal)</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">3. How We Use Your Data</h2>
            <ul class="mt-3 space-y-2 list-disc list-inside text-sm">
                <li>To provide and operate the visit tracking and MR management platform.</li>
                <li>To authenticate users and maintain account security.</li>
                <li>To process subscription payments via Razorpay.</li>
                <li>To generate visit reports, analytics, and AI summaries for your company.</li>
                <li>To send account-related notifications (leave approvals, expense updates, etc.) via email.</li>
                <li>To detect fraudulent GPS activity and maintain data integrity.</li>
                <li>To improve our platform and fix technical issues.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">4. GPS & Location Data</h2>
            <p class="mt-3">GPS location is the core functionality of this platform. Here is how we handle it:</p>
            <ul class="mt-2 space-y-2 list-disc list-inside text-sm">
                <li><strong>Collection:</strong> Location pings are collected every 30–60 seconds during the MR's active work session (after clock-in, before clock-out).</li>
                <li><strong>Purpose:</strong> To verify field visits, generate route history, and enable geofence check-ins.</li>
                <li><strong>Consent:</strong> Representatives must clock in to activate tracking — the app does not track passively without a logged-in, active session.</li>
                <li><strong>Access:</strong> Location data is accessible only to the Company Admin and authorised managers of the same company.</li>
                <li><strong>Retention:</strong> Location history is retained for the duration of the subscription and deleted upon account termination.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">5. Data Sharing</h2>
            <p class="mt-3">We do <strong>not sell</strong> your data. We share data only with:</p>
            <ul class="mt-2 space-y-2 list-disc list-inside text-sm">
                <li><strong>Razorpay</strong> — for payment processing. Subject to Razorpay's privacy policy.</li>
                <li><strong>OpenAI</strong> — visit data summaries are sent to OpenAI's API to generate AI reports. No personally identifiable information (PII) is included in these requests.</li>
                <li><strong>Google Maps</strong> — for geocoding and map display. Only coordinates are shared.</li>
                <li><strong>Email provider (Gmail SMTP)</strong> — for sending transactional emails.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">6. Data Security</h2>
            <ul class="mt-3 space-y-2 list-disc list-inside text-sm">
                <li>All data is transmitted over HTTPS (TLS encryption).</li>
                <li>Passwords are hashed using bcrypt and never stored in plain text.</li>
                <li>API tokens (Sanctum) are stored securely in the device keychain on mobile.</li>
                <li>Database access is restricted to authorised server infrastructure.</li>
                <li>We perform regular backups of company data.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">7. Your Rights</h2>
            <p class="mt-3">As a company admin or registered user, you have the right to:</p>
            <ul class="mt-2 space-y-2 list-disc list-inside text-sm">
                <li><strong>Access</strong> — Request a copy of your data held on our platform.</li>
                <li><strong>Correction</strong> — Update incorrect personal information via your account settings.</li>
                <li><strong>Deletion</strong> — Request deletion of your account and all associated data by emailing us.</li>
                <li><strong>Portability</strong> — Request an export of your data in a structured format.</li>
            </ul>
            <p class="mt-3 text-sm">To exercise these rights, email <a href="mailto:shobha.solanki107@gmail.com" class="text-teal-600 hover:underline">shobha.solanki107@gmail.com</a>.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">8. Cookies</h2>
            <p class="mt-3">The web portal uses essential session cookies only — for authentication and CSRF protection. We do not use advertising, analytics, or tracking cookies.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">9. Data Retention</h2>
            <p class="mt-3">We retain your data for as long as your account is active. Upon account deletion or subscription cancellation (after a 30-day grace period), all company data is permanently deleted from our servers.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">10. Changes to This Policy</h2>
            <p class="mt-3">We may update this Privacy Policy from time to time. We will notify you by email before significant changes take effect. Your continued use of the platform after the effective date constitutes acceptance.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900">11. Contact</h2>
            <p class="mt-3">For privacy concerns, data requests, or questions about this policy, contact us at:</p>
            <p class="mt-2 font-medium text-slate-800">
                <a href="mailto:shobha.solanki107@gmail.com" class="text-teal-600 hover:underline">shobha.solanki107@gmail.com</a>
            </p>
        </section>

    </div>

    <div class="mt-10 flex gap-4 text-sm">
        <a href="{{ route('terms') }}" class="text-teal-600 hover:underline">Terms of Service →</a>
        <a href="{{ route('contact') }}" class="text-teal-600 hover:underline">Contact Us →</a>
    </div>

</div>
@endsection
