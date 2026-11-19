@extends('layouts.app')
@section('title', 'Reports')

@section('content')
@php $net = $totalIncome - $totalExpense; @endphp
<div class="page-head no-print">
    <div>
        <h2 style="font-size:1.25rem">Farm reports</h2>
        <p class="muted">Summaries built from the records you have entered.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="btn btn-ghost" href="{{ route('reports.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"><x-icon name="download" :size="17" /> Download ledger (CSV)</a>
        <button class="btn btn-primary" type="button" onclick="window.print()"><x-icon name="printer" :size="17" /> Print</button>
    </div>
</div>

<form method="GET" class="card filter-bar no-print">
    <div class="field"><label for="from">From</label><input id="from" type="date" name="from" value="{{ $from->toDateString() }}"></div>
    <div class="field"><label for="to">To</label><input id="to" type="date" name="to" value="{{ $to->toDateString() }}"></div>
    <button class="btn btn-primary" type="submit">Update report</button>
    <a class="btn btn-link" href="{{ route('reports') }}">Reset</a>
</form>

<h2 class="report-title">FarmRex report · Butiiti Youth Farmers · {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</h2>

<div class="stats" style="margin-bottom:20px">
    <div class="card stat"><span class="ico ico-green"><x-icon name="income" /></span><div><div class="label">Total income</div><div class="value">{{ ugx($totalIncome) }}</div></div></div>
    <div class="card stat"><span class="ico ico-red"><x-icon name="expense" /></span><div><div class="label">Total expenses</div><div class="value">{{ ugx($totalExpense) }}</div></div></div>
    <div class="card stat"><span class="ico ico-blue"><x-icon name="wallet" /></span><div><div class="label">Net result</div><div class="value {{ $net >= 0 ? 'pos' : 'neg' }}">{{ $net < 0 ? '−' : '' }}{{ ugx(abs($net)) }}</div></div></div>
    <div class="card stat"><span class="ico ico-amber"><x-icon name="users" /></span><div><div class="label">Labour</div><div class="value">{{ rtrim(rtrim(number_format($labourDays, 1), '0'), '.') }} days</div></div></div>
</div>

<div class="two-col" style="margin-bottom:20px">
    <section class="card"><div class="card-head"><h2>Income by category</h2></div><x-donut :data="$income" label="Income" /></section>
    <section class="card"><div class="card-head"><h2>Expenses by category</h2></div><x-donut :data="$expense" label="Expenses" /></section>
</div>

<section class="card" style="margin-bottom:20px">
    <div class="card-head"><h2>Performance by planting</h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Planting</th><th>Stage</th><th>Harvested</th><th class="num">Income</th><th class="num">Costs</th><th class="num">Profit / loss</th></tr></thead>
            <tbody>
                @forelse ($plantings as $r)
                    <tr>
                        <td><a href="{{ route('plantings.show', $r['planting']->id) }}">{{ $r['planting']->label }}</a></td>
                        <td><span class="badge {{ badge_class($r['planting']->growth_stage) }}">{{ $r['planting']->growth_stage }}</span></td>
                        <td>{{ $r['harvest'] }}</td>
                        <td class="num">{{ ugx($r['income']) }}</td>
                        <td class="num">{{ ugx($r['cost']) }}</td>
                        <td class="num {{ $r['profit'] >= 0 ? 'pos' : 'neg' }}">{{ $r['profit'] < 0 ? '−' : '' }}{{ ugx(abs($r['profit'])) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No planting-linked records in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="muted" style="margin-top:12px;font-size:.82rem">Only income, expenses, inputs and labour that were linked to a planting are included in this table.</p>
</section>

<div class="two-col">
    <section class="card">
        <div class="card-head"><h2>Harvest totals by crop</h2></div>
        <table class="table">
            <thead><tr><th>Crop</th><th class="num">Quantity</th></tr></thead>
            <tbody>
                @forelse ($harvestByCrop as $h)
                    <tr><td>{{ $h->crop }}</td><td class="num">{{ rtrim(rtrim(number_format($h->qty, 2), '0'), '.') }} {{ $h->unit }}</td></tr>
                @empty
                    <tr><td colspan="2" class="empty">No harvests in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="card">
        <div class="card-head"><h2>Input costs by type</h2></div>
        <table class="table">
            <thead><tr><th>Type</th><th class="num">Items</th><th class="num">Cost</th></tr></thead>
            <tbody>
                @forelse ($inputsByType as $i)
                    <tr><td>{{ $i->type }}</td><td class="num">{{ $i->items }}</td><td class="num">{{ ugx($i->total) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="empty">No inputs in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="card-head" style="margin:22px 0 10px"><h2>Observations</h2></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @forelse ($observations as $cat => $n)
                <span class="badge {{ badge_class($cat) }}">{{ $cat }}: {{ $n }}</span>
            @empty
                <span class="muted">None recorded.</span>
            @endforelse
        </div>
    </section>
</div>
@endsection
