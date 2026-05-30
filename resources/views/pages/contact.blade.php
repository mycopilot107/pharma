@extends('layouts.app')

@section('title', 'Contact Us — ' . config('pharma.app_name'))

@section('content')
<div class="mx-auto max-w-3xl">

    <div class="text-center">
        <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-700">Contact</span>
        <h1 class="mt-4 text-3xl font-bold text-slate-900 sm:text-4xl">Get in Touch</h1>
        <p class="mt-3 text-slate-600">Have a question, need help, or want to discuss your team's requirements? We're here.</p>
    </div>

    <div class="mt-12 grid gap-8 sm:grid-cols-2">

        {{-- Contact Info --}}
        <div class="space-y-6">

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600 text-xl">✉️</div>
                    <div>
                        <p class="font-semibold text-slate-800">Email Support</p>
                        <p class="mt-1 text-sm text-slate-500">For general enquiries, billing, or technical help</p>
                        <a href="mailto:support@webauditaitool.com" class="mt-2 inline-block text-sm font-medium text-teal-600 hover:underline">
                            support@webauditaitool.com
                        </a>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600 text-xl">🕐</div>
                    <div>
                        <p class="font-semibold text-slate-800">Response Time</p>
                        <p class="mt-1 text-sm text-slate-500">We typically respond within <strong>24 hours</strong> on business days (Mon–Sat, 9am–6pm IST).</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600 text-xl">📋</div>
                    <div>
                        <p class="font-semibold text-slate-800">Before You Write</p>
                        <p class="mt-1 text-sm text-slate-500">Check our <a href="{{ route('faq') }}" class="text-teal-600 hover:underline">FAQ</a> — many common questions are already answered there.</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Quick Contact Form (mailto) --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-slate-800">Send Us a Message</h2>
            <p class="mt-1 text-sm text-slate-500">Fill in the details below and click send to open your email client.</p>

            <form id="contact-form" class="mt-5 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1" for="cf-name">Your Name</label>
                    <input id="cf-name" type="text" placeholder="Ravi Sharma"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1" for="cf-company">Company Name</label>
                    <input id="cf-company" type="text" placeholder="ABC Pharma Ltd."
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1" for="cf-subject">Subject</label>
                    <select id="cf-subject"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        <option value="General Enquiry">General Enquiry</option>
                        <option value="Technical Support">Technical Support</option>
                        <option value="Billing & Subscription">Billing & Subscription</option>
                        <option value="Mobile App Issue">Mobile App Issue</option>
                        <option value="Feature Request">Feature Request</option>
                        <option value="Demo Request">Demo Request</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1" for="cf-message">Message</label>
                    <textarea id="cf-message" rows="4" placeholder="Describe your question or issue..."
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100 resize-none"></textarea>
                </div>
                <button type="button" onclick="sendMail()"
                    class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 transition-colors">
                    Open in Email Client →
                </button>
            </form>
        </div>

    </div>

    {{-- FAQ Shortcut --}}
    <div class="mt-10 grid gap-4 sm:grid-cols-3 text-center text-sm">
        <a href="{{ route('faq') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-5 shadow-sm hover:border-teal-300 hover:shadow-md transition">
            <p class="text-2xl">❓</p>
            <p class="mt-2 font-medium text-slate-800">FAQ</p>
            <p class="mt-1 text-slate-500 text-xs">Common questions answered</p>
        </a>
        <a href="{{ route('terms') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-5 shadow-sm hover:border-teal-300 hover:shadow-md transition">
            <p class="text-2xl">📄</p>
            <p class="mt-2 font-medium text-slate-800">Terms of Service</p>
            <p class="mt-1 text-slate-500 text-xs">Our usage guidelines</p>
        </a>
        <a href="{{ route('policy') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-5 shadow-sm hover:border-teal-300 hover:shadow-md transition">
            <p class="text-2xl">🔒</p>
            <p class="mt-2 font-medium text-slate-800">Privacy Policy</p>
            <p class="mt-1 text-slate-500 text-xs">How we protect your data</p>
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
function sendMail() {
    const name    = document.getElementById('cf-name').value.trim();
    const company = document.getElementById('cf-company').value.trim();
    const subject = document.getElementById('cf-subject').value;
    const message = document.getElementById('cf-message').value.trim();

    if (!name || !message) {
        alert('Please fill in your name and message before sending.');
        return;
    }

    const body = [
        'Name: ' + name,
        company ? 'Company: ' + company : '',
        '',
        message,
    ].filter(Boolean).join('\n');

    const mailto = 'mailto:support@webauditaitool.com'
        + '?subject=' + encodeURIComponent('[{{ config("pharma.app_name") }}] ' + subject)
        + '&body='    + encodeURIComponent(body);

    window.location.href = mailto;
}
</script>
@endpush
