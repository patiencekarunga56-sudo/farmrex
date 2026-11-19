<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
    <title>@yield('title') · {{ config('app.name') }}</title>
</head>
<body>
<div class="auth">
    <div class="auth-side">
        <h2>Keep every farm record in one place.</h2>
        <p>Plantings, inputs, harvests, expenses and income – organised, searchable and ready for reports. Built for Butiiti Youth Farmers.</p>
    </div>
    <div class="auth-main">
        <div class="auth-theme"><x-theme-toggle /></div>
        <div class="auth-box">
            <a href="{{ route('home') }}"><img class="logo" src="{{ asset('images/logo.png') }}" alt="FarmRex"></a>
            @if (session('success'))<div class="flash flash-success"><x-icon name="check" :size="18" /> {{ session('success') }}</div>@endif
            @if (session('error'))<div class="flash flash-error"><x-icon name="alert" :size="18" /> {{ session('error') }}</div>@endif
            @yield('content')
        </div>
    </div>
</div>
<script src="{{ asset('js/app.js') }}?v=1"></script>
</body>
</html>
