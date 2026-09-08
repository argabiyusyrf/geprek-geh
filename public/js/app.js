/* Geprek Geh — Motion Choreography
   transform/opacity only · IntersectionObserver · cubic-bezier spring */

const easeExpo = 'cubic-bezier(0.19,1,0.22,1)';

function prefersReduced() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/* ── Lenis smooth scroll (vendored) — rAF-driven, GPU-safe ── */
(function lenisInit() {
    if (!window.Lenis || prefersReduced()) return;
    const lenis = new Lenis({
        duration: 1.15,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        smoothWheel: true,
        touchMultiplier: 1.6,
    });
    window.__lenis = lenis;

    function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);
})();

/* ── Scroll reveal (IntersectionObserver — never 'scroll' listeners) ── */
(function reveal() {
    const base = document.querySelectorAll('[data-reveal], .card, .product-card, .category-card, .order-card, .stat-card, .about-item');
    if (!('IntersectionObserver' in window) || prefersReduced()) {
        base.forEach((el) => el.classList.add('in'));
        return;
    }

    // Auto-stagger children of any [data-reveal-stagger] container.
    const staggers = document.querySelectorAll('[data-reveal-stagger]');
    staggers.forEach((host) => {
        Array.from(host.children).forEach((child, i) => {
            child.dataset.reveal = '';
            child.dataset.stagger = i;
        });
    });

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const explicit = parseInt(el.dataset.revealDelay || '0', 10);
                const stagger = parseInt(el.dataset.stagger || '0', 10);
                const delay = explicit ? explicit * 70 : Math.min(stagger * 70, 350);
                el.style.transitionDelay = delay + 'ms';
                el.classList.add('in');
                io.unobserve(el);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -48px 0px' });

    base.forEach((el, i) => {
        if (!el.dataset.stagger) el.dataset.stagger = i % 12;
        io.observe(el);
    });
})();

/* ── Global search overlay ── */
(function searchOverlay() {
    const overlay = document.getElementById('searchOverlay');
    const input = document.getElementById('searchInput');
    const results = document.getElementById('searchResults');
    const triggers = document.querySelectorAll('[data-search-trigger]');
    if (!overlay || !input) return;

    let lastQ = '';
    let debounceTimer = null;

    function open() {
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (window.__lenis) window.__lenis.stop();
        setTimeout(() => input.focus({ preventScroll: true }), 60);
    }
    function close() {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (window.__lenis) window.__lenis.start();
        input.value = '';
        results.innerHTML = '<div class="search-empty">Ketik minimal 2 karakter untuk mencari.</div>';
    }
    window.closeSearch = close;

    function escape(s) { return (s || '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }

    function renderEmpty(msg) {
        results.innerHTML = '<div class="search-empty">' + escape(msg) + '</div>';
    }
    function renderItems(items, total, viewAllHref) {
        if (!items.length) { renderEmpty('Tidak ada hasil yang cocok.'); return; }
        const list = items.map((it) => (
            '<a class="search-item" href="' + escape(it.href) + '">' +
            '<span class="search-thumb">' + (it.image ? '<img src="' + escape(it.image) + '" alt="" loading="lazy">' : '') + '</span>' +
            '<span class="search-meta"><small>' + escape(it.category) + '</small><b>' + escape(it.name) + '</b></span>' +
            '<span class="search-price">Rp ' + Number(it.price).toLocaleString('id-ID') + '</span>' +
            '</a>'
        )).join('');
        const more = total > items.length
            ? '<a class="search-view-all" href="' + escape(viewAllHref) + '">Lihat semua ' + total + ' hasil &rarr;</a>'
            : '';
        results.innerHTML = list + more;
    }

    function fetchSuggest(q) {
        if (q === lastQ) return;
        lastQ = q;
        fetch('/geprek-geh/search?q=' + encodeURIComponent(q) + '&_=' + Date.now(), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
        .then((r) => r.json())
        .then((data) => { if (data && data.ok) renderItems(data.items || [], data.total || 0, data.view_all_href || ''); })
        .catch(() => renderEmpty('Koneksi bermasalah.'));
    }

    input.addEventListener('input', () => {
        const q = input.value.trim();
        clearTimeout(debounceTimer);
        if (q.length < 2) { results.innerHTML = '<div class="search-empty">Ketik minimal 2 karakter untuk mencari.</div>'; return; }
        debounceTimer = setTimeout(() => fetchSuggest(q), 180);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
        if (e.key === 'Enter') {
            const q = input.value.trim();
            if (q.length >= 1) window.location.href = '/geprek-geh/products?q=' + encodeURIComponent(q);
        }
    });

    triggers.forEach((t) => t.addEventListener('click', open));
    overlay.querySelectorAll('[data-close-search]').forEach((b) => b.addEventListener('click', close));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) close();
        if ((e.key === '/' || (e.metaKey && e.key === 'k')) && !overlay.classList.contains('is-open') &&
            !['INPUT','TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            open();
        }
    });
})();

/* ── Fluid Island nav: scroll state ── */
const navPill = document.querySelector('.nav-pill');
if (navPill) {
    let ticking = false;
    const update = () => {
        navPill.classList.toggle('scrolled', window.scrollY > 24);
        ticking = false;
    };
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(update);
            ticking = true;
        }
    }, { passive: true });
}

