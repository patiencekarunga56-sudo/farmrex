<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.head')
    <title>{{ config('app.name') }} · Farm Records Management System</title>
    <meta name="description" content="FarmRex helps Butiiti Youth Farmers record, store, update and retrieve farm records.">
</head>
<body>
<nav class="land-nav">
    <a class="logo-link" href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}" alt="FarmRex"></a>
    <div class="right">
        <x-theme-toggle />
        <a class="btn btn-ghost" href="{{ route('login') }}">Sign in</a>
        <a class="btn btn-primary" href="{{ route('register') }}">Get started</a>
    </div>
</nav>

<header class="hero">
    <div>
        <span class="eyebrow">Built for Butiiti Youth Farmers · Kyenjojo</span>
        <h1>Farm records, <em>organised</em> in one place.</h1>
        <p>Replace notebooks and loose papers with a simple system for planting schedules, inputs, crop observations, harvests, expenses and income – with reminders and reports when you need them.</p>
        <div class="btns">
            <a class="btn btn-primary" href="{{ route('register') }}">Create an account</a>
            <a class="btn btn-white" href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
</header>

<section class="section">
    <h2 class="big">Everything your records need</h2>
    <p class="lead">FarmRex covers the record categories identified for the group: crop, input, labour, production, financial and sales records.</p>
    <div class="features">
        @foreach ([
            ['sprout', 'ico-green', 'Crop records & schedules', 'Record every planting with its field, planting date, growth stage and expected harvest date.'],
            ['activity', 'ico-blue', 'Farm activities', 'Log land preparation, weeding, spraying and fertiliser application as they happen.'],
            ['box', 'ico-amber', 'Input tracking', 'Keep seeds, fertilisers and pesticides with quantities, costs and suppliers.'],
            ['eye', 'ico-red', 'Pest & disease observations', 'Note what you see in the field, how serious it is and what you did about it.'],
            ['basket', 'ico-green', 'Harvest records', 'Capture quantities harvested, quality grade and where the produce is stored.'],
            ['bell', 'ico-amber', 'Date-based reminders', 'Never miss a weeding, spraying or harvest date – and see everything on a calendar.'],
            ['wallet', 'ico-blue', 'Income & expenses', 'See where money comes from and where it goes, with clear summaries.'],
            ['reports', 'ico-green', 'Basic reports', 'Profit per planting, harvest totals and a ledger you can print or download.'],
        ] as [$icon, $cls, $title, $text])
            <div class="card feature">
                <span class="ico {{ $cls }}"><x-icon :name="$icon" :size="22" /></span>
                <h3>{{ $title }}</h3>
                <p>{{ $text }}</p>
            </div>
        @endforeach
    </div>
</section>

<section class="section alt">
    <div class="split">
        <div>
            <h2 class="big">Why keep accurate farm records?</h2>
            <p class="lead" style="margin-bottom:0">Paper records get lost, damaged or filled in differently each time. A single digital system makes it easy to find what happened last season and plan the next one.</p>
            <ul class="checklist">
                @foreach (['Retrieve past plantings, inputs and harvests in seconds', 'Compare costs and income across farming cycles', 'Know exactly what work is coming up next', 'Make planning decisions based on your own data'] as $point)
                    <li><x-icon name="check" :size="20" /> <span>{{ $point }}</span></li>
                @endforeach
            </ul>
        </div>
        <img src="{{ asset('images/office-records.jpg') }}" alt="Two farm managers reviewing records on a laptop" loading="lazy">
    </div>
</section>

<section class="section">
    <h2 class="big">Records that work in the field</h2>
    <p class="lead">Simple screens and clear labels, designed to be used on a phone, tablet or computer.</p>
    <div class="gallery">
        <figure><img src="{{ asset('images/field-tablet.jpg') }}" alt="Farmer using a tablet between crop rows" loading="lazy"><figcaption>Update records right from the garden.</figcaption></figure>
        <figure><img src="{{ asset('images/maize-survey.jpg') }}" alt="Farmer checking a maize field" loading="lazy"><figcaption>Note growth stages and observations as you walk the field.</figcaption></figure>
        <figure><img src="{{ asset('images/banana-harvest.jpg') }}" alt="Farmer inspecting a bunch of bananas" loading="lazy"><figcaption>Log every harvest, quantity and quality.</figcaption></figure>
        <figure><img src="{{ asset('images/reasons-records.jpg') }}" alt="Reasons to maintain accurate farm records" loading="lazy"><figcaption>Good records lead to better decisions.</figcaption></figure>
        <figure><img src="{{ asset('images/software-recordkeeping.jpg') }}" alt="Using software to simplify farm record keeping" loading="lazy"><figcaption>Software makes record keeping simpler.</figcaption></figure>
        <figure><img src="{{ asset('images/records-keeping.jpg') }}" alt="Farm records keeping" loading="lazy"><figcaption>From paper files to one organised system.</figcaption></figure>
    </div>
</section>

<section class="cta">
    <h2 class="big" style="color:#fff">Ready to put your farm records in order?</h2>
    <p>Create an account and add your first planting in minutes.</p>
    <a class="btn btn-primary" href="{{ route('register') }}">Get started free</a>
</section>

<footer class="land-foot">© {{ date('Y') }} FarmRex · Farm Records Management System for Butiiti Youth Farmers, Kyenjojo District, Uganda.</footer>
<script src="{{ asset('js/app.js') }}?v=1"></script>
</body>
</html>
