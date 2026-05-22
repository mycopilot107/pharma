@php
    $items = [
        ['route' => 'super-admin.dashboard', 'label' => 'Overview', 'match' => 'super-admin.dashboard'],
        ['route' => 'super-admin.companies.index', 'label' => 'Companies', 'match' => 'super-admin.companies.*'],
        ['route' => 'super-admin.settings.edit', 'label' => 'Platform settings', 'match' => 'super-admin.settings.*'],
    ];

    $isActive = function (string $pattern): bool {
        return request()->routeIs($pattern);
    };
@endphp

<nav class="border-b border-violet-200 bg-violet-50">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-1 px-4 py-2 text-sm sm:px-6">
        <span class="mr-2 rounded-lg bg-violet-700 px-2 py-1 text-xs font-semibold uppercase tracking-wide text-white">Super Admin</span>
        @foreach ($items as $item)
            <a href="{{ route($item['route']) }}"
                class="rounded-lg px-3 py-2 font-medium whitespace-nowrap
                    {{ $isActive($item['match']) ? 'bg-violet-700 text-white' : 'text-violet-900 hover:bg-white hover:shadow-sm' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