/* ── Hamburger morph → fullscreen glass overlay ── */
(function menu() {
    const burger = document.getElementById('navBurger');
    const overlay = document.getElementById('navOverlay');
    const closeBtn = document.getElementById('navClose');
    if (!burger || !overlay) return;

    const open = () => {
        burger.classList.add('open');
        burger.setAttribute('aria-expanded', 'true');
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
        document.body.classList.add('menu-locked');
        if (window.__lenis) window.__lenis.stop();
    };
    const close = () => {
        burger.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
        document.body.classList.remove('menu-locked');
        if (window.__lenis) window.__lenis.start();
    };

    window.closeNav = close;

    burger.addEventListener('click', () => overlay.classList.contains('open') ? close() : open());
    if (closeBtn) closeBtn.addEventListener('click', close);
    overlay.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));
    overlay.addEventListener('click', (e) => { if (e.target === overlay || e.target.closest('.overlay-inner') === null) close(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
})();

/* ── Account dropdown (header) ── */
(function accountDropdown() {
    const accounts = document.querySelectorAll('[data-account]');
    if (!accounts.length) return;

    const closeAll = () => accounts.forEach((a) => {
        a.removeAttribute('data-open');
        const t = a.querySelector('.account-trigger');
        if (t) t.setAttribute('aria-expanded', 'false');
    });

    accounts.forEach((acc) => {
        const trigger = acc.querySelector('.account-trigger');
        if (!trigger) return;
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = acc.hasAttribute('data-open');
            closeAll();
            if (!isOpen) {
                acc.setAttribute('data-open', '');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });
        acc.querySelectorAll('a').forEach((a) => a.addEventListener('click', closeAll));
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-account]')) closeAll();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); });
})();

/* ── Notification dropdown (header) ── */
(function notifDropdown() {
    const wrap = document.querySelector('[data-notif]');
    if (!wrap) return;

    const trigger = wrap.querySelector('[data-notif-trigger]');
    const panel = wrap.querySelector('[data-notif-panel]');
    if (!trigger || !panel) return;

    let token = '';
    const readAllForm = wrap.querySelector('[data-notif-readall]');
    if (readAllForm) {
        const t = readAllForm.querySelector('input[name="_token"]');
        if (t) token = t.value;
    }

    const close = () => {
        wrap.removeAttribute('data-open');
        trigger.setAttribute('aria-expanded', 'false');
        if (window.lenis) window.lenis.start();
    };

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = wrap.hasAttribute('data-open');
        document.querySelectorAll('[data-account]').forEach((a) => a.removeAttribute('data-open'));
        const otherTriggers = document.querySelectorAll('[data-account] .account-trigger');
        otherTriggers.forEach((t) => t.setAttribute('aria-expanded', 'false'));
        if (isOpen) {
            close();
        } else {
            wrap.setAttribute('data-open', '');
            trigger.setAttribute('aria-expanded', 'true');
            if (window.lenis) window.lenis.stop();
        }
    });

    panel.querySelectorAll('.notif-item').forEach((item) => {
        item.addEventListener('click', (e) => {
            const readUrl = item.dataset.readUrl;
            const href = item.getAttribute('href');
            if (readUrl && token && href) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('_token', token);
                fetch(readUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .finally(() => { window.location.href = href; });
            }
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-notif]') && wrap.hasAttribute('data-open')) close();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
})();

/* ── Magnetic buttons (pointer-friendly, GPU-safe transform) ── */
(function magnetic() {
    if (prefersReduced() || !window.matchMedia('(hover: hover)').matches) return;
    const mags = document.querySelectorAll('.magnetic');
    mags.forEach((btn) => {
        btn.addEventListener('mousemove', (e) => {
            const r = btn.getBoundingClientRect();
            const dx = e.clientX - (r.left + r.width / 2);
            const dy = e.clientY - (r.top + r.height / 2);
            btn.style.transform = `translate(${dx * 0.18}px, ${dy * 0.22}px)`;
            btn.style.transition = 'transform .25s cubic-bezier(0.32,0.72,0,1)';
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.transition = 'transform .6s cubic-bezier(0.19,1,0.22,1)';
            btn.style.transform = '';
        });
    });
})();

/* ── Toast auto-dismiss ── */
document.querySelectorAll('.alert').forEach((el) => {
    setTimeout(() => {
        el.style.transition = 'opacity .6s cubic-bezier(0.25,0.5,0.2,1), transform .6s cubic-bezier(0.19,1,0.22,1)';
        el.style.opacity = '0';
        el.style.transform = 'translate(-50%, -12px)';
        setTimeout(() => el.remove(), 600);
    }, 4200);
});

/* ── Quantity stepper ── */
function changeQty(delta) {
    const input = document.getElementById('qty');
    if (!input) return;
    let val = parseInt(input.value, 10) + delta;
    if (Number.isNaN(val) || val < 1) val = 1;
    const max = parseInt(input.max, 10);
    if (max > 0 && val > max) val = max;
    input.value = val;
}

/* ── Cart quantity stepper: adjust + submit form ── */
(function cartSteppers() {
    const forms = document.querySelectorAll('.cart-qty-form');
    if (!forms.length) return;

    forms.forEach((form) => {
        const stepper = form.querySelector('.cart-stepper');
        const input = form.querySelector('input[name="quantity"]');
        if (!stepper || !input) return;

        stepper.querySelectorAll('.cart-step-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const delta = parseInt(btn.dataset.qtyStep, 10);
                const min = parseInt(input.min, 10) || 1;
                const max = parseInt(input.max, 10) || Infinity;
                let val = (parseInt(input.value, 10) || min) + delta;
                if (val < min) val = min;
                if (val > max) val = max;
                input.value = val;
                form.submit();
            });
        });
    });
})();

