@extends('layouts.app')
@section('title', ucfirst($singular))

@section('content')
<div class="page-head">
    <div>
        <h2 style="font-size:1.25rem">{{ ucfirst($singular) }} details</h2>
        <p class="muted"><a href="{{ route($route.'.index') }}">← Back to {{ strtolower($title) }}</a></p>
    </div>
    @if ($canModify($item))
        <div style="display:flex;gap:10px">
            <a class="btn btn-primary" href="{{ route($route.'.edit', $item->id) }}"><x-icon name="edit" :size="17" /> Edit</a>
            <form method="POST" action="{{ route($route.'.destroy', $item->id) }}" data-confirm="Delete this {{ $singular }}? This cannot be undone.">
                @csrf @method('DELETE')
                <button class="btn btn-ghost" type="submit"><x-icon name="trash" :size="17" /> Delete</button>
            </form>
        </div>
    @endif
</div>

<div class="card">
    <dl class="detail">
        @foreach ($fields as $name => $f)
            @continue(($f['type'] ?? 'text') === 'password')
            @php
                $type = $f['type'] ?? 'text';
                $val = $item->{$name};
                if ($val instanceof \DateTimeInterface) { $display = $val->format('d M Y'); }
                elseif ($type === 'select') { $display = $f['options'][$val] ?? $val; }
                elseif ($type === 'money') { $display = $val === null ? null : ugx($val); }
                else { $display = $val; }
            @endphp
            <dt>{{ $f['label'] }}</dt>
            <dd>{{ ($display === null || $display === '') ? '—' : $display }}</dd>
        @endforeach
        @if (isset($item->total_cost))<dt>Total cost</dt><dd>{{ ugx($item->total_cost) }}</dd>@endif
        @if (isset($item->created_at))<dt>Recorded</dt><dd>{{ $item->created_at->format('d M Y, H:i') }}</dd>@endif
    </dl>
</div>
@endsection
