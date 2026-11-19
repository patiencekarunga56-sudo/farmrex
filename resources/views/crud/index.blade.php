@extends('layouts.app')
@section('title', $title)

@section('content')
<div class="page-head">
    <div>
        <h2 style="font-size:1.25rem">{{ $title }}</h2>
        @if ($subtitle)<p class="muted">{{ $subtitle }}</p>@endif
    </div>
    <a class="btn btn-primary" href="{{ route($route.'.create') }}"><x-icon name="plus" :size="18" /> Add {{ $singular }}</a>
</div>

<div class="card">
    <form class="toolbar" method="GET">
        <div class="search"><x-icon name="search" :size="17" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search {{ strtolower($title) }}…" aria-label="Search">
        </div>
        <button class="btn btn-ghost" type="submit">Search</button>
        @if (request('q'))<a class="btn btn-link" href="{{ route($route.'.index') }}">Clear</a>@endif
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    @foreach ($columns as $col)
                        <th class="{{ in_array($col['type'] ?? 'text', ['money', 'number']) ? 'num' : '' }}">{{ $col['label'] }}</th>
                    @endforeach
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        @foreach ($columns as $col)
                            @php $type = $col['type'] ?? 'text'; $val = $col['value']($item); @endphp
                            <td class="{{ in_array($type, ['money', 'number']) ? 'num' : '' }}">
                                @switch($type)
                                    @case('money') {{ ugx($val) }} @break
                                    @case('date') {{ $val ? $val->format('d M Y') : '—' }} @break
                                    @case('datetime') {{ $val ? $val->format('d M Y, H:i') : 'Never' }} @break
                                    @case('badge') <span class="badge {{ badge_class($val) }}">{{ $val }}</span> @break
                                    @case('number') {{ $val }} @break
                                    @default {{ $val }}
                                @endswitch
                            </td>
                        @endforeach
                        <td class="actions">
                            <a class="icon-btn" href="{{ route($route.'.show', $item->id) }}" title="View"><x-icon name="eye" :size="17" /></a>
                            @if ($canModify($item))
                                <a class="icon-btn" href="{{ route($route.'.edit', $item->id) }}" title="Edit"><x-icon name="edit" :size="17" /></a>
                                <form method="POST" action="{{ route($route.'.destroy', $item->id) }}" data-confirm="Delete this {{ $singular }}? This cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button class="icon-btn danger" title="Delete" type="submit"><x-icon name="trash" :size="17" /></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) + 1 }}" class="empty">
                        {{ request('q') ? 'Nothing matches your search.' : 'No '.strtolower($title).' recorded yet.' }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $items->links() }}
</div>
@endsection