/* ── Confirm dialog: promise-based replacement for window.confirm ── */
(function confirmDialog() {
    const active = { el: null, resolve: null };

    window.gehAlert = function (opts) {
        opts = opts || {};
        if (active.el) {
            active.resolve(false);
            active.el.remove();
        }

        const backdrop = document.createElement('div');
        backdrop.className = 'confirm-backdrop';
        backdrop.innerHTML =
            `<div class="confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="geh-confirm-title">
                <div class="confirm-icon">
                    <span class="confirm-icon-ring">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4M12 17h.01"/></svg>
                    </span>
                </div>
                <h3 class="confirm-title" id="geh-confirm-title">${opts.title || 'Konfirmasi'}</h3>
                <p class="confirm-msg">${opts.message || ''}</p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-outline" data-geh-cancel>${opts.cancelText || 'Batal'}</button>
                    <button type="button" class="btn btn-danger" data-geh-ok>${opts.okText || 'Ya, Lanjut'}</button>
                </div>
            </div>`;

        document.body.appendChild(backdrop);
        requestAnimationFrame(() => requestAnimationFrame(() => backdrop.classList.add('show')));
        document.body.style.overflow = 'hidden';
        if (window.__lenis) window.__lenis.stop();

        const promise = new Promise((resolve) => { active.resolve = resolve; active.el = backdrop; });

        const close = (val) => {
            if (document.body.style.overflow === 'hidden') document.body.style.overflow = '';
            if (window.__lenis) window.__lenis.start();
            backdrop.classList.remove('show');
            setTimeout(() => backdrop.remove(), 350);
            const r = active.resolve;
            active.el = null;
            active.resolve = null;
            if (r) r(val);
        };

        backdrop.querySelector('[data-geh-ok]').addEventListener('click', () => close(true));
        backdrop.querySelector('[data-geh-cancel]').addEventListener('click', () => close(false));
        backdrop.addEventListener('click', (e) => { if (e.target === backdrop) close(false); });
        const onEsc = (e) => { if (e.key === 'Escape') { close(false); document.removeEventListener('keydown', onEsc); } };
        document.addEventListener('keydown', onEsc);

        // Esc handler is intentionally not removed when the user clicks OK/Cancel,
        // because the backdrop itself is removed before the listener fires.
        return promise;
    };
    window.gehConfirm = window.gehAlert;

    /* Wire [data-confirm] submit forms */
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const btn = form.querySelector('[type="submit"]');
            gehAlert({ message: form.dataset.confirm || 'Yakin melanjutkan?' }).then((ok) => {
                if (ok) {
                    if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }
                    form.submit();
                }
            });
        });
    });
})();

