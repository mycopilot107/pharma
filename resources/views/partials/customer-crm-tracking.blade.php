@php
    $crmPrefix = $crmRoutePrefix ?? 'mr';
@endphp

<div class="mt-8 space-y-8">
    {{-- Follow-ups --}}
    <section class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 sm:p-5">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-amber-900">📅 Follow-ups</h2>
            <span class="text-xs text-amber-700">{{ $followUps->where('status', \App\Enums\FollowUpStatus::Pending)->count() }} pending</span>
        </div>
        @if ($editable ?? true)
        <details class="mt-3">
            <summary class="cursor-pointer text-sm font-medium text-amber-800">+ Schedule follow-up</summary>
            <form method="POST" action="{{ route($crmPrefix.'.customers.follow-ups.store', $customer) }}" class="mt-3 space-y-2 rounded-xl bg-white p-3">
                @csrf
                <input type="text" name="title" placeholder="Title *" required class="w-full rounded-lg border px-3 py-2 text-sm">
                <input type="datetime-local" name="due_at" required class="w-full rounded-lg border px-3 py-2 text-sm">
                <textarea name="notes" rows="2" placeholder="Notes" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
                <button type="submit" class="w-full rounded-lg bg-amber-600 py-2 text-sm font-medium text-white">Save follow-up</button>
            </form>
        </details>
        @endif
        <div class="mt-3 space-y-2">
            @forelse ($followUps as $followUp)
                <div class="flex items-start justify-between gap-2 rounded-xl border bg-white p-3 {{ $followUp->isOverdue() ? 'border-red-300' : '' }}">
                    <div>
                        <p class="font-medium text-sm">{{ $followUp->title }}</p>
                        <p class="text-xs text-slate-500">Due {{ $followUp->due_at->format('d M Y, h:i A') }} · {{ $followUp->user->name }}</p>
                        @if ($followUp->notes)<p class="text-xs text-slate-600 mt-1">{{ $followUp->notes }}</p>@endif
                    </div>
                    <div class="text-right shrink-0">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $followUp->status->color() }}">{{ $followUp->status->label() }}</span>
                        @if ($followUp->status === \App\Enums\FollowUpStatus::Pending && ($editable ?? true))
                            <form method="POST" action="{{ route($crmPrefix.'.follow-ups.complete', $followUp) }}" class="mt-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs text-teal-700 hover:underline">Complete</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 py-2">No follow-ups scheduled.</p>
            @endforelse
        </div>
    </section>

    {{-- Prescriptions --}}
    <section class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4 sm:p-5">
        <h2 class="font-semibold text-blue-900">💊 Prescriptions</h2>
        @if ($editable ?? true)
        <details class="mt-3">
            <summary class="cursor-pointer text-sm font-medium text-blue-800">+ Log prescription</summary>
            <form method="POST" action="{{ route($crmPrefix.'.customers.prescriptions.store', $customer) }}" class="mt-3 space-y-2 rounded-xl bg-white p-3">
                @csrf
                <input type="text" name="product_name" placeholder="Product name *" required class="w-full rounded-lg border px-3 py-2 text-sm">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="brand" placeholder="Brand" class="rounded-lg border px-3 py-2 text-sm">
                    <input type="text" name="strength" placeholder="Strength" class="rounded-lg border px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" step="0.01" name="quantity" value="1" required class="rounded-lg border px-3 py-2 text-sm">
                    <input type="text" name="unit" placeholder="Unit (strips, bottles)" class="rounded-lg border px-3 py-2 text-sm">
                </div>
                <input type="date" name="prescribed_at" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                <textarea name="notes" rows="2" placeholder="Notes" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
                <button type="submit" class="w-full rounded-lg bg-blue-600 py-2 text-sm font-medium text-white">Save prescription</button>
            </form>
        </details>
        @endif
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500">
                        <th class="pb-2 pr-3">Product</th>
                        <th class="pb-2 pr-3">Qty</th>
                        <th class="pb-2 pr-3">Date</th>
                        <th class="pb-2">MR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($prescriptions as $rx)
                        <tr>
                            <td class="py-2 pr-3 font-medium">{{ $rx->product_name }}@if($rx->brand)<span class="text-slate-500 font-normal"> · {{ $rx->brand }}</span>@endif</td>
                            <td class="py-2 pr-3">{{ $rx->formattedQuantity() }}</td>
                            <td class="py-2 pr-3">{{ $rx->prescribed_at->format('d M Y') }}</td>
                            <td class="py-2 text-slate-500">{{ $rx->user->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-3 text-slate-500">No prescriptions logged.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Purchase patterns --}}
    <section class="rounded-2xl border border-green-200 bg-green-50/50 p-4 sm:p-5">
        <h2 class="font-semibold text-green-900">📊 Purchase patterns</h2>
        @if (($purchasePatterns['total_records'] ?? 0) > 0)
            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                <div class="rounded-lg bg-white p-3 text-center">
                    <p class="text-xs text-slate-500">Total orders</p>
                    <p class="text-lg font-bold">{{ $purchasePatterns['total_records'] }}</p>
                </div>
                <div class="rounded-lg bg-white p-3 text-center">
                    <p class="text-xs text-slate-500">Total value</p>
                    <p class="text-lg font-bold">{{ format_money($purchasePatterns['total_value']) }}</p>
                </div>
                <div class="rounded-lg bg-white p-3 text-center">
                    <p class="text-xs text-slate-500">Top product</p>
                    <p class="text-sm font-bold truncate">{{ $purchasePatterns['top_products'][0]['product'] ?? '—' }}</p>
                </div>
            </div>
            @if (!empty($purchasePatterns['top_products']))
                <div class="mt-3 rounded-xl bg-white p-3">
                    <p class="text-xs font-medium text-slate-600 mb-2">Product insights</p>
                    @foreach ($purchasePatterns['top_products'] as $insight)
                        <p class="text-sm py-1 border-b border-slate-50 last:border-0">
                            <span class="font-medium">{{ $insight['product'] }}</span>
                            — {{ $insight['count'] }} orders, qty {{ rtrim(rtrim(number_format($insight['total_qty'], 2), '0'), '.') }}
                            @if ($insight['frequency'])
                                · {{ \App\Enums\PurchaseFrequency::tryFrom($insight['frequency'])?->label() ?? $insight['frequency'] }}
                            @endif
                        </p>
                    @endforeach
                </div>
            @endif
        @endif
        @if ($editable ?? true)
        <details class="mt-3">
            <summary class="cursor-pointer text-sm font-medium text-green-800">+ Record purchase</summary>
            <form method="POST" action="{{ route($crmPrefix.'.customers.purchases.store', $customer) }}" class="mt-3 space-y-2 rounded-xl bg-white p-3">
                @csrf
                <input type="text" name="product_name" placeholder="Product name *" required class="w-full rounded-lg border px-3 py-2 text-sm">
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" step="0.01" name="quantity" value="1" required class="rounded-lg border px-3 py-2 text-sm">
                    <input type="text" name="unit" placeholder="Unit" class="rounded-lg border px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" step="0.01" name="amount" placeholder="Amount ($)" class="rounded-lg border px-3 py-2 text-sm">
                    <select name="purchase_frequency" class="rounded-lg border px-3 py-2 text-sm">
                        <option value="">Frequency</option>
                        @foreach (\App\Enums\PurchaseFrequency::cases() as $freq)
                            <option value="{{ $freq->value }}">{{ $freq->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="date" name="purchased_at" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                <textarea name="notes" rows="2" placeholder="Notes" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
                <button type="submit" class="w-full rounded-lg bg-green-600 py-2 text-sm font-medium text-white">Save purchase</button>
            </form>
        </details>
        @endif
        <div class="mt-3 space-y-2">
            @forelse ($purchases->take(10) as $purchase)
                <div class="rounded-xl border bg-white p-3 text-sm">
                    <p class="font-medium">{{ $purchase->product_name }} · {{ rtrim(rtrim(number_format($purchase->quantity, 2), '0'), '.') }} {{ $purchase->unit }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $purchase->purchased_at->format('d M Y') }}
                        @if ($purchase->amount) · {{ format_money($purchase->amount) }} @endif
                        @if ($purchase->purchase_frequency) · {{ $purchase->purchase_frequency->label() }} @endif
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-500 py-2">No purchase history yet.</p>
            @endforelse
        </div>
    </section>

    {{-- Meeting history --}}
    <section class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 sm:p-5">
        <h2 class="font-semibold text-slate-900">🤝 Meeting history</h2>
        <p class="text-xs text-slate-500 mt-1">GPS visits and field meetings with this customer</p>
        <div class="mt-3 space-y-2">
            @forelse ($meetings as $meeting)
                @php
                    $meetingUrl = $crmPrefix === 'admin'
                        ? route('admin.visits.show', $meeting)
                        : route('mr.visits.show', $meeting);
                @endphp
                <a href="{{ $meetingUrl }}" class="block rounded-xl border bg-white p-3 hover:border-teal-300">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-sm">{{ $meeting->visit_type->icon() }} {{ $meeting->place_name }}</p>
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $meeting->status->color() }}">{{ $meeting->status->label() }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $meeting->checked_in_at?->format('d M Y, h:i A') ?? $meeting->created_at->format('d M Y') }}
                        · {{ $meeting->user->name }}
                        @if ($meeting->formattedDuration()) · {{ $meeting->formattedDuration() }} @endif
                    </p>
                    @if ($meeting->notes)
                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $meeting->notes }}</p>
                    @endif
                </a>
            @empty
                <p class="text-sm text-slate-500 py-2">No meetings recorded yet.</p>
            @endforelse
        </div>
    </section>
</div>
