@extends('layouts.app')
@section('title', 'My profile')

@section('content')
<div class="page-head"><div><h2 style="font-size:1.25rem">My profile</h2><p class="muted">Signed in as {{ $user->role_label }}.</p></div></div>

<div class="stack" style="max-width:860px">
    <form method="POST" action="{{ route('profile.update') }}" class="card">
        @csrf @method('PUT')
        <div class="card-head"><h2>Account details</h2></div>
        <div class="form-grid">
            @foreach ([['name', 'Full name', 'text'], ['email', 'Email', 'email'], ['phone', 'Phone', 'tel']] as [$n, $l, $t])
                <div class="field span-6 {{ $errors->has($n) ? 'has-error' : '' }}">
                    <label for="{{ $n }}">{{ $l }}</label>
                    <input id="{{ $n }}" name="{{ $n }}" type="{{ $t }}" value="{{ old($n, $user->{$n}) }}">
                    @error($n)<small class="err">{{ $message }}</small>@enderror
                </div>
            @endforeach
        </div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Save changes</button></div>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="card">
        @csrf @method('PUT')
        <div class="card-head"><h2>Change password</h2></div>
        <div class="form-grid">
            <div class="field span-12 {{ $errors->has('current_password') ? 'has-error' : '' }}">
                <label for="current_password">Current password</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password">
                @if ($errors->has('current_password'))<small class="err">{{ $errors->first('current_password') }}</small>@endif
            </div>
            <div class="field span-6 {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="password">New password</label>
                <input id="password" name="password" type="password" autocomplete="new-password">
                @if ($errors->has('password'))<small class="err">{{ $errors->first('password') }}</small>@endif
            </div>
            <div class="field span-6">
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
            </div>
        </div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Update password</button></div>
    </form>
</div>
@endsection