/* ── Checkout: phone mask + subtle interactions (all no-JS safe) ── */
/* ── Checkout: saved address selector ── */
(function savedAddress() {
    const wrap = document.querySelector('[data-saved-addresses]');
    if (!wrap) return;

    const form = wrap.closest('form.checkout-form');
    if (!form) return;

    const nameInput = form.querySelector('input[name="recipient_name"]');
    const phoneInput = form.querySelector('input[name="phone"]');
    const addressInput = form.querySelector('textarea[name="address"]');
    const provinceInput = form.querySelector('input[name="province"]');
    const cityInput = form.querySelector('input[name="city"]');
    const districtInput = form.querySelector('input[name="district"]');
    const villageInput = form.querySelector('input[name="village"]');
    const postalInput = form.querySelector('input[name="postal_code"]');
    const addressIdInput = document.getElementById('addressIdInput');
    const fieldsWrap = document.getElementById('checkoutFields');

    const savedCards = wrap.querySelectorAll('[data-saved-address]');
    const manualCard = wrap.querySelector('[data-saved-manual]');

    const userName = nameInput && nameInput.defaultValue || '';
    const userPhone = phoneInput && phoneInput.defaultValue || '';

    function fillFields(d) {
        if (nameInput) nameInput.value = d.recipient || '';
        if (phoneInput) phoneInput.value = d.phone || '';
        if (addressInput) addressInput.value = d.address || '';
        if (provinceInput) provinceInput.value = d.province || '';
        if (cityInput) cityInput.value = d.city || '';
        if (districtInput) districtInput.value = d.district || '';
        if (villageInput) villageInput.value = d.village || '';
        if (postalInput) postalInput.value = d.postal || '';
    }

    function collapseFields() {
        if (fieldsWrap) fieldsWrap.classList.add('is-collapsed');
    }
    function expandFields() {
        if (fieldsWrap) fieldsWrap.classList.remove('is-collapsed');
    }

    function setActive(card) {
        wrap.querySelectorAll('.sa-radio-card').forEach((c) => c.classList.remove('is-active'));
        if (card) card.classList.add('is-active');
    }

    savedCards.forEach((card) => {
        card.addEventListener('click', () => {
            fillFields({
                recipient: card.dataset.recipient,
                phone: card.dataset.phone,
                address: card.dataset.address,
                province: card.dataset.province,
                city: card.dataset.city,
                district: card.dataset.district,
                village: card.dataset.village,
                postal: card.dataset.postal,
            });
            if (addressIdInput) addressIdInput.value = card.dataset.id || '';
            setActive(card);
            collapseFields();
        });
    });

    if (manualCard) {
        manualCard.addEventListener('click', () => {
            fillFields({ recipient: userName, phone: userPhone, address: '', province: '', city: '', district: '', village: '', postal: '' });
            if (addressIdInput) addressIdInput.value = '';
            setActive(manualCard);
            expandFields();
        });
    }

    // Auto-select default address on load
    const defaultCard = wrap.querySelector('.sa-radio-card.is-active[data-saved-address]');
    const gotErrors = form.querySelector('.field-error') !== null;
    if (!gotErrors && defaultCard) {
        fillFields({
            recipient: defaultCard.dataset.recipient,
            phone: defaultCard.dataset.phone,
            address: defaultCard.dataset.address,
            province: defaultCard.dataset.province,
            city: defaultCard.dataset.city,
            district: defaultCard.dataset.district,
            village: defaultCard.dataset.village,
            postal: defaultCard.dataset.postal,
        });
        if (addressIdInput) addressIdInput.value = defaultCard.dataset.id || '';
        collapseFields();
    } else if (gotErrors) {
        expandFields();
    }
})();

