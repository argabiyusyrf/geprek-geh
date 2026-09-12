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
    updatePdSubtotal();
}

/* ── Live subtotal (product detail, transparent pricing) ── */
function formatIdr(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

function updatePdSubtotal() {
    const buy = document.querySelector('.pd-buy[data-price]');
    const input = document.getElementById('qty');
    const label = document.getElementById('pdQtyLabel');
    const subtotal = document.getElementById('pdSubtotal');
    if (!buy || !input || !label || !subtotal) return;
    const qty = parseInt(input.value, 10) || 1;
    const price = parseInt(buy.dataset.price, 10) || 0;
    label.textContent = qty + '\u00d7 porsi';
    subtotal.textContent = formatIdr(price * qty);
}

(function pdSubtotalListeners() {
    const input = document.getElementById('qty');
    if (!input) return;
    updatePdSubtotal();
    ['input', 'change'].forEach((ev) => input.addEventListener(ev, updatePdSubtotal));
})();

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
    if (!form) return;

    form.addEventListener('submit', () => {
        form.querySelectorAll('.checkout-submit').forEach((btn) => {
            btn.disabled = true;
            btn.classList.add('is-loading');
            const label = btn.querySelector('.checkout-submit-label');
            if (label) label.textContent = 'Memproses…';
        });
    });
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
    const bankSel = form.querySelector('select[name="payment_bank"]');
    form.addEventListener('submit', (e) => {
        if (bankSel && !bankSel.value) {
            e.preventDefault();
            showError('Pilih bank atau e-wallet terlebih dahulu.');
            return;
        }
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
    const form = drawer.querySelector('form');
    const titleEl = drawer.querySelector('#drawer-title');
    const formActionInput = form ? form.querySelector('[name="_form_action"]') : null;

    // Default form action (for add)
    const addAction = '/geprek-geh/account/addresses';

    function resetForm() {
        if (!form) return;
        form.reset();
        form.setAttribute('action', addAction);
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.field-error').forEach(el => el.remove());
        if (titleEl) titleEl.textContent = 'Tambah Alamat';
        // remove any leftover hidden edit-id input
        const old = form.querySelector('input[name="edit_id"]');
        if (old) old.remove();
    }

    function open() {
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        if (overlay) overlay.classList.add('is-open');
        body.style.overflow = 'hidden';
        if (window.__lenis) window.__lenis.stop();
        const first = drawer.querySelector('input, textarea, select, button[type="submit"]');
        if (first) setTimeout(() => first.focus({ preventScroll: true }), 220);
    }

    function close() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (overlay) overlay.classList.remove('is-open');
        body.style.overflow = '';
        if (window.__lenis) window.__lenis.start();
        // reset form + button state after close animation finishes
        setTimeout(() => {
            resetForm();
            const btn = drawer.querySelector('button[type="submit"]');
            if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
        }, 350);
    }

    function openForAdd() {
        resetForm();
        open();
    }

    function openForEdit(data) {
        resetForm();
        // set form action to edit endpoint
        form.setAttribute('action', '/geprek-geh/account/addresses/' + data.id);
        if (titleEl) titleEl.textContent = 'Edit Alamat';
        // prefill fields
        const fields = ['label', 'recipient_name', 'phone', 'province', 'city', 'district', 'village', 'postal_code', 'address', 'notes'];
        fields.forEach(name => {
            const input = form.querySelector('[name="' + name + '"]');
            if (input && data[name] !== undefined) input.value = data[name] || '';
        });
        const defCheckbox = form.querySelector('[name="is_default"]');
        if (defCheckbox) defCheckbox.checked = parseInt(data.is_default) === 1;
        open();
    }

    window.openAddressDrawer = openForAdd;
    window.closeAddressDrawer = close;

    if (openBtn) openBtn.addEventListener('click', openForAdd);

    // edit buttons: instant client-side prefill
    drawer.querySelectorAll('[data-close-address-drawer]').forEach((btn) => btn.addEventListener('click', close));
    document.querySelectorAll('[data-edit-address]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                const data = JSON.parse(btn.dataset.editAddress);
                openForEdit(data);
            } catch (_) {
                // fallback: use server redirect
                const form = btn.closest('form');
                if (form) form.submit();
            }
        });
    });

    if (overlay) overlay.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });

    // loading state on submit
    if (form) {
        form.addEventListener('submit', () => {
            const btn = drawer.querySelector('button[type="submit"]');
            if (btn && !btn.classList.contains('is-loading')) {
                btn.classList.add('is-loading');
                btn.disabled = true;
            }
        });
    }

    // auto-open when editing / after validation error (server set flag)
    if (window.__ADDRESS_DRAWER_OPEN__ === true) {
        const editData = window.__ADDRESS_EDIT_DATA__;
        if (editData) {
            openForEdit(editData);
        } else {
            open();
        }
    }
})();

