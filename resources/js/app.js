import './bootstrap';

import L from 'leaflet';
window.L = L;

/**
 * SAHARA CONNECT — front-end behaviours.
 * Progressive enhancement only: every feature below has a server-rendered
 * fallback so the platform stays fully usable without JavaScript.
 */
document.addEventListener('DOMContentLoaded', () => {
    initNavbarScroll();
    initReveal();
    initQtySteppers();
    initGallery();
    initCartForms();
    initSidebarToggle();
    initCopyButtons();
    initCountdowns();
});

/* ── Sticky navbar shadow ─────────────────────────────────────────────── */
function initNavbarScroll() {
    const nav = document.querySelector('.navbar-sc');
    if (!nav) return;
    const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 12);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

/* ── Scroll reveal ────────────────────────────────────────────────────── */
function initReveal() {
    const items = document.querySelectorAll('.reveal');
    if (!items.length) return;
    if (!('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('in-view'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    items.forEach((el) => io.observe(el));
}

/* ── Quantity steppers ────────────────────────────────────────────────── */
function initQtySteppers() {
    document.querySelectorAll('[data-qty-stepper]').forEach((stepper) => {
        const input = stepper.querySelector('input');
        if (!input) return;
        const min = parseInt(input.min || '1', 10);
        const max = parseInt(input.max || '999', 10);

        stepper.querySelectorAll('[data-qty]').forEach((btn) => {
            btn.addEventListener('click', () => {
                let value = parseInt(input.value || '1', 10);
                value += btn.dataset.qty === 'up' ? 1 : -1;
                value = Math.max(min, Math.min(max, value));
                input.value = value;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    });
}

/* ── Product gallery ──────────────────────────────────────────────────── */
function initGallery() {
    const main = document.querySelector('[data-gallery-main]');
    if (!main) return;
    document.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
        thumb.addEventListener('click', () => {
            main.src = thumb.dataset.galleryThumb;
            document.querySelectorAll('[data-gallery-thumb]').forEach((t) => t.classList.remove('active'));
            thumb.classList.add('active');
        });
    });
}

/* ── Cart AJAX (add / update / remove) ────────────────────────────────── */
function initCartForms() {
    bindAjaxForm('form[data-cart-add]', () => updateCartBadge);
    bindAjaxForm('form[data-cart-update]', null, true);
    bindAjaxForm('form[data-cart-remove]', null, true);
    bindAjaxForm('form[data-cart-clear]', null, true);
}

function bindAjaxForm(selector, after, reloadPage = false) {
    document.querySelectorAll(selector).forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.fetch) return; // fall back to a normal submit
            e.preventDefault();

            const button = form.querySelector('[type=submit]');
            if (button) button.disabled = true;

            fetch(form.action, {
                method: form.method.toUpperCase() === 'GET' ? 'POST' : form.method.toUpperCase(),
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                body: new FormData(form),
            })
                .then((res) => {
                    if (reloadPage) { window.location.reload(); return null; }
                    return res.json();
                })
                .then((data) => {
                    if (!data) return;
                    if (data.success === false) { showToast(data.message || 'Erreur', 'danger'); return; }
                    if (data.cart_count !== undefined) updateCartBadge(data.cart_count);
                    showToast(data.message || 'Ajouté au panier', 'success');
                    if (document.querySelector('[data-cart-page]') && data.cart_count !== undefined) {
                        window.location.reload();
                    }
                })
                .catch(() => showToast('Une erreur est survenue.', 'danger'))
                .finally(() => { if (button) button.disabled = false; });
        });
    });
}

function updateCartBadge(count) {
    const badge = document.querySelector('[data-cart-count]');
    if (!badge) return;
    if (count === undefined) {
        fetch('/panier/count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((r) => r.json())
            .then((d) => setBadge(badge, d.count))
            .catch(() => {});
    } else {
        setBadge(badge, count);
    }
}

function setBadge(badge, count) {
    badge.textContent = count;
    badge.classList.toggle('d-none', !count || count <= 0);
}

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

/* ── Toast notifications ──────────────────────────────────────────────── */
function showToast(message, variant = 'success') {
    let container = document.getElementById('scToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'scToastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1090';
        document.body.appendChild(container);
    }
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${variant} border-0`;
    el.setAttribute('role', 'alert');
    el.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    container.appendChild(el);
    const toast = new window.bootstrap.Toast(el, { delay: 2600 });
    toast.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
}

/* ── Dashboard sidebar (mobile) ───────────────────────────────────────── */
function initSidebarToggle() {
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('.dash-sidebar');
    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));

    document.addEventListener('click', (e) => {
        if (window.innerWidth > 992) return;
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) sidebar.classList.remove('open');
    });
}

/* ── Copy-to-clipboard (affiliate links) ──────────────────────────────── */
function initCopyButtons() {
    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const value = btn.dataset.copy;
            const done = () => showToast('Lien copié !', 'success');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(value).then(done).catch(() => {});
            } else {
                const tmp = document.createElement('textarea');
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

/* ── Promo countdowns ─────────────────────────────────────────────────── */
function initCountdowns() {
    document.querySelectorAll('[data-countdown]').forEach((el) => {
        const end = new Date(el.dataset.countdown).getTime();
        const tick = () => {
            const diff = end - Date.now();
            if (diff <= 0) { el.textContent = el.dataset.countdownDone || 'Terminé'; return; }
            const d = Math.floor(diff / 864e5);
            const h = Math.floor((diff % 864e5) / 36e5);
            const m = Math.floor((diff % 36e5) / 6e4);
            el.textContent = `${d}j ${h}h ${m}m`;
        };
        tick();
        setInterval(tick, 60000);
    });
}

window.scToast = showToast;
