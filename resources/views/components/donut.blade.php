@props(['data', 'label' => 'Total'])
@php
    $items = collect($data);
    $total = (float) $items->sum();
    $stops = [];
    $acc = 0;
    $i = 0;
    foreach ($items as $name => $value) {
        $pct = $total > 0 ? $value / $total * 100 : 0;
        $stops[] = palette($i).' '.round($acc, 3).'% '.round($acc + $pct, 3).'%';
        $acc += $pct;
        $i++;
    }
    $gradient = $total > 0 ? implode(', ', $stops) : 'var(--border) 0% 100%';
@endphp
<div class="summary">
    <div class="donut" style="background: conic-gradient({{ $gradient }})" role="img" aria-label="{{ $label }} breakdown">
        <div class="donut-hole">
            <small>{{ $label }}</small>
            <strong>{{ ugx($total) }}</strong>
        </div>
    </div>
    <ul class="legend">
        @forelse ($items as $name => $value)
            <li>
                <span class="sw" style="background: {{ palette($loop->index) }}"></span>
                <span class="name">{{ $name }}</span>
                <span class="amt">{{ ugx($value) }}</span>
            </li>
        @empty
            <li class="muted">No entries for this period yet.</li>
        @endforelse
    </ul>
</div>