/* ── Product drawer (admin CRUD) ── */
(function productDrawer() {
    const drawer = document.getElementById('product-drawer');
    const overlay = document.getElementById('product-drawer-overlay');
    if (!drawer) return;

    const body = document.body;
    const form = drawer.querySelector('form#product-form');
    const titleEl = drawer.querySelector('#product-drawer-title');
    const submitBtn = drawer.querySelector('#product-submit');
    const nameInput = drawer.querySelector('#product-name');
    const slugPreview = drawer.querySelector('#product-slug-preview');
    const priceInput = drawer.querySelector('[name="price"]');
    const stockInput = drawer.querySelector('[name="stock"]');
    const stockRange = drawer.querySelector('[data-stock-range]');
    const descInput = drawer.querySelector('[name="description"]');
    const editor = drawer.querySelector('[data-editor]');
    const editorToolbar = drawer.querySelector('.editor-toolbar');
    const imgField = drawer.querySelector('[data-pimg-field]');
    const imgInput = drawer.querySelector('[data-pimg-input]');
    const imgEmpty = drawer.querySelector('[data-pimg-empty]');
    const imgPreview = drawer.querySelector('[data-pimg-preview]');
    const imgImg = drawer.querySelector('[data-pimg-img]');
    const imgName = drawer.querySelector('[data-pimg-name]');
    const imgNote = drawer.querySelector('[data-pimg-note]');
    const imgActions = drawer.querySelector('[data-pimg-actions]');
    const imgReplace = drawer.querySelector('[data-pimg-replace]');
    const imgClear = drawer.querySelector('[data-pimg-clear]');
    const imgError = drawer.querySelector('[data-pimg-error]');

    const addAction = '/geprek-geh/admin/products';
    const categories = window.__gehCategories || [];
    const products = window.__gehProducts || [];
    const PRODUCT_IMG_BASE = '/geprek-geh/assets/uploads/products/';
    const IMG_MAX = 5 * 1024 * 1024;
    const IMG_ALLOWED = ['image/png', 'image/jpeg', 'image/webp'];
    const IMG_EXT = ['png', 'jpg', 'jpeg', 'webp'];
    const STOCK_MAX = 500;
    let currentId = null;
    let savedImage = null;

    // ── helpers ────────────────────────────────────────────────
    function slugify(v) {
        return String(v || '').toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-').replace(/-+/g, '-');
    }
    function digits(v) { return String(v || '').replace(/\D/g, ''); }
    function fmtPrice(v) { return digits(v).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    function show(el, on) { if (el) el.hidden = !on; }

    function fillCategorySelect(selectedId) {
        const sel = form.querySelector('[name="category_id"]');
        if (!sel) return;
        sel.innerHTML = '<option value="">Pilih</option>' + categories.map((c) =>
            '<option value="' + c.id + '">' + c.name + '</option>'
        ).join('');
        if (selectedId) sel.value = String(selectedId);
    }

    // ── stok: slider + stepper sinkron ──────────────────────
    function paintRange() {
        if (!stockRange) return;
        const pct = (parseInt(stockRange.value, 10) / STOCK_MAX) * 100;
        const color = 'var(--accent)';
        stockRange.style.background =
            'linear-gradient(to right, ' + color + ' 0%, ' + color + ' ' + pct +
            '%, rgba(29,26,21,0.08) ' + pct + '%)';
    }
    function syncStock() {
        const v = Math.max(0, parseInt(stockInput.value, 10) || 0);
        stockInput.value = v;
        if (stockRange) stockRange.value = Math.min(v, STOCK_MAX);
        paintRange();
    }

    // ── gambar: kosong / tersimpan / file baru ──────────────
    function imgAccept(file) {
        const okMime = IMG_ALLOWED.includes(file.type);
        const ext = (file.name || '').split('.').pop().toLowerCase();
        if (!okMime && !IMG_EXT.includes(ext)) {
            setImgError('Format tidak didukung. Gunakan PNG, JPG, atau WebP.');
            return false;
        }
        if (file.size > IMG_MAX) {
            setImgError('Ukuran file melebihi 5MB. Pilih gambar yang lebih kecil.');
            return false;
        }
        return true;
    }
    function setImgError(msg) {
        if (!imgError) return;
        imgError.textContent = msg;
        imgError.hidden = !msg;
    }
    function renderImgEmpty() {
        show(imgEmpty, true);
        show(imgPreview, false);
        show(imgActions, false);
    }
    function renderImgSaved() {
        if (!savedImage) { renderImgEmpty(); return; }
        setImgError('');
        show(imgEmpty, false);
        show(imgPreview, true);
        show(imgActions, true);
        if (imgImg) imgImg.src = PRODUCT_IMG_BASE + savedImage;
        if (imgName) imgName.textContent = savedImage;
        if (imgNote) imgNote.textContent = 'Tersimpan · pilih “Ganti” untuk memperbarui';
        show(imgClear, false);
    }
    function renderImgFile(file) {
        setImgError('');
        show(imgEmpty, false);
        show(imgPreview, true);
        show(imgActions, true);
        if (imgImg && file) {
            try { imgImg.src = URL.createObjectURL(file); }
            catch (_) { imgImg.src = ''; }
        }
        if (imgName) imgName.textContent = file.name;
        if (imgNote) imgNote.textContent = 'Gambar baru · mulai terpakai saat menyimpan';
        show(imgClear, true);
    }

    // ── editor B/I/U ─────────────────────────────────────────
    function refreshEditorState() {
        if (!editorToolbar) return;
        editorToolbar.querySelectorAll('.editor-btn').forEach((btn) => {
            const cmd = btn.dataset.ed;
            btn.classList.toggle('is-active', document.queryCommandState(cmd));
        });
    }
    function editorToValue() {
        if (!editor) return '';
        const html = editor.innerHTML || '';
        return html
            .replace(/<div><br><\/div>/gi, '\n')
            .replace(/<div>\s*<\/div>/gi, '\n')
            .replace(/<br\s*\/?>/gi, '\n')
            .replace(/<\/div>/gi, '')
            .replace(/<div>/gi, '\n')
            .trim();
    }

    // ── lifecycle ────────────────────────────────────────────
    function resetForm() {
        if (!form) return;
        form.reset();
        form.setAttribute('action', addAction);
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        form.querySelectorAll('.field-error').forEach((el) => el.remove());
        currentId = null;
        savedImage = null;
        if (titleEl) titleEl.textContent = 'Tambah Produk';
        if (submitBtn) submitBtn.textContent = 'Tambah Produk';
        if (slugPreview) slugPreview.textContent = '/nama-produk';
        if (priceInput) priceInput.value = '';
        syncStock();
        if (descInput) descInput.value = '';
        if (editor) editor.innerHTML = '';
        if (imgInput) imgInput.value = '';
        setImgError('');
        renderImgEmpty();
        fillCategorySelect(0);
    }

    function open() {
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        if (overlay) overlay.classList.add('is-open');
        body.style.overflow = 'hidden';
        if (window.__lenis) window.__lenis.stop();
        if (priceInput) priceInput.value = fmtPrice(priceInput.value);
        syncStock();
        if (editor) editor.innerHTML = descInput && descInput.value ? descInput.value.replace(/\n/g, '<br>') : '';
        if (savedImage) renderImgSaved(); else renderImgEmpty();
        const first = drawer.querySelector('input, textarea, select, button[type="submit"]');
        if (first) setTimeout(() => first.focus({ preventScroll: true }), 220);
    }

    function close() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (overlay) overlay.classList.remove('is-open');
        body.style.overflow = '';
        if (window.__lenis) window.__lenis.start();
        setTimeout(() => {
            resetForm();
            if (submitBtn) { submitBtn.classList.remove('is-loading'); submitBtn.disabled = false; }
        }, 350);
    }

    function openForAdd() {
        resetForm();
        open();
    }

    function setEditState(id) {
        currentId = id;
        form.setAttribute('action', '/geprek-geh/admin/products/' + id);
        if (titleEl) titleEl.textContent = 'Edit Produk';
        if (submitBtn) submitBtn.textContent = 'Simpan Perubahan';
    }

    function openForEdit(data) {
        resetForm();
        currentId = data ? data.id : null;
        nameInput.value = data.name || '';
        if (slugPreview) slugPreview.textContent = '/' + (data.slug || slugify(data.name));
        fillCategorySelect(data.category_id);
        if (priceInput) priceInput.value = data.price || '';
        if (stockInput) stockInput.value = data.stock || 0;
        if (descInput) descInput.value = data.description || '';
        if (editor) editor.innerHTML = data.description ? String(data.description).replace(/\n/g, '<br>') : '';
        savedImage = data.image || null;
        const active = form.querySelector('[name="is_active"]');
        const featured = form.querySelector('[name="is_featured"]');
        if (active) active.checked = parseInt(data.is_active) === 1;
        if (featured) featured.checked = parseInt(data.is_featured) === 1;
        setEditState(data.id);
        open();
    }

    window.openProductDrawer = openForAdd;
    window.closeProductDrawer = close;

    // name → live slug preview
    if (nameInput && slugPreview) {
        nameInput.addEventListener('input', () => {
            const s = slugify(nameInput.value);
            slugPreview.textContent = s ? '/' + s : '/nama-produk';
        });
    }

    // harga: hanya angka + format titik ribuan
    if (priceInput) {
        priceInput.addEventListener('input', () => {
            priceInput.value = fmtPrice(priceInput.value);
        });
    }

    // stok: slider ⇄ stepper
    if (stockInput && stockRange) {
        stockRange.addEventListener('input', () => {
            stockInput.value = Math.min(Math.max(0, parseInt(stockRange.value, 10) || 0), STOCK_MAX);
            paintRange();
        });
        stockInput.addEventListener('input', () => {
            const v = Math.max(0, parseInt(digits(stockInput.value), 10) || 0);
            stockInput.value = v;
            stockRange.value = Math.min(v, STOCK_MAX);
            paintRange();
        });
    }
    drawer.querySelectorAll('[data-stock-step]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const step = parseInt(btn.dataset.stockStep, 10) || 0;
            const v = Math.max(0, (parseInt(stockInput.value, 10) || 0) + step);
            stockInput.value = v;
            if (stockRange) stockRange.value = Math.min(v, STOCK_MAX);
            paintRange();
            stockInput.focus();
        });
    });

    // gambar: preview / ganti / batal / drag & drop
    function handleImgFile(file) {
        if (!file) return;
        if (!imgAccept(file)) {
            imgInput.value = '';
            if (savedImage) renderImgSaved(); else renderImgEmpty();
            return;
        }
        renderImgFile(file);
    }
    if (imgInput) {
        imgInput.addEventListener('change', () => {
            handleImgFile(imgInput.files && imgInput.files[0]);
        });
    }
    if (imgReplace) {
        imgReplace.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            imgInput.click();
        });
    }
    if (imgClear) {
        imgClear.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            imgInput.value = '';
            setImgError('');
            if (savedImage) renderImgSaved(); else renderImgEmpty();
        });
    }
    if (imgField && window.FileReader) {
        ['dragenter', 'dragover'].forEach((ev) =>
            imgField.addEventListener(ev, (e) => { e.preventDefault(); imgField.classList.add('is-dragover'); }));
        ['dragleave', 'drop'].forEach((ev) =>
            imgField.addEventListener(ev, (e) => { e.preventDefault(); imgField.classList.remove('is-dragover'); }));
        imgField.addEventListener('drop', (e) => {
            e.preventDefault();
            const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            imgInput.files = dt.files;
            handleImgFile(file);
        });
    }

    // editor: toolbar B/I/U
    drawer.querySelectorAll('.editor-btn').forEach((btn) => {
        btn.addEventListener('mousedown', (e) => e.preventDefault());
        btn.addEventListener('click', () => {
            editor.focus();
            document.execCommand(btn.dataset.ed, false, null);
            refreshEditorState();
        });
    });
    if (editor) {
        ['keyup', 'mouseup', 'input'].forEach((ev) => editor.addEventListener(ev, refreshEditorState));
    }
    document.addEventListener('selectionchange', () => {
        if (editor && editorToolbar && editor.contains(document.activeElement)) refreshEditorState();
    });
    if (editor && editor.firstChild && editor.firstChild.nodeName === '#text' && editor.getAttribute('data-placeholder')) {
        // placeholder via CSS :empty — text nodes only appear after editing
    }

    // form submit → normalisasi editor, harga, tutup drawer
    if (form) {
        form.addEventListener('submit', () => {
            if (descInput) descInput.value = editorToValue();
            if (priceInput) priceInput.value = digits(priceInput.value);
            if (submitBtn && !submitBtn.classList.contains('is-loading')) {
                submitBtn.classList.add('is-loading');
                submitBtn.disabled = true;
            }
        });
    }

    // openers / closers
    document.querySelectorAll('[data-open-product-drawer]').forEach((b) => b.addEventListener('click', openForAdd));
    document.querySelectorAll('[data-close-product-drawer]').forEach((b) => b.addEventListener('click', close));
    document.querySelectorAll('[data-edit-product]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                openForEdit(JSON.parse(btn.dataset.editProduct));
            } catch (_) {
                const editId = btn.getAttribute('data-edit-product');
                window.location.href = '/geprek-geh/admin/products?edit=' + encodeURIComponent(editId);
            }
        });
    });

    if (overlay) overlay.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });

    // auto-open via ?create=1 / ?edit={id} (+ error-restore: keep server-rendered values)
    const qs = new URLSearchParams(window.location.search);
    const drawerError = window.__gehDrawerError || null;
    if (qs.get('create')) {
        if (drawerError && drawerError.mode === 'create') open();       // values already rendered
        else openForAdd();
    } else if (qs.get('edit')) {
        const targetId = parseInt(qs.get('edit'), 10);
        if (drawerError && drawerError.mode === 'edit' && drawerError.id === targetId) {
            const found = products.find((p) => parseInt(p.id) === targetId);
            if (found) savedImage = found.image || null;
            setEditState(targetId);                                    // keep server-rendered values+errors
            open();
        } else {
            const found = products.find((p) => parseInt(p.id) === targetId);
            if (found) openForEdit(found);
        }
    }
})();

