@php
    $groups = [
        [
            'label' => 'Today',
            'items' => [
                ['route' => 'mr.dashboard', 'label' => 'Dashboard', 'match' => 'mr.dashboard'],
            ],
        ],
        [
            'label' => 'Field work',
            'items' => [
                ['route' => 'mr.visits.create', 'label' => 'New visit', 'match' => 'mr.visits.create'],
                ['route' => 'mr.visits.index', 'label' => 'My visits', 'match' => 'mr.visits.index|mr.visits.show'],
                ['route' => 'mr.routes.index', 'label' => 'Routes', 'match' => 'mr.routes.*'],
                ['route' => 'mr.customers.index', 'label' => 'Customers', 'match' => 'mr.customers.*'],
            ],
        ],
        [
            'label' => 'Sales',
            'items' => [
                ['route' => 'mr.orders.index', 'label' => 'Orders', 'match' => 'mr.orders.*'],
                ['route' => 'mr.targets.index', 'label' => 'Targets', 'match' => 'mr.targets.*'],
            ],
        ],
        [
            'label' => 'HR',
            'items' => [
                ['route' => 'mr.expenses.index', 'label' => 'Expenses', 'match' => 'mr.expenses.*'],
                ['route' => 'mr.leaves.index', 'label' => 'Leave', 'match' => 'mr.leaves.*'],
            ],
        ],
        [
            'label' => 'Insights',
            'items' => [
                ['route' => 'mr.ai-reports.index', 'label' => 'AI reports', 'match' => 'mr.ai-reports.*'],
                ['route' => 'mr.notifications.index', 'label' => 'Alerts', 'match' => 'mr.notifications.*'],
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

<nav class="mb-6 rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-start">
        @foreach ($groups as $group)
            <div class="min-w-0 flex-1 sm:min-w-[8rem] sm:max-w-[12rem]">
                <p class="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $group['label'] }}</p>
                <div class="flex flex-wrap gap-1">
                    @foreach ($group['items'] as $item)
                        <a href="{{ route($item['route']) }}"
                            class="rounded-lg px-2.5 py-1.5 text-xs font-medium whitespace-nowrap
                                {{ $isActive($item['match']) ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</nav>
