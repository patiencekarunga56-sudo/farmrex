@extends('layouts.guest')
@section('title', 'Sign in')

@section('content')
<h1>Sign in</h1>
<p class="muted">Sign in to manage your farm records.</p>

<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')<small class="err">{{ $message }}</small>@enderror
    </div>
    <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        @error('password')<small class="err">{{ $message }}</small>@enderror
    </div>
    <label class="check"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
    <button class="btn btn-primary btn-block" type="submit">Sign in</button>
</form>

<p class="auth-foot muted">New to FarmRex? <a href="{{ route('register') }}">Create an account</a></p>

@if (app()->environment('local'))
    <div class="demo-box">
        <strong>Demo accounts</strong> (after <code>php artisan migrate --seed</code>)<br>
        Admin: <code>admin@farmrex.test</code> · Farmer: <code>farmer@farmrex.test</code><br>
        Password: <code>password</code>
    </div>
@endif
@endsection
