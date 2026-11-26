/* =============================================================================
   SAHARA CONNECT — front-end behaviours
   Plain (non-module) JavaScript. Requires Bootstrap bundle + Leaflet from CDN.
   Progressive enhancement only: every feature has a server-rendered fallback.
   ============================================================================= */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initNavbarScroll();
        initReveal();
        initQtySteppers();
        initGallery();
        initCartForms();
        initSidebarToggle();
        initCopyButtons();
        initCountdowns();
        initMaps();
    });

    /* ── Sticky navbar shadow ─────────────────────────────────────────── */
    function initNavbarScroll() {
        var nav = document.querySelector('.navbar-sc');
        if (!nav) return;
        var onScroll = function () { nav.classList.toggle('is-scrolled', window.scrollY > 12); };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ── Scroll reveal ────────────────────────────────────────────────── */
    function initReveal() {
        var items = document.querySelectorAll('.reveal');
        if (!items.length) return;
        if (!('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('in-view'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        items.forEach(function (el) { io.observe(el); });
    }

    /* ── Quantity steppers ────────────────────────────────────────────── */
    function initQtySteppers() {
        document.querySelectorAll('[data-qty-stepper]').forEach(function (stepper) {
            var input = stepper.querySelector('input');
            if (!input) return;
            var min = parseInt(input.min || '1', 10);
            var max = parseInt(input.max || '999', 10);

            stepper.querySelectorAll('[data-qty]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var value = parseInt(input.value || '1', 10);
                    value += btn.dataset.qty === 'up' ? 1 : -1;
                    value = Math.max(min, Math.min(max, value));
                    input.value = value;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        });
    }

    /* ── Product gallery ──────────────────────────────────────────────── */
    function initGallery() {
        var main = document.querySelector('[data-gallery-main]');
        if (!main) return;
        document.querySelectorAll('[data-gallery-thumb]').forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                main.src = thumb.dataset.galleryThumb;
                document.querySelectorAll('[data-gallery-thumb]').forEach(function (t) { t.classList.remove('active'); });
                thumb.classList.add('active');
            });
        });
    }

    /* ── Cart AJAX (add / update / remove / clear) ────────────────────── */
    function initCartForms() {
        bindAjaxForm('form[data-cart-add]', false);
        bindAjaxForm('form[data-cart-update]', true);
        bindAjaxForm('form[data-cart-remove]', true);
        bindAjaxForm('form[data-cart-clear]', true);
    }

    function bindAjaxForm(selector, reloadPage) {
        document.querySelectorAll(selector).forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!window.fetch) return; // graceful fall back to a normal submit
                e.preventDefault();

                var button = form.querySelector('[type=submit]');
                if (button) button.disabled = true;

                fetch(form.action, {
                    method: (form.method || 'POST').toUpperCase(),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                })
                    .then(function (res) {
                        if (reloadPage) { window.location.reload(); return null; }
                        return res.json();
                    })
                    .then(function (data) {
                        if (!data) return;
                        if (data.success === false) { showToast(data.message || 'Erreur', 'danger'); return; }
                        if (data.cart_count !== undefined) updateCartBadge(data.cart_count);
                        showToast(data.message || 'Ajouté au panier', 'success');
                        if (document.querySelector('[data-cart-page]') && data.cart_count !== undefined) {
                            window.location.reload();
                        }
                    })
                    .catch(function () { showToast('Une erreur est survenue.', 'danger'); })
                    .finally(function () { if (button) button.disabled = false; });
            });
        });
    }

    function updateCartBadge(count) {
        var badge = document.querySelector('[data-cart-count]');
        if (!badge) return;
        if (count === undefined) {
            fetch('/panier/count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (d) { setBadge(badge, d.count); })
                .catch(function () {});
        } else {
            setBadge(badge, count);
        }
    }

    function setBadge(badge, count) {
        badge.textContent = count;
        badge.classList.toggle('d-none', !count || count <= 0);
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    /* ── Toast notifications ──────────────────────────────────────────── */
    function showToast(message, variant) {
        variant = variant || 'success';
        if (!window.bootstrap || !window.bootstrap.Toast) { return; }
        var container = document.getElementById('scToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'scToastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1090';
            document.body.appendChild(container);
        }
        var el = document.createElement('div');
        el.className = 'toast align-items-center text-bg-' + variant + ' border-0';
        el.setAttribute('role', 'alert');
        el.innerHTML = '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
        container.appendChild(el);
        var toast = new window.bootstrap.Toast(el, { delay: 2600 });
        toast.show();
        el.addEventListener('hidden.bs.toast', function () { el.remove(); });
    }

    /* ── Dashboard sidebar (mobile) ───────────────────────────────────── */
    function initSidebarToggle() {
        var toggle = document.querySelector('[data-sidebar-toggle]');
        var sidebar = document.querySelector('.dash-sidebar');
        if (!toggle || !sidebar) return;

        toggle.addEventListener('click', function () { sidebar.classList.toggle('open'); });

        document.addEventListener('click', function (e) {
            if (window.innerWidth > 992) return;
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) sidebar.classList.remove('open');
        });
    }

    /* ── Copy-to-clipboard (affiliate links) ──────────────────────────── */
    function initCopyButtons() {
        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var value = btn.dataset.copy;
                var done = function () { showToast('Lien copié !', 'success'); };
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(value).then(done).catch(function () {});
                } else {
                    var tmp = document.createElement('textarea');
                    tmp.value = value;
                    document.body.appendChild(tmp);
                    tmp.select();
                    document.execCommand('copy');
                    tmp.remove();
                    done();
                }
            });
        });
    }

    /* ── Promo countdowns ─────────────────────────────────────────────── */
    function initCountdowns() {
        document.querySelectorAll('[data-countdown]').forEach(function (el) {
            var end = new Date(el.dataset.countdown).getTime();
            var tick = function () {
                var diff = end - Date.now();
                if (diff <= 0) { el.textContent = el.dataset.countdownDone || 'Terminé'; return; }
                var d = Math.floor(diff / 864e5);
                var h = Math.floor((diff % 864e5) / 36e5);
                var m = Math.floor((diff % 36e5) / 6e4);
                el.textContent = d + 'j ' + h + 'h ' + m + 'm';
            };
            tick();
            setInterval(tick, 60000);
        });
    }

    /* ── Leaflet interactive maps ─────────────────────────────────────── */
    function initMaps() {
        document.querySelectorAll('[data-map]').forEach(function (box) {
            if (!window.L) return;
            var lat = parseFloat(box.dataset.lat);
            var lng = parseFloat(box.dataset.lng);
            var zoom = parseInt(box.dataset.zoom || '6', 10);
            if (isNaN(lat) || isNaN(lng)) return;

            var map = window.L.map(box).setView([lat, lng], zoom);
            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            window.L.marker([lat, lng]).addTo(map)
                .bindPopup('<strong>' + (box.dataset.title || '') + '</strong>');

            // Markers provided as JSON in a child script tag
            var dataEl = box.querySelector('script[type="application/json"]');
            if (dataEl) {
                try {
                    var points = JSON.parse(dataEl.textContent);
                    var bounds = [];
                    points.forEach(function (p) {
                        if (p.lat == null || p.lng == null) return;
                        var marker = window.L.marker([p.lat, p.lng]).addTo(map);
                        var html = '<div class="map-popup">';
                        if (p.image) html += '<img src="' + p.image + '" alt="">';
                        html += '<strong>' + (p.title || '') + '</strong>';
                        if (p.meta) html += '<div class="text-muted small">' + p.meta + '</div>';
                        if (p.url) html += '<a class="small" href="' + p.url + '">' + (p.cta || 'Voir') + '</a>';
                        html += '</div>';
                        marker.bindPopup(html);
                        bounds.push([p.lat, p.lng]);
                    });
                    if (bounds.length > 1) map.fitBounds(bounds, { padding: [40, 40] });
                } catch (err) { /* ignore malformed data */ }
            }
        });
    }

    window.scToast = showToast;
    window.scUpdateCartBadge = updateCartBadge;
})();
