@php
    $groups = [
        [
            'label' => null,
            'items' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'match' => 'dashboard'],
            ],
        ],
        [
            'label' => 'Team',
            'items' => [
                ['route' => 'users.index', 'label' => 'All MRs', 'match' => 'users.index|users.show|users.edit'],
                ['route' => 'users.create', 'label' => 'Add MR', 'match' => 'users.create'],
            ],
        ],
        [
            'label' => 'CRM',
            'items' => [
                ['route' => 'admin.customers.index', 'label' => 'Customers', 'match' => 'admin.customers.*'],
                ['route' => 'admin.visits.index', 'label' => 'Visits', 'match' => 'admin.visits.*'],
                ['route' => 'admin.tracking.index', 'label' => 'Live Tracking', 'match' => 'admin.tracking.*', 'highlight' => true],
                ['route' => 'admin.targets.index', 'label' => 'Targets', 'match' => 'admin.targets.*'],
            ],
        ],
        [
            'label' => 'Sales',
            'items' => [
                ['route' => 'admin.products.index', 'label' => 'Products', 'match' => 'admin.products.*'],
                ['route' => 'admin.orders.index', 'label' => 'Orders', 'match' => 'admin.orders.index|admin.orders.show'],
            ],
        ],
        [
            'label' => 'HR',
            'items' => [
                ['route' => 'admin.expenses.index', 'label' => 'Expenses', 'match' => 'admin.expenses.*'],
                ['route' => 'admin.leaves.index', 'label' => 'Leave', 'match' => 'admin.leaves.index|admin.leaves.show'],
            ],
        ],
        [
            'label' => 'Reports',
            'items' => [
                ['route' => 'admin.visits.index', 'label' => 'Visit Reports', 'match' => 'admin.visits.*'],
                ['route' => 'admin.orders.reports', 'label' => 'Sales Reports', 'match' => 'admin.orders.reports'],
                ['route' => 'admin.leaves.reports', 'label' => 'Leave Reports', 'match' => 'admin.leaves.reports'],
                ['route' => 'admin.ai-reports.index', 'label' => 'AI Reports', 'match' => 'admin.ai-reports.*'],
            ],
        ],
    ];

    $isActive = function (string $pattern): bool {
        foreach (explode('|', $pattern) as $p) {
            if (request()->routeIs(trim($p))) {
                return true;
            }
        }
        return false;
    };

    $groupActive = function (array $group) use ($isActive): bool {
        foreach ($group['items'] as $item) {
            if ($isActive($item['match'])) {
                return true;
            }
        }
        return false;
    };
@endphp

<nav class="border-b border-slate-200 bg-slate-50" id="admin-nav">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex flex-wrap items-center gap-1 py-2 text-sm">
            @foreach ($groups as $group)
                @if ($group['label'])
                    <div class="relative flex-shrink-0" data-admin-nav-dropdown>
                        <button type="button"
                            data-admin-nav-trigger
                            aria-expanded="false"
                            aria-haspopup="true"
                            class="flex items-center gap-1 rounded-lg px-3 py-2 font-medium whitespace-nowrap transition
                                {{ $groupActive($group) ? 'bg-teal-600 text-white' : 'text-slate-700 hover:bg-white hover:shadow-sm' }}">
                            {{ $group['label'] }}
                            <svg class="h-4 w-4 opacity-70 transition-transform" data-admin-nav-chevron fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        {{-- pt-1 bridge keeps hover path open; menu uses hidden until opened --}}
                        <div class="absolute left-0 top-full z-[100] hidden min-w-[12rem] pt-1" data-admin-nav-menu role="menu">
                            <div class="rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                                @foreach ($group['items'] as $item)
                                    <a href="{{ route($item['route']) }}" role="menuitem"
                                        class="block px-4 py-2.5 text-slate-700 hover:bg-teal-50 hover:text-teal-800
                                            {{ $isActive($item['match']) ? 'bg-teal-50 font-semibold text-teal-800' : '' }}
                                            {{ !empty($item['highlight']) ? 'text-emerald-700' : '' }}">
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    @foreach ($group['items'] as $item)
                        <a href="{{ route($item['route']) }}"
                            class="flex-shrink-0 rounded-lg px-3 py-2 font-medium whitespace-nowrap
                                {{ $isActive($item['match']) ? 'bg-teal-600 text-white' : 'text-slate-700 hover:bg-white hover:shadow-sm' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <span class="mx-1 hidden h-6 w-px bg-slate-300 sm:inline" aria-hidden="true"></span>
                @endif
            @endforeach

            <a href="{{ route('admin.notifications.index') }}"
                class="ml-auto flex-shrink-0 rounded-lg px-3 py-2 font-medium whitespace-nowrap
                    {{ request()->routeIs('admin.notifications.*') ? 'bg-amber-100 text-amber-900' : 'text-slate-700 hover:bg-white hover:shadow-sm' }}">
                Alerts
            </a>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nav = document.getElementById('admin-nav');
    if (!nav) return;

    const dropdowns = nav.querySelectorAll('[data-admin-nav-dropdown]');

    function closeAll() {
        dropdowns.forEach(function (dd) {
            const menu = dd.querySelector('[data-admin-nav-menu]');
            const trigger = dd.querySelector('[data-admin-nav-trigger]');
            const chevron = dd.querySelector('[data-admin-nav-chevron]');
            if (menu) menu.classList.add('hidden');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
            if (chevron) chevron.classList.remove('rotate-180');
        });
    }

    dropdowns.forEach(function (dd) {
        const trigger = dd.querySelector('[data-admin-nav-trigger]');
        const menu = dd.querySelector('[data-admin-nav-menu]');
        const chevron = dd.querySelector('[data-admin-nav-chevron]');
        if (!trigger || !menu) return;

        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const isOpen = !menu.classList.contains('hidden');
            closeAll();
            if (!isOpen) {
                menu.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
                if (chevron) chevron.classList.add('rotate-180');
            }
        });
    });

    document.addEventListener('click', function (e) {
        if (!nav.contains(e.target)) {
            closeAll();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll();
    });
});
</script>
