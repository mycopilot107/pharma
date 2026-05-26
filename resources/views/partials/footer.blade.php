<footer class="mt-auto border-t border-slate-200 bg-white">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Brand --}}
            <div>
                <p class="text-base font-bold text-teal-700">{{ config('pharma.app_name') }}</p>
                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                    GPS-powered field visit tracking for pharma medical representatives, sales teams & distributors.
                </p>
                <p class="mt-4 text-xs text-slate-400">&copy; {{ date('Y') }} {{ config('pharma.app_name') }}. All rights reserved.</p>
            </div>

            {{-- Product --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Product</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="text-slate-600 hover:text-teal-600 transition-colors">Home</a></li>
                    <li><a href="{{ route('companies.register') }}" class="text-slate-600 hover:text-teal-600 transition-colors">Register Company</a></li>
                    <li><a href="{{ route('login') }}" class="text-slate-600 hover:text-teal-600 transition-colors">Login</a></li>
                    <li><a href="{{ route('faq') }}" class="text-slate-600 hover:text-teal-600 transition-colors">FAQ</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Company</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('about') }}" class="text-slate-600 hover:text-teal-600 transition-colors">About Us</a></li>
                    <li><a href="{{ route('contact') }}" class="text-slate-600 hover:text-teal-600 transition-colors">Contact</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Legal</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('terms') }}" class="text-slate-600 hover:text-teal-600 transition-colors">Terms of Service</a></li>
                    <li><a href="{{ route('policy') }}" class="text-slate-600 hover:text-teal-600 transition-colors">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-6 text-xs text-slate-400">
            <p>Built for India's pharma field force.</p>
            <div class="flex gap-4">
                <a href="{{ route('terms') }}" class="hover:text-teal-600 transition-colors">Terms</a>
                <a href="{{ route('policy') }}" class="hover:text-teal-600 transition-colors">Privacy</a>
                <a href="{{ route('contact') }}" class="hover:text-teal-600 transition-colors">Contact</a>
            </div>
        </div>
    </div>
</footer>
