@extends('layouts.app')
@section('title', 'Home')

@section('content')
@php
    $first = explode(' ', trim(auth()->user()->name))[0];
    $periodSelect = function () use ($period, $periods) {
        $html = '<form method="GET" action="'.e(route('dashboard')).'"><select class="select-pill" name="period" data-autosubmit aria-label="Period">';
        foreach ($periods as $key => $label) {
            $html .= '<option value="'.e($key).'"'.($key === $period ? ' selected' : '').'>'.e($label).'</option>';
        }
        return $html.'</select></form>';
    };
@endphp

<section class="banner">
    <div class="date">{{ now()->format('D jS M, Y') }}</div>
    <h2>Hello, {{ $first }}! 👋</h2>
    <div class="actions">
        <a href="{{ route('incomes.create') }}" class="btn btn-white"><x-icon name="income" class="down" :size="17" /> Add income</a>
        <a href="{{ route('expenses.create') }}" class="btn btn-white"><x-icon name="expense" class="up" :size="17" /> Add expense</a>
        <a href="{{ route('plantings.create') }}" class="btn btn-white"><x-icon name="sprout" :size="17" /> New planting</a>
    </div>
</section>

<div class="stats" style="margin-bottom:20px">
    <div class="card stat">
        <span class="ico ico-green"><x-icon name="income" /></span>
        <div><div class="label">Income · {{ $periods[$period] }}</div><div class="value">{{ ugx($totalIncome) }}</div></div>
    </div>
    <div class="card stat">
        <span class="ico ico-red"><x-icon name="expense" /></span>
        <div><div class="label">Expenses · {{ $periods[$period] }}</div><div class="value">{{ ugx($totalExpense) }}</div></div>
    </div>
    <div class="card stat">
        <span class="ico ico-blue"><x-icon name="wallet" /></span>
        <div><div class="label">Net balance</div><div class="value {{ $net >= 0 ? 'pos' : 'neg' }}">{{ $net < 0 ? '−' : '' }}{{ ugx(abs($net)) }}</div></div>
    </div>
    <div class="card stat">
        <span class="ico ico-amber"><x-icon name="sprout" /></span>
        <div><div class="label">Active plantings</div><div class="value">{{ $activePlantings->count() }}</div></div>
    </div>
</div>

@if ($weather)
<section class="card weather" style="margin-bottom:20px">
    <div class="card-head">
        <h2>Weather · {{ $weather['location'] }}</h2>
        <span class="muted" style="font-size:.8rem">Updated {{ $weather['updated']->format('H:i') }}</span>
    </div>
    <div class="weather-body">
        <div class="weather-now">
            <div class="wx-icon" aria-hidden="true">{{ $weather['icon'] }}</div>
            <div>
                <div class="wx-temp">{{ $weather['temp'] }}°C</div>
                <div class="wx-cond">{{ $weather['condition'] }}</div>
                <div class="wx-meta">Feels like {{ $weather['feels_like'] }}° · Humidity {{ $weather['humidity'] }}% · Wind {{ $weather['wind'] }} km/h</div>
            </div>
        </div>
        <ul class="weather-days">
            @foreach ($weather['days'] as $d)
                <li title="{{ $d['summary'] }} · {{ $d['rain_mm'] }} mm expected">
                    <b>{{ $d['label'] }}</b>
                    <span class="wx-ico" aria-hidden="true">{{ $d['icon'] }}</span>
                    <span class="wx-range">{{ $d['max'] }}° <small>{{ $d['min'] }}°</small></span>
                    <span class="wx-rain">💧 {{ $d['rain_chance'] }}%</span>
                </li>
            @endforeach
        </ul>
    </div>
    <ul class="weather-tips">
        @foreach ($weather['tips'] as [$text, $level])
            <li class="tip-{{ $level }}">{{ $text }}</li>
        @endforeach
    </ul>
</section>
@endif