/* ── Admin Kategori: drawer tambah/edit ── */
(function categoryDrawer() {
    const drawer = document.getElementById('category-drawer');
    if (!drawer) return;
    const overlay = document.getElementById('category-drawer-overlay');
    const body = document.body;
    const form = drawer.querySelector('form#category-form');
    const titleEl = drawer.querySelector('#category-drawer-title');
    const submitBtn = drawer.querySelector('#category-submit');
    const items = window.__gehCategories || [];

    function resetForm() {
        form.reset();
        form.setAttribute('action', '/geprek-geh/admin/categories');
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        form.querySelectorAll('.field-error').forEach((el) => el.remove());
        if (titleEl) titleEl.textContent = 'Tambah Kategori';
        if (submitBtn) submitBtn.textContent = 'Tambah Kategori';
    }

    function open() {
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        if (overlay) overlay.classList.add('is-open');
        body.style.overflow = 'hidden';
        if (window.__lenis) window.__lenis.stop();
        const first = drawer.querySelector('input, textarea, select, button[type="submit"]');
        if (first) setTimeout(() => first.focus({ preventScroll: true }), 220);
    }

    function close() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (overlay) overlay.classList.remove('is-open');
        body.style.overflow = '';
        if (window.__lenis) window.__lenis.start();
        setTimeout(() => {
            resetForm();
            if (submitBtn) { submitBtn.classList.remove('is-loading'); submitBtn.disabled = false; }
        }, 350);
    }

    function openForAdd() { resetForm(); open(); }

    function setEditState(id) {
        form.setAttribute('action', '/geprek-geh/admin/categories/' + id);
        if (titleEl) titleEl.textContent = 'Edit Kategori';
        if (submitBtn) submitBtn.textContent = 'Simpan Perubahan';
    }

    function openForEdit(data) {
        resetForm();
        form.querySelector('[name="name"]').value = data.name || '';
        form.querySelector('[name="description"]').value = data.description || '';
        form.querySelector('[name="sort_order"]').value = data.sort_order || 0;
        setEditState(data.id);
        open();
    }

    window.openCategoryDrawer = openForAdd;
    window.closeCategoryDrawer = close;
    document.querySelectorAll('[data-open-category-drawer]').forEach((b) => b.addEventListener('click', openForAdd));
    document.querySelectorAll('[data-close-category-drawer]').forEach((b) => b.addEventListener('click', close));
    document.querySelectorAll('[data-edit-category]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openForEdit(JSON.parse(btn.dataset.editCategory));
        });
    });
    if (overlay) overlay.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });
    if (form) form.addEventListener('submit', () => {
        if (submitBtn && !submitBtn.classList.contains('is-loading')) {
            submitBtn.classList.add('is-loading');
            submitBtn.disabled = true;
        }
    });

    const qs = new URLSearchParams(window.location.search);
    const drawerError = window.__gehDrawerError || null;
    if (qs.get('create')) {
        if (drawerError && drawerError.mode === 'create') open();
        else openForAdd();
    } else if (qs.get('edit')) {
        const targetId = parseInt(qs.get('edit'), 10);
        if (drawerError && drawerError.mode === 'edit' && drawerError.id === targetId) {
            setEditState(targetId);
            open();
        } else {
            const found = items.find((c) => parseInt(c.id) === targetId);
            if (found) openForEdit(found);
        }
    }
})();