/* ── Checkout: phone mask + subtle interactions (all no-JS safe) ── */
(function checkout() {
    const phone = document.querySelector('input[name="phone"]');
    if (phone) {
        phone.addEventListener('input', () => {
            let digits = phone.value.replace(/\D/g, '').slice(0, 13);
            if (digits.startsWith('62')) { digits = '0' + digits.slice(2); }
            const match = digits.match(/^(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,4})$/);
            phone.value = match ? [match[1], match[2], match[3], match[4]].filter(Boolean).join(' ') : digits;
        });
    }

    const form = document.querySelector('form.checkout-form');
    const submitBtn = form && form.querySelector('.checkout-submit');
    if (form && submitBtn) {
        form.addEventListener('submit', () => {
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            const label = submitBtn.querySelector('.checkout-submit-label');
            if (label) label.textContent = 'Memproses…';
        });
    }
})();

/* ── Upload bukti pembayaran: preview, ganti, batal, validasi ── */
(function proofUpload() {
    const form = document.querySelector('.proof-form');
    if (!form) return;

    const input = form.querySelector('.proof-input');
    const field = form.querySelector('.proof-field');
    const empty = form.querySelector('[data-proof-empty]');
    const preview = form.querySelector('[data-proof-preview]');
    const previewImg = form.querySelector('[data-proof-img]');
    const previewName = form.querySelector('[data-proof-name]');
    const actions = form.querySelector('[data-proof-actions]');
    const errorEl = form.querySelector('[data-proof-error]');
    const submit = form.querySelector('[data-proof-submit]');

    const MAX_BYTES = 2 * 1024 * 1024; // 2MB
    const ALLOWED_MIME = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/heic', 'image/heif'];
    const ALLOWED_EXT = ['png', 'jpg', 'jpeg', 'webp', 'heic', 'heif'];

    function showError(msg) {
        if (!errorEl) return;
        errorEl.textContent = msg;
        errorEl.hidden = false;
    }
    function clearError() {
        if (errorEl) { errorEl.hidden = true; errorEl.textContent = ''; }
    }

    function hasAllowedExt(name) {
        const ext = (name || '').split('.').pop().toLowerCase();
        return ALLOWED_EXT.includes(ext);
    }

    function accepts(file) {
        const okType = ALLOWED_MIME.includes(file.type);
        if (!okType && !hasAllowedExt(file.name)) {
            showError('Format tidak didukung. Gunakan PNG, JPG, WebP, atau HEIC.');
            return false;
        }
        if (file.size > MAX_BYTES) {
            showError('Ukuran file melebihi 2MB. Pilih gambar yang lebih kecil.');
            return false;
        }
        return true;
    }

    function showEl(el, on) {
        if (!el) return;
        el.hidden = !on;
        el.style.display = on ? '' : 'none';
    }

    function renderFile(file) {
        clearError();
        if (!accepts(file)) {
            input.value = '';
            return;
        }
        if (preview && previewImg && previewName) {
            try {
                previewImg.src = URL.createObjectURL(file);
                previewName.textContent = file.name;
            } catch (err) {
                previewImg.src = '';
                previewName.textContent = file.name || 'File siap diunggah';
            }
            showEl(preview, true);
        }
        showEl(empty, false);
        showEl(actions, true);
        if (submit) submit.disabled = false;
    }

    function reset() {
        input.value = '';
        clearError();
        showEl(preview, false);
        showEl(actions, false);
        showEl(empty, true);
        if (submit) submit.disabled = true;
        if (previewImg) previewImg.src = '';
        if (previewName) previewName.textContent = '';
    }

    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) { reset(); return; }
        renderFile(file);
    });

    // "Ganti" re-opens the file picker
    const replaceBtn = form.querySelector('[data-proof-replace]');
    if (replaceBtn) {
        replaceBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            input.click();
        });
    }

    // "Batal" clears the selection (and stops the label from reopening the picker)
    const clearBtn = form.querySelector('[data-proof-clear]');
    if (clearBtn) {
        clearBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            reset();
        });
    }

    // Drag & drop support
    if (field && window.FileReader) {
        ;['dragenter', 'dragover'].forEach((ev) =>
            field.addEventListener(ev, (e) => { e.preventDefault(); field.classList.add('is-dragover'); }));
        ;['dragleave', 'drop'].forEach((ev) =>
            field.addEventListener(ev, (e) => { e.preventDefault(); field.classList.remove('is-dragover'); }));
        field.addEventListener('drop', (e) => {
            e.preventDefault();
            const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            renderFile(file);
        });
    }

    // Disable submit while invalid
    form.addEventListener('submit', (e) => {
        if (!input.files || !input.files[0]) {
            e.preventDefault();
            showError('Pilih file bukti pembayaran terlebih dahulu.');
        }
    });
})();

