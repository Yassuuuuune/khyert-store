@php
    $footerCategories = \App\Models\Category::active()->parents()->orderBy('sort_order')->take(6)->get();
    $footerTourism = \App\Models\TourismCategory::active()->orderBy('sort_order')->take(6)->get();
@endphp

<footer class="footer-sc mt-auto">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="footer-brand mb-3">
                    <i class="bi bi-brightness-high-fill text-warning"></i> SAHARA <span class="text-warning">CONNECT</span>
                </div>
                <p>{{ __('footer.about_text') }}</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="social-btn" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-btn" aria-label="X"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-btn" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="mb-3">{{ __('footer.shop') }}</h6>
                <ul class="list-unstyled d-grid gap-2">
                    @foreach ($footerCategories as $cat)
                        <li><a href="{{ route('products.index', ['category' => $cat->slug]) }}">{{ $cat->localized_name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="mb-3">{{ __('footer.tourism') }}</h6>
                <ul class="list-unstyled d-grid gap-2">
                    @foreach ($footerTourism as $cat)
                        <li><a href="{{ route('tourism.index', ['category' => $cat->slug]) }}">{{ $cat->localized_name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-lg-4">
                <h6 class="mb-3">{{ __('footer.newsletter') }}</h6>
                <p class="small">{{ __('footer.newsletter_text') }}</p>
                <form method="POST" action="{{ route('newsletter.subscribe') }}" class="d-flex gap-2">
                    @csrf
                    <input type="email" name="email" class="form-control" placeholder="{{ __('footer.email_placeholder') }}" required>
                    <button class="btn btn-gold text-nowrap" type="submit">{{ __('footer.subscribe') }}</button>
                </form>

                <ul class="list-unstyled d-grid gap-2 mt-4 small">
                    <li><a href="{{ route('pages.become-seller') }}"><i class="bi bi-shop me-2"></i>{{ __('nav.become_seller') }}</a></li>
                    <li><a href="{{ route('pages.become-affiliate') }}"><i class="bi bi-share me-2"></i>{{ __('nav.become_affiliate') }}</a></li>
                    <li><a href="{{ route('pages.contact') }}"><i class="bi bi-envelope me-2"></i>{{ __('nav.contact') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'Sahara Connect') }}. {{ __('footer.rights') }}</span>
            <span class="d-flex gap-3">
                <a href="{{ route('pages.about') }}">{{ __('nav.about') }}</a>
                <a href="{{ route('pages.contact') }}">{{ __('nav.contact') }}</a>
            </span>
        </div>
    </div>
</footer>