/* ── Admin Kode Promo: drawer tambah/edit ── */
(function promoDrawer() {
    const drawer = document.getElementById('promo-drawer');
    if (!drawer) return;
    const overlay = document.getElementById('promo-drawer-overlay');
    const body = document.body;
    const form = drawer.querySelector('form#promo-form');
    const titleEl = drawer.querySelector('#promo-drawer-title');
    const submitBtn = drawer.querySelector('#promo-submit');
    const items = window.__gehPromos || [];

    function resetForm() {
        form.reset();
        form.setAttribute('action', '/geprek-geh/admin/promos');
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        form.querySelectorAll('.field-error').forEach((el) => el.remove());
        if (titleEl) titleEl.textContent = 'Buat Kode Promo';
        if (submitBtn) submitBtn.textContent = 'Buat Kode';
    }

    function open() {
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        if (overlay) overlay.classList.add('is-open');
        body.style.overflow = 'hidden';
        if (window.__lenis) window.__lenis.stop();
        const first = drawer.querySelector('input, textarea, select, button[type="submit"]');
        if (first) setTimeout(() => first.focus({ preventScroll: true }), 220);
    }

    function close() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (overlay) overlay.classList.remove('is-open');
        body.style.overflow = '';
        if (window.__lenis) window.__lenis.start();
        setTimeout(() => {
            resetForm();
            if (submitBtn) { submitBtn.classList.remove('is-loading'); submitBtn.disabled = false; }
        }, 350);
    }

    function openForAdd() { resetForm(); open(); }

    function setEditState(id) {
        form.setAttribute('action', '/geprek-geh/admin/promos/' + id);
        if (titleEl) titleEl.textContent = 'Edit Kode Promo';
        if (submitBtn) submitBtn.textContent = 'Simpan Perubahan';
    }

    function openForEdit(data) {
        resetForm();
        form.querySelector('[name="code"]').value = data.code || '';
        const typeSel = form.querySelector('[name="type"]');
        if (typeSel) typeSel.value = data.type || 'percentage';
        form.querySelector('[name="value"]').value = data.value;
        form.querySelector('[name="min_order"]').value = data.min_order || 0;
        form.querySelector('[name="max_uses"]').value = data.max_uses || '';
        form.querySelector('[name="starts_at"]').value = data.starts_at || '';
        form.querySelector('[name="expires_at"]').value = data.expires_at || '';
        const active = form.querySelector('[name="is_active"]');
        if (active) active.checked = parseInt(data.is_active) === 1;
        setEditState(data.id);
        open();
    }

    window.openPromoDrawer = openForAdd;
    window.closePromoDrawer = close;
    document.querySelectorAll('[data-open-promo-drawer]').forEach((b) => b.addEventListener('click', openForAdd));
    document.querySelectorAll('[data-close-promo-drawer]').forEach((b) => b.addEventListener('click', close));
    document.querySelectorAll('[data-edit-promo]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openForEdit(JSON.parse(btn.dataset.editPromo));
        });
    });
    if (overlay) overlay.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });
    if (form) form.addEventListener('submit', () => {
        if (submitBtn && !submitBtn.classList.contains('is-loading')) {
            submitBtn.classList.add('is-loading');
            submitBtn.disabled = true;
        }
    });

    const qs = new URLSearchParams(window.location.search);
    const drawerError = window.__gehDrawerError || null;
    if (qs.get('create')) {
        if (drawerError && drawerError.mode === 'create') open();
        else openForAdd();
    } else if (qs.get('edit')) {
        const targetId = parseInt(qs.get('edit'), 10);
        if (drawerError && drawerError.mode === 'edit' && drawerError.id === targetId) {
            setEditState(targetId);
            open();
        } else {
            const found = items.find((p) => parseInt(p.id) === targetId);
            if (found) openForEdit(found);
        }
    }
})();

