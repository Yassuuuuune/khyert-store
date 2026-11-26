<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.dashboard')) — {{ config('app.name', 'Sahara Connect') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:500,600,700|nunito:400,600,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="{{ asset('css/sahara.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div class="dash-wrapper">
    @include('layouts.partials.dash-sidebar')

    <div class="dash-main">
        <div class="dash-topbar d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary btn-sm d-lg-none" data-sidebar-toggle aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>

            <h1 class="h5 mb-0 font-display">@yield('page_title', __('admin.dashboard'))</h1>

            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">
                    <i class="bi bi-box-arrow-up-right me-1"></i><span class="d-none d-sm-inline">{{ __('admin.view_site') }}</span>
                </a>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <span class="avatar-circle" style="width:24px;height:24px;font-size:.68rem;">{{ auth()->user()->initials }}</span>
                        <span class="d-none d-md-inline">{{ Str::limit(auth()->user()->name, 16) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>{{ __('nav.profile') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('dash-logout').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>{{ __('nav.logout') }}
                            </a>
                            <form id="dash-logout" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dash-content">
            @include('layouts.partials.flash')
            @yield('content')
        </div>
    </div>
</div>

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/sahara.js') }}"></script>
</body>
</html>