/* ── Copy payment number to clipboard ── */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-copy');
    if (!btn) return;
    const text = btn.dataset.copy;
    navigator.clipboard.writeText(text).then(() => {
        btn.classList.add('copied');
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
        setTimeout(() => {
            btn.classList.remove('copied');
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
        }, 1500);
    });
});

/* ── AJAX add-to-cart (no page reload) + toast feedback ── */
(function ajaxCart() {
    const forms = document.querySelectorAll('form[action="/geprek-geh/cart/add"]');
    if (!forms.length) return;

    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);

    const timers = new Map();
    function showToast(type, message, action) {
        const existing = container.lastElementChild;
        if (existing) container.removeChild(existing);

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML =
            `<span class="toast-icon">` +
            (type === 'success'
                ? `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>`
                : `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4M12 16h.01"/></svg>`) +
            `</span><span class="toast-message">${message}</span>` +
            (action ? `<a class="toast-action" href="#" onclick="event.preventDefault();window.openCartDrawer && window.openCartDrawer();">${action}</a>` : '');
        container.appendChild(toast);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });
        clearTimeout(timers.get(toast));
        timers.set(toast, setTimeout(() => {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 350);
        }, 3400));
    }

    function updateCartCount(count) {
        document.querySelectorAll('[data-cart-count]').forEach((el) => {
            el.textContent = count;
            el.setAttribute('data-cart-count', count);
            if (el.classList.contains('notif-dot')) {
                el.style.display = count > 0 ? '' : 'none';
            }
        });
    }

    forms.forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = form.querySelector('[type="submit"]');
            if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then((res) => res.json().catch(() => null))
                .then((data) => {
                    if (data && data.ok) {
                        updateCartCount(data.count);
                        showToast('success', data.message, 'Lihat Keranjang');
                        if (typeof window.refreshCartDrawer === 'function') {
                            window.refreshCartDrawer().then(() => {
                                setTimeout(() => {
                                    if (typeof window.openCartDrawer === 'function') window.openCartDrawer();
                                }, 600);
                            });
                        }
                    } else {
                        showToast('error', (data && data.message) || 'Gagal menambahkan produk.');
                    }
                })
                .catch(() => {
                    showToast('error', 'Koneksi bermasalah. Mencoba lagi lewat halaman keranjang.');
                    setTimeout(() => form.submit(), 600);
                })
                .finally(() => {
                    if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); }
                });
        });
    });
})();