/* ── Admin Pengguna: drawer tambah/edit ── */
(function userDrawer() {
    const drawer = document.getElementById('user-drawer');
    if (!drawer) return;
    const overlay = document.getElementById('user-drawer-overlay');
    const body = document.body;
    const form = drawer.querySelector('form#user-form');
    const titleEl = drawer.querySelector('#user-drawer-title');
    const submitBtn = drawer.querySelector('#user-submit');
    const passInput = drawer.querySelector('[name="password"]');
    const passHint = drawer.querySelector('#user-pass-hint');
    const passReq = drawer.querySelector('#user-pass-required');
    const items = window.__gehUsers || [];

    function setPassMode(edit) {
        if (!passInput) return;
        if (edit) {
            passInput.removeAttribute('required');
            if (passHint) passHint.textContent = 'Kosongkan agar password tidak berubah.';
            if (passReq) passReq.style.visibility = 'hidden';
        } else {
            passInput.setAttribute('required', 'required');
            if (passHint) passHint.textContent = '';
            if (passReq) passReq.style.visibility = 'visible';
        }
    }

    function resetForm() {
        form.reset();
        form.setAttribute('action', '/geprek-geh/admin/users');
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        form.querySelectorAll('.field-error').forEach((el) => el.remove());
        if (titleEl) titleEl.textContent = 'Tambah Pengguna';
        if (submitBtn) submitBtn.textContent = 'Tambah Pengguna';
        setPassMode(false);
    }

    function open() {
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        if (overlay) overlay.classList.add('is-open');
        body.style.overflow = 'hidden';
        if (window.__lenis) window.__lenis.stop();
        const first = drawer.querySelector('input, textarea, select, button[type="submit"]');
        if (first) setTimeout(() => first.focus({ preventScroll: true }), 220);
    }

    function close() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        if (overlay) overlay.classList.remove('is-open');
        body.style.overflow = '';
        if (window.__lenis) window.__lenis.start();
        setTimeout(() => {
            resetForm();
            if (submitBtn) { submitBtn.classList.remove('is-loading'); submitBtn.disabled = false; }
        }, 350);
    }

    function openForAdd() { resetForm(); open(); }

    function setEditState(id) {
        form.setAttribute('action', '/geprek-geh/admin/users/' + id);
        if (titleEl) titleEl.textContent = 'Edit Pengguna';
        if (submitBtn) submitBtn.textContent = 'Simpan Perubahan';
        setPassMode(true);
    }

    function openForEdit(data) {
        resetForm();
        form.querySelector('[name="name"]').value = data.name || '';
        form.querySelector('[name="email"]').value = data.email || '';
        form.querySelector('[name="phone"]').value = data.phone || '';
        const roleSel = form.querySelector('[name="role"]');
        if (roleSel) roleSel.value = data.role || 'customer';
        const notify = form.querySelector('[name="notify_email"]');
        if (notify) notify.checked = parseInt(data.notify_email) === 1;
        setEditState(data.id);
        open();
    }

    window.openUserDrawer = openForAdd;
    window.closeUserDrawer = close;
    document.querySelectorAll('[data-open-user-drawer]').forEach((b) => b.addEventListener('click', openForAdd));
    document.querySelectorAll('[data-close-user-drawer]').forEach((b) => b.addEventListener('click', close));
    document.querySelectorAll('[data-edit-user]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openForEdit(JSON.parse(btn.dataset.editUser));
        });
    });
    if (overlay) overlay.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });
    if (form) form.addEventListener('submit', () => {
        if (submitBtn && !submitBtn.classList.contains('is-loading')) {
            submitBtn.classList.add('is-loading');
            submitBtn.disabled = true;
        }
    });

    const qs = new URLSearchParams(window.location.search);
    const drawerError = window.__gehDrawerError || null;
    if (qs.get('create')) {
        if (drawerError && drawerError.mode === 'create') {
            open();
            setPassMode(false);
        } else {
            openForAdd();
        }
    } else if (qs.get('edit')) {
        const targetId = parseInt(qs.get('edit'), 10);
        if (drawerError && drawerError.mode === 'edit' && drawerError.id === targetId) {
            setEditState(targetId);
            open();
        } else {
            const found = items.find((u) => parseInt(u.id) === targetId);
            if (found) openForEdit(found);
        }
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
    // execCommand fallback: works on plain HTTP where navigator.clipboard is unavailable
    const legacyCopy = (text) => {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        ta.setSelectionRange(0, text.length);
        let ok = false;
        try { ok = document.execCommand('copy'); } catch (_) { ok = false; }
        ta.remove();
        return ok;
    };
    btns.forEach((btn) => {
        btn.addEventListener('click', async () => {
            const el = document.querySelector(btn.dataset.copy);
            const text = el ? el.textContent.trim() : btn.dataset.copy;
            const prev = btn.textContent;
            let ok = false;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try { await navigator.clipboard.writeText(text); ok = true; } catch (_) { ok = false; }
            }
            if (!ok) ok = legacyCopy(text);
            btn.textContent = ok ? 'Tersalin ✓' : 'Gagal salin';
            setTimeout(() => { btn.textContent = prev; }, 1600);
        });
    });
})();

