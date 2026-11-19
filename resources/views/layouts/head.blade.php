<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
{{-- Apply the saved theme before first paint so there is no flash --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('farmrex-theme');
            if (!t) { t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
            document.documentElement.setAttribute('data-theme', t);
        } catch (e) { document.documentElement.setAttribute('data-theme', 'light'); }
    })();
</script>
<link rel="stylesheet" href="{{ asset('css/farmrex.css') }}?v=1">
