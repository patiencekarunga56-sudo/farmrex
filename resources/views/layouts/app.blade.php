<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
    <title>@yield('title', 'Home') · {{ config('app.name') }}</title>
</head>
<body>
@php
    $user = auth()->user();
    $nav = [
        'Overview' => [
            ['Home', 'dashboard', 'grid', 'dashboard'],
            ['Calendar', 'calendar', 'calendar', 'calendar'],
        ],
        'Production' => [
            ['Plantings', 'plantings.index', 'sprout', 'plantings.*'],
            ['Activities', 'activities.index', 'activity', 'activities.*'],
            ['Observations', 'observations.index', 'eye', 'observations.*'],
            ['Harvests', 'harvests.index', 'basket', 'harvests.*'],
        ],
        'Resources' => [
            ['Farm Inputs', 'inputs.index', 'box', 'inputs.*'],
            ['Labour', 'labour.index', 'users', 'labour.*'],
            ['Fields & Plots', 'farms.index', 'map', 'farms.*'],
            ['Crops', 'crops.index', 'leaf', 'crops.*'],
        ],
        'Finance' => [
            ['Income', 'incomes.index', 'income', 'incomes.*'],
            ['Expenses', 'expenses.index', 'expense', 'expenses.*'],
        ],
        'Planning' => [
            ['Reminders', 'reminders.index', 'bell', 'reminders.*'],
        ],
    ];
    if ($user->isAdmin()) {
        $nav['Overview'][] = ['Reports', 'reports', 'reports', 'reports*'];
        $nav['Administration'] = [['Farmers & Users', 'users.index', 'shield', 'users.*']];
    }
@endphp
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="brand"><img src="{{ asset('images/logo.png') }}" alt="FarmRex"></a>
        @foreach ($nav as $group => $links)
            <div class="nav-group">
                <div class="nav-title">{{ $group }}</div>
                @foreach ($links as [$label, $route, $icon, $match])
                    <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($match) ? 'active' : '' }}">
                        <x-icon :name="$icon" :size="19" /> {{ $label }}
                    </a>
                @endforeach
            </div>
        @endforeach
        <div class="sidebar-foot">FarmRex · Farm Records Management<br>Butiiti, Kyenjojo</div>
    </aside>
    <div class="backdrop" data-nav-toggle></div>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn menu-btn" data-nav-toggle aria-label="Open menu"><x-icon name="menu" /></button>
            <h1>@yield('title', 'Home')</h1>
            <div class="org-chip"><x-icon name="globe" :size="16" /> Butiiti Youth Farmers <x-icon name="chevron-down" :size="14" /></div>
            <span class="top-divider"></span>
            <x-theme-toggle />
            <a href="{{ route('reminders.index') }}" class="icon-btn" title="Reminders" aria-label="Reminders">
                <x-icon name="bell" :size="19" />
                @if ($dueCount > 0)<span class="dot">{{ $dueCount }}</span>@endif
            </a>
            <div class="user-menu">
                <button class="avatar" aria-label="Account menu">{{ $user->initials }}</button>
                <div class="menu">
                    <div class="who"><strong>{{ $user->name }}</strong><span>{{ $user->role_label }}</span></div>
                    <a href="{{ route('profile') }}"><x-icon name="settings" :size="17" /> My profile</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit"><x-icon name="logout" :size="17" /> Sign out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="content">
            @if (session('success'))<div class="flash flash-success"><x-icon name="check" :size="18" /> {{ session('success') }}</div>@endif
            @if (session('error'))<div class="flash flash-error"><x-icon name="alert" :size="18" /> {{ session('error') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('js/app.js') }}?v=1"></script>
</body>
</html>