/* ── 2FA QR: rendered locally via vendored qrcode-generator (no external API) ── */
(function twofaQrLocal() {
    const canvas = document.querySelector('.twofa-qr canvas[data-twofa-uri]');
    if (!canvas) return;
    const wrap = canvas.closest('.twofa-qr');
    const fallback = wrap && wrap.querySelector('.twofa-qr-fallback');
    let ok = false;
    try {
        const qr = qrcode(0, 'M');
        qr.addData(canvas.dataset.twofaUri);
        qr.make();
        const count = qr.getModuleCount();
        const border = 4;
        const size = count + border * 2;
        const scale = Math.max(1, Math.floor(190 / size));
        canvas.width = canvas.height = size * scale;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#131210';
        for (let r = 0; r < count; r++) {
            for (let c = 0; c < count; c++) {
                if (qr.isDark(r, c)) {
                    ctx.fillRect((c + border) * scale, (r + border) * scale, scale, scale);
                }
            }
        }
        canvas.style.width = (size * scale) + 'px';
        canvas.style.height = (size * scale) + 'px';
        ok = true;
    } catch (_) { ok = false; }
    if (!ok && fallback) {
        canvas.remove();
        fallback.hidden = false;
    }
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

/* ── Custom dropdown — "Urutkan" (menu & orders) ── */
(function sortDropdown() {
    const boxes = document.querySelectorAll('[data-dropdown]');
    if (!boxes.length) return;

    const close = (box) => {
        box.classList.remove('is-open');
        const t = box.querySelector('[data-dropdown-trigger]');
        if (t) t.setAttribute('aria-expanded', 'false');
    };
    const closeAll = () => boxes.forEach(close);

    boxes.forEach((box) => {
        const trigger = box.querySelector('[data-dropdown-trigger]');
        const menu = box.querySelector('[data-dropdown-menu]');
        const label = box.querySelector('[data-dropdown-label]');
        const select = box.querySelector('[data-dropdown-select]');
        const items = box.querySelectorAll('.menu-dropdown-item');
        if (!trigger || !menu || !label || !select) return;

        const apply = (value) => {
            const item = Array.from(items).find((i) => i.dataset.value === value);
            if (!item) return;
            label.textContent = item.textContent;
            items.forEach((i) => {
                i.classList.toggle('is-selected', i.dataset.value === value);
                i.setAttribute('aria-selected', i.dataset.value === value ? 'true' : 'false');
            });
        };

        apply(select.value);

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = box.classList.contains('is-open');
            closeAll();
            if (!isOpen) {
                box.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        items.forEach((item) => {
            item.addEventListener('click', () => {
                const v = item.dataset.value;
                select.value = v;
                apply(v);
                close(box);
                if (box.hasAttribute('data-form-submit')) {
                    const form = box.closest('form');
                    if (form) form.submit();
                }
            });
        });

        select.addEventListener('change', () => apply(select.value));
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-dropdown]')) closeAll();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAll();
    });
})();

/* ── Settings switches (role="switch") — submit parent form ── */
(function settingsSwitch() {
    const sliders = document.querySelectorAll('.switch-slider[role="switch"]');
    if (!sliders.length) return;

    function trigger(slider) {
        const form = slider.closest('form');
        if (!form || slider.classList.contains('is-pending')) return;
        slider.classList.add('is-pending');
        slider.setAttribute('aria-checked', slider.getAttribute('aria-checked') === 'true' ? 'false' : 'true');
        if (form.requestSubmit) form.requestSubmit();
        else form.submit();
    }

    sliders.forEach((slider) => {
        slider.addEventListener('click', () => trigger(slider));
        slider.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                trigger(slider);
            }
        });
    });
})();

