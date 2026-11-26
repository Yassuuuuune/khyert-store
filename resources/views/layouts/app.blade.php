<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('site.tagline')) — {{ config('app.name', 'Sahara Connect') }}</title>
    <meta name="description" content="@yield('meta_description', __('site.meta_default'))">
    @hasSection('meta_keywords')
        <meta name="keywords" content="@yield('meta_keywords')">
    @endif

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name', 'Sahara Connect') }}">
    <meta property="og:title" content="@yield('og_title', config('app.name', 'Sahara Connect'))">
    <meta property="og:description" content="@yield('meta_description', __('site.meta_default'))">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:500,600,700|nunito:400,600,700" rel="stylesheet">

    {{-- Bootstrap 5.3 + Bootstrap Icons + Leaflet (CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">

    {{-- Sahara Connect brand layer --}}
    <link href="{{ asset('css/sahara.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="@yield('body_class')">

    @include('layouts.partials.navbar')

    <main>
        @include('layouts.partials.flash')
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/sahara.js') }}"></script>
    @stack('scripts')
</body>
</html>
