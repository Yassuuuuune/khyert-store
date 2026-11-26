<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — {{ config('app.name', 'Sahara Connect') }}</title>
    @vite(['resources/sass/app.scss'])
    <style>body{min-height:100vh;display:grid;place-items:center;}</style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <div class="display-1 font-display text-terracotta mb-0" style="font-size:6rem;">@yield('code')</div>
                <h1 class="h3 mb-3">@yield('title')</h1>
                <p class="text-muted mb-4">@yield('message')</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house me-2"></i>{{ __('errors.back_home') }}</a>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-dark"><i class="bi bi-arrow-left me-2"></i>{{ __('errors.back') }}</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