/* ── Account: keep active tab + scroll position across refresh ── */
(function accountStay() {
    const key = 'gg:account';
    const tabEls = document.querySelectorAll('.account-tab');

    if (tabEls.length) {
        // remember current tab (from URL) so refreshes stay put
        const params = new URLSearchParams(location.search);
        const cur = params.get('tab');
        if (cur) {
            try { sessionStorage.setItem(key, cur); } catch (_) {}
        }
        tabEls.forEach((t) => t.addEventListener('click', () => {
            const m = t.href.match(/[?&]tab=([^&]+)/);
            if (m) { try { sessionStorage.setItem(key, m[1]); } catch (_) {} }
        }));
    }

    // restore scroll after a soft refresh
    const navType = performance.getEntriesByType && performance.getEntriesByType('navigation').length
        ? performance.getEntriesByType('navigation')[0].type : '';
    if (navType === 'reload') {
        let y = 0;
        try { y = parseInt(sessionStorage.getItem(key + ':scroll'), 10) || 0; } catch (_) {}
        if (y > 0) {
            const restore = () => {
                const done = () => {
                    if (window.__lenis) window.__lenis.scrollTo(y, { immediate: true });
                    else window.scrollTo(0, y);
                };
                if (document.readyState !== 'complete') window.addEventListener('load', done, { once: true });
                else setTimeout(done, 250);
            };
            if (document.readyState !== 'loading') restore();
            else document.addEventListener('DOMContentLoaded', restore, { once: true });
        }
    }

    function saveScroll() {
        try {
            const y = Math.max(0, Math.round(window.scrollY || window.pageYOffset || 0));
            if (y > 0) sessionStorage.setItem(key + ':scroll', String(y));
        } catch (_) {}
    }
    window.addEventListener('pagehide', saveScroll);
    window.addEventListener('beforeunload', saveScroll);
})();