<div class="grid dash-grid">
    <div class="stack">
        <section class="card">
            <div class="card-head"><h2>Income Summary</h2>{!! $periodSelect() !!}</div>
            <x-donut :data="$income" label="Total Income" />
        </section>

        <section class="card">
            <div class="card-head"><h2>Expense Summary</h2>{!! $periodSelect() !!}</div>
            <x-donut :data="$expense" label="Total Expenses" />
        </section>

        <section class="card">
            <div class="card-head">
                <h2>Income vs expenses · last 6 months</h2>
                <div class="chart-legend"><span><i style="background:#74d24a"></i>Income</span><span><i style="background:#fa3df3"></i>Expenses</span></div>
            </div>
            <div class="bars">
                @foreach ($monthly as $m)
                    <div class="bar-col">
                        <div class="bar-pair" title="{{ $m['label'] }}: income {{ ugx($m['income']) }}, expenses {{ ugx($m['expense']) }}">
                            <i class="inc" style="height: {{ round($m['income'] / $chartMax * 100) }}%"></i>
                            <i class="exp" style="height: {{ round($m['expense'] / $chartMax * 100) }}%"></i>
                        </div>
                        <small>{{ $m['label'] }}</small>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="stack">
        <section class="card">
            <div class="card-head"><h2>Recent Entries</h2><a href="{{ route('reports') }}" class="btn btn-link btn-sm">View all</a></div>
            <ul class="entries">
                @forelse ($recent as $e)
                    <li>
                        <a class="entry" href="{{ $e['url'] }}">
                            <div class="when"><small>{{ $e['date']->format('M') }}</small><b>{{ $e['date']->format('d') }}</b></div>
                            <div class="what">
                                <div class="cat {{ $e['kind'] }}">{{ $e['category'] }}</div>
                                <div class="desc">{{ $e['description'] }}</div>
                            </div>
                            <div class="amt">{{ ugx($e['amount']) }}</div>
                        </a>
                    </li>
                @empty
                    <li class="empty">No entries yet. Add your first income or expense.</li>
                @endforelse
            </ul>
        </section>

        <section class="card">
            <div class="card-head">
                <h2>Upcoming reminders</h2>
                @if ($overdue > 0)<span class="badge badge-red">{{ $overdue }} overdue</span>@endif
            </div>
            <ul class="rowlist">
                @forelse ($reminders as $r)
                    @php $late = $r->due_date->isPast() && ! $r->due_date->isToday(); @endphp
                    <li>
                        <span class="ico {{ $late ? 'ico-red' : 'ico-amber' }}" style="width:38px;height:38px;border-radius:10px;display:grid;place-items:center;flex:none"><x-icon name="bell" :size="17" /></span>
                        <div class="grow">
                            <div class="t">{{ $r->title }}</div>
                            <div class="s">{{ $late ? 'Overdue · ' : '' }}{{ $r->due_date->format('D, d M Y') }}</div>
                        </div>
                        <form method="POST" action="{{ route('reminders.complete', $r->id) }}">@csrf @method('PATCH')
                            <button class="icon-btn" title="Mark done"><x-icon name="check" :size="18" /></button>
                        </form>
                    </li>
                @empty
                    <li class="empty">You are all caught up. 🎉</li>
                @endforelse
            </ul>
        </section>

        <section class="card">
            <div class="card-head"><h2>Active plantings</h2><a href="{{ route('plantings.index') }}" class="btn btn-link btn-sm">View all</a></div>
            <ul class="rowlist">
                @forelse ($activePlantings->take(5) as $p)
                    <li>
                        <div class="grow">
                            <div class="t">{{ $p->crop->name }} <span class="muted" style="font-weight:400">· {{ $p->farm->name }}</span></div>
                            <div class="s">{{ $p->growth_stage }}@if ($p->expected_harvest_date) · harvest {{ $p->expected_harvest_date->format('d M') }}@endif</div>
                            <div class="progress"><span style="width: {{ max(4, $p->progress) }}%"></span></div>
                        </div>
                    </li>
                @empty
                    <li class="empty">No active plantings.</li>
                @endforelse
            </ul>
        </section>

        <section class="card">
            <div class="card-head"><h2>Latest observations</h2><a href="{{ route('observations.index') }}" class="btn btn-link btn-sm">View all</a></div>
            <ul class="rowlist">
                @forelse ($recentObservations as $o)
                    <li>
                        <div class="grow">
                            <div class="t">{{ $o->planting->crop->name ?? 'Crop' }} · {{ $o->category }}</div>
                            <div class="s">{{ $o->observed_on->format('d M') }} — {{ \Illuminate\Support\Str::limit($o->description, 60) }}</div>
                        </div>
                        @if ($o->severity)<span class="badge {{ badge_class($o->severity) }}">{{ $o->severity }}</span>@endif
                    </li>
                @empty
                    <li class="empty">No observations recorded.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection
