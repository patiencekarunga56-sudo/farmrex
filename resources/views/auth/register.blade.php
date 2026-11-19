@extends('layouts.guest')
@section('title', 'Create account')

@section('content')
<h1>Create your account</h1>
<p class="muted">Join the group’s farm records system.</p>

<form method="POST" action="{{ route('register') }}">
    @csrf
    @foreach ([['name', 'Full name', 'text', 'name'], ['email', 'Email', 'email', 'username'], ['phone', 'Phone (optional)', 'tel', 'tel']] as [$n, $l, $t, $ac])
        <div class="field {{ $errors->has($n) ? 'has-error' : '' }}">
            <label for="{{ $n }}">{{ $l }}</label>
            <input id="{{ $n }}" type="{{ $t }}" name="{{ $n }}" value="{{ old($n) }}" autocomplete="{{ $ac }}" {{ $n !== 'phone' ? 'required' : '' }}>
            @error($n)<small class="err">{{ $message }}</small>@enderror
        </div>
    @endforeach
    <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">
        <small class="hint">At least 8 characters.</small>
        @error('password')<small class="err">{{ $message }}</small>@enderror
    </div>
    <div class="field">
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
    </div>
    <button class="btn btn-primary btn-block" type="submit">Create account</button>
</form>

<p class="auth-foot muted">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
@endsection