/* ── Cookie consent ── */
(function cookieConsent() {
    const KEY = 'gg_cookie_consent';
    const bar = document.getElementById('cookie-bar');
    const accept = bar && bar.querySelector('[data-cookie-accept]');
    const decline = bar && bar.querySelector('[data-cookie-decline]');
    const openBtns = document.querySelectorAll('[data-cookie-open]');

    const reached = (() => {
        try {
            const v = JSON.parse(localStorage.getItem(KEY) || 'null');
            return v && v.state;
        } catch (_) { return false; }
    })();

    const show = () => {
        if (!bar) return;
        bar.setAttribute('aria-hidden', 'false');
        bar.classList.add('is-visible');
    };
    const hide = () => {
        if (!bar) return;
        bar.removeAttribute('aria-hidden');
        bar.classList.remove('is-visible');
    };

    const save = (state) => {
        try { localStorage.setItem(KEY, JSON.stringify({ state, ts: Date.now() })); } catch (_) {}
        hide();
    };
    openBtns.forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); show(); }));

    if (!reached) {
        const t = setTimeout(show, 900);
        if (accept) accept.addEventListener('click', () => save('accepted'));
        if (decline) decline.addEventListener('click', () => save('declined'));
        // don't remove timers on purpose; keep the page stable
        window.addEventListener('keydown', (e) => { if (e.key === 'Escape') clearTimeout(t); });
    } else {
        hide();
    }
})();

/* ── Notifications page: mark-as-read on click ── */
(function notifPageRead() {
    const items = document.querySelectorAll('.notif-page-item[data-page-read-url]');
    if (!items.length) return;

    const csrfEl = document.querySelector('form input[name="_token"]');
    if (!csrfEl) return;
    const token = csrfEl.value;

    items.forEach((item) => {
        item.addEventListener('click', (e) => {
            const readUrl = item.dataset.pageReadUrl;
            if (!readUrl) return;
            e.preventDefault();
            const fd = new FormData();
            fd.append('_token', token);
            fetch(readUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .finally(() => { window.location.href = item.getAttribute('href'); });
        });
    });
})();
