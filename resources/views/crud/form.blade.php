@extends('layouts.app')
@section('title', ($mode === 'create' ? 'Add ' : 'Edit ').$singular)

@section('content')
<div class="page-head">
    <div>
        <h2 style="font-size:1.25rem">{{ $mode === 'create' ? 'Add '.$singular : 'Edit '.$singular }}</h2>
        <p class="muted"><a href="{{ route($route.'.index') }}">← Back to {{ strtolower($title) }}</a></p>
    </div>
</div>

<form method="POST" class="card form-card" novalidate
      action="{{ $mode === 'create' ? route($route.'.store') : route($route.'.update', $item->id) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif

    @if ($hint)<div class="notice">{{ $hint }}</div>@endif

    <div class="form-grid">
        @foreach ($fields as $name => $f)
            @php
                $type = $f['type'] ?? 'text';
                $rules = is_array($f['rules'] ?? null) ? implode('|', array_map('strval', $f['rules'])) : ($f['rules'] ?? '');
                $required = str_contains($rules, 'required');
                $value = $type === 'password' ? '' : old($name, $mode === 'edit' ? $item->{$name} : ($f['default'] ?? null));
                if ($value instanceof \DateTimeInterface) { $value = $value->format('Y-m-d'); }
                if (is_bool($value)) { $value = (int) $value; }
                $span = $f['col'] ?? ($type === 'textarea' ? 12 : 6);
            @endphp
            <div class="field span-{{ $span }} {{ $errors->has($name) ? 'has-error' : '' }}">
                <label for="{{ $name }}">{{ $f['label'] }} @if ($required)<span class="req">*</span>@endif</label>

                @if ($type === 'select' && empty($f['options']) && ! empty($f['empty_hint']))
                    {{-- Nothing to choose from yet: say so and point to where to add it. --}}
                    <div class="notice" style="margin:0">
                        {{ $f['empty_hint'] }}
                        @if (! empty($f['empty_link']))<a href="{{ $f['empty_link'][1] }}"><strong>{{ $f['empty_link'][0] }}</strong></a>@endif
                    </div>
                @elseif ($type === 'select')
                    <select id="{{ $name }}" name="{{ $name }}">
                        <option value="">— Select —</option>
                        @foreach ($f['options'] as $k => $label)
                            <option value="{{ $k }}" @selected((string) $value === (string) $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                @elseif ($type === 'textarea')
                    <textarea id="{{ $name }}" name="{{ $name }}">{{ $value }}</textarea>
                @else
                    <input id="{{ $name }}" name="{{ $name }}"
                           type="{{ $type === 'money' ? 'number' : $type }}"
                           @if (in_array($type, ['number', 'money'])) step="{{ $f['step'] ?? ($type === 'money' ? '1' : 'any') }}" min="0" inputmode="decimal" @endif
                           value="{{ $value }}" placeholder="{{ $f['placeholder'] ?? '' }}"
                           @if ($type === 'password') autocomplete="new-password" @endif>
                @endif

                @error($name)<small class="err">{{ $message }}</small>@enderror
                @if (! empty($f['hint']))<small class="hint">{{ $f['hint'] }}</small>@endif
            </div>
        @endforeach
    </div>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">{{ $mode === 'create' ? 'Save '.$singular : 'Save changes' }}</button>
        <a class="btn btn-ghost" href="{{ route($route.'.index') }}">Cancel</a>
    </div>
</form>
@endsection