/* ── Global cart drawer (#cart-drawer) ── */
(function cartDrawer() {
    const drawer = document.getElementById('cart-drawer');
    if (!drawer) return;

    const scrim = drawer.querySelector('.drawer-scrim');
    const panel = drawer.querySelector('.drawer-panel') || drawer.querySelector('aside');
    const openers = document.querySelectorAll('[data-open-drawer]');
    const closers = drawer.querySelectorAll('[data-close-drawer]');

    function open() {
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        if (scrim) scrim.classList.add('is-open');
        if (panel) panel.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        if (window.__lenis) window.__lenis.stop();
        const focusable = drawer.querySelector('button, a, input');
        if (focusable) setTimeout(() => focusable.focus({ preventScroll: true }), 220);
    }
    function close() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (scrim) scrim.classList.remove('is-open');
        if (panel) panel.classList.remove('is-open');
        document.body.style.overflow = '';
        if (window.__lenis) window.__lenis.start();
    }

    openers.forEach((el) => el.addEventListener('click', (e) => { e.preventDefault(); open(); }));
    closers.forEach((el) => el.addEventListener('click', close));
    if (scrim) scrim.addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && drawer.classList.contains('is-open')) close(); });

    window.openCartDrawer = open;
    window.closeCartDrawer = close;

    window.refreshCartDrawer = function () {
        return fetch('/geprek-geh/cart/drawer', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then((r) => r.text())
            .then((html) => {
                const target = drawer.querySelector('.drawer-panel') || drawer.querySelector('aside');
                if (target) target.innerHTML = html;

                target.querySelectorAll('[data-close-drawer]').forEach((el) => {
                    el.addEventListener('click', close);
                });

                updateCartCountFromDrawer();
            });
    };

    function updateCartCountFromDrawer() {
        const title = drawer.querySelector('.drawer-title em');
        if (!title) return;
        const match = title.textContent.match(/\((\d+)\)/);
        const count = match ? parseInt(match[1], 10) : 0;
        document.querySelectorAll('[data-cart-count]').forEach((el) => {
            el.textContent = count;
            el.setAttribute('data-cart-count', count);
            if (el.classList.contains('notif-dot')) {
                el.style.display = count > 0 ? '' : 'none';
            }
        });
    }
})();

/* ── Address drawer (account) ── */
(function addressDrawer() {
    const drawer = document.getElementById('address-drawer');
    const overlay = document.getElementById('address-drawer-overlay');
    if (!drawer) return;

    const body = document.body;
    const openBtn = document.querySelector('[data-open-address-drawer]');

    function open() {
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        if (overlay) overlay.classList.add('is-open');
        body.style.overflow = 'hidden';
        if (window.__lenis) window.__lenis.stop();
        const first = drawer.querySelector('input, textarea, select, button');
        if (first) setTimeout(() => first.focus({ preventScroll: true }), 220);
    }
    function close() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (overlay) overlay.classList.remove('is-open');
        body.style.overflow = '';
        if (window.__lenis) window.__lenis.start();
    }
    window.openAddressDrawer = open;
    window.closeAddressDrawer = close;

    if (openBtn) openBtn.addEventListener('click', open);
    document.querySelectorAll('[data-close-address-drawer]').forEach((btn) => btn.addEventListener('click', close));
    if (overlay) overlay.addEventListener('click', close);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });

    // auto-open when editing / after validation error (server set flag)
    if (window.__ADDRESS_DRAWER_OPEN__ === true) {
        open();
    }
})();

