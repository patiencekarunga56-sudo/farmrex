@extends('layouts.app')
@section('title', 'Calendar')

@section('content')
<div class="page-head">
    <div>
        <h2 style="font-size:1.25rem">{{ $month->format('F Y') }}</h2>
        <p class="muted">Reminders, activities, plantings and harvests at a glance.</p>
    </div>
    <div class="cal-nav">
        <a class="btn btn-ghost btn-sm" href="{{ route('calendar', ['month' => $prev]) }}"><x-icon name="chevron-left" :size="16" /> Prev</a>
        <a class="btn btn-ghost btn-sm" href="{{ route('calendar') }}">Today</a>
        <a class="btn btn-ghost btn-sm" href="{{ route('calendar', ['month' => $next]) }}">Next <x-icon name="chevron-right" :size="16" /></a>
        <a class="btn btn-primary btn-sm" href="{{ route('reminders.create') }}"><x-icon name="plus" :size="16" /> Reminder</a>
    </div>
</div>

<div class="card" style="padding:14px">
    <div class="table-wrap">
        <table class="cal">
            <thead><tr>@foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d)<th>{{ $d }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach ($weeks as $week)
                    <tr>
                        @foreach ($week as $day)
                            <td class="{{ $day['inMonth'] ? '' : 'off' }} {{ $day['isToday'] ? 'today' : '' }}">
                                <div class="dn">{{ $day['date']->format('j') }}</div>
                                @foreach (array_slice($day['events'], 0, 3) as $ev)
                                    <a class="ev {{ $ev['type'] }}" href="{{ $ev['url'] }}" title="{{ $ev['label'] }}">{{ $ev['label'] }}</a>
                                @endforeach
                                @if (count($day['events']) > 3)<small class="muted">+{{ count($day['events']) - 3 }} more</small>@endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="cal-key">
        <span><i style="background:var(--warn-soft)"></i>Reminder</span>
        <span><i style="background:var(--info-soft)"></i>Activity</span>
        <span><i style="background:var(--primary-soft)"></i>Planting / harvest</span>
        <span><i style="background:var(--danger-soft)"></i>Harvest due</span>
    </div>
</div>
@endsection