/* ── Checkout: tampilkan instruksi bayar sesuai metode yang dipilih ── */
(function paymentInfo() {
    const wrap = document.querySelector('[data-pay-info-wrap]');
    if (!wrap) return;
    const radios = document.querySelectorAll('input[name="payment_method"]');
    if (!radios.length) return;

    function apply(value) {
        wrap.querySelectorAll('.pay-info').forEach((el) => {
            el.classList.toggle('is-visible', el.dataset.payInfo === value);
        });
    }
    radios.forEach((radio) => {
        radio.addEventListener('change', () => {
            if (radio.checked) apply(radio.value);
        });
    });
    const checked = wrap.closest('form') && wrap.closest('form').querySelector('input[name="payment_method"]:checked');
    apply((checked && checked.value) || radios[0].value);
})();

/* ── Admin: tampilkan field resi / alasan batal sesuai status terpilih ── */
(function statusForm() {
    const form = document.querySelector('form[data-status-form]');
    if (!form) return;
    const select = form.querySelector('[data-status-select]');
    const extras = form.querySelectorAll('[data-status-extra]');

    function apply() {
        const value = select.value;
        extras.forEach((el) => {
            el.hidden = el.dataset.statusExtra !== value;
            const input = el.querySelector('input, textarea');
            if (input) input.required = el.dataset.statusExtra === value;
        });
    }
    select.addEventListener('change', apply);
    apply();
})();

/* ── Salin teks ke clipboard (data-copy="#selector") ── */
(function copyText() {
    const btns = document.querySelectorAll('[data-copy]');
    btns.forEach((btn) => {
        btn.addEventListener('click', async () => {
            const el = document.querySelector(btn.dataset.copy);
            const text = el ? el.textContent.trim() : btn.dataset.copy;
            try {
                await navigator.clipboard.writeText(text);
                const prev = btn.textContent;
                btn.textContent = 'Tersalin ✓';
                setTimeout(() => { btn.textContent = prev; }, 1600);
            } catch (_) {
                btn.textContent = 'Gagal salin';
                setTimeout(() => { btn.textContent = prev ?? ''; }, 1600);
            }
        });
    });
})();

/* ── Format input kode 2FA: angka saja, max 6 digit pada kolom TOTP ── */
(function twofaInputs() {
    document.querySelectorAll('input.twofa-code-input').forEach((input) => {
        const isTotp = (input.maxLength || 0) === 6;
        input.addEventListener('input', () => {
            let v = input.value.replace(/[^0-9a-zA-Z]/g, '').toUpperCase();
            if (isTotp) v = v.replace(/[^0-9]/g, '').slice(0, 6);
            input.value = v;
        });
    });
})();

/* ── Review star rating input ── */
(function reviewStars() {
    const wrap = document.querySelector('[data-rating-input]');
    if (!wrap) return;
    const input = wrap.querySelector('input[name="rating"]');
    const stars = wrap.querySelectorAll('.review-star-btn');

    function update(val) {
        input.value = val;
        stars.forEach((s) => {
            const v = parseInt(s.dataset.star);
            s.classList.toggle('is-active', v <= val);
            const path = s.querySelector('svg path');
            if (path) path.setAttribute('fill', v <= val ? '#D43E1B' : 'none');
        });
    }

    stars.forEach((s) => {
        s.addEventListener('click', () => update(parseInt(s.dataset.star)));
        s.addEventListener('mouseenter', () => {
            const v = parseInt(s.dataset.star);
            stars.forEach((x) => {
                const sv = parseInt(x.dataset.star);
                const p = x.querySelector('svg path');
                if (p) p.setAttribute('fill', sv <= v ? '#D43E1B' : 'none');
            });
        });
    });

    wrap.addEventListener('mouseleave', () => update(parseInt(input.value) || 0));
    update(parseInt(input.value) || 0);
})();

/* ── Global form submit loading state (all forms except AJAX cart & reg-submit) ── */
(function formLoading() {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.matches('form[action="/geprek-geh/cart/add"]')) return;
        if (form.hasAttribute('data-no-loading')) return;
        const btn = form.querySelector('[type="submit"]');
        if (btn && !btn.disabled && !btn.classList.contains('reg-submit')) {
            btn.disabled = true;
            btn.classList.add('is-loading');
        }
    });
})();
