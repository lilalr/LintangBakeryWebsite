document.querySelectorAll('[data-region-form]').forEach((form) => {
    const regions = JSON.parse(form.dataset.regions);
    const provinceSelect = form.querySelector('[name="province"]');
    const citySelect = form.querySelector('[name="city"]');
    const oldCity = form.querySelector('[data-old-city]').value;

    const updateCities = (selectedCity = '') => {
        const cities = regions[provinceSelect.value] ?? [];
        citySelect.replaceChildren(new Option(
            cities.length ? 'Pilih kota / kabupaten' : 'Pilih provinsi terlebih dahulu',
            '',
        ));

        cities.forEach((city) => {
            citySelect.add(new Option(city, city, false, city === selectedCity));
        });

        citySelect.disabled = cities.length === 0;
    };

    provinceSelect.addEventListener('change', () => updateCities());
    citySelect.addEventListener('change', () => {
        const matchingProvince = Object.entries(regions)
            .find(([, cities]) => cities.includes(citySelect.value))?.[0];

        if (matchingProvince && matchingProvince !== provinceSelect.value) {
            provinceSelect.value = matchingProvince;
            updateCities(citySelect.value);
        }
    });

    updateCities(oldCity);
});

document.querySelectorAll('[data-toast]').forEach((toast) => {
    const dismiss = () => {
        toast.classList.add('is-hiding');
        window.setTimeout(() => toast.remove(), 250);
    };

    const autoDismissTimer = window.setTimeout(dismiss, 4500);

    toast.querySelector('.toast-close')?.addEventListener('click', () => {
        window.clearTimeout(autoDismissTimer);
        dismiss();
    });
});

document.querySelectorAll('[data-password-field]').forEach((field) => {
    const input = field.querySelector('input');
    const toggle = field.querySelector('.password-toggle');

    if (!input || !toggle) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isVisible = input.type === 'text';
        input.type = isVisible ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!isVisible));
        toggle.setAttribute('aria-label', isVisible ? 'Lihat password' : 'Sembunyikan password');
        toggle.querySelectorAll('[data-password-icon]').forEach((icon, index) => {
            icon.toggleAttribute('hidden', index === (isVisible ? 0 : 1));
        });
    });
});

// Cart Drawer
const cartDrawer = document.getElementById('cartDrawer');
const cartDrawerOverlay = document.getElementById('cartDrawerOverlay');
const openCartDrawerBtn = document.getElementById('openCartDrawerBtn');
const closeCartDrawerBtn = document.getElementById('closeCartDrawerBtn');
const browseProductsBtn = document.getElementById('browseProductsBtn');

function openCartDrawer() {
    if (cartDrawer && cartDrawerOverlay) {
        cartDrawer.classList.add('active');
        cartDrawerOverlay.classList.add('active');
        cartDrawer.setAttribute('aria-hidden', 'false');
        cartDrawerOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
}

function closeCartDrawer() {
    if (cartDrawer && cartDrawerOverlay) {
        cartDrawer.classList.remove('active');
        cartDrawerOverlay.classList.remove('active');
        cartDrawer.setAttribute('aria-hidden', 'true');
        cartDrawerOverlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
}

window.openCartDrawer = openCartDrawer;
window.closeCartDrawer = closeCartDrawer;

if (openCartDrawerBtn) {
    openCartDrawerBtn.addEventListener('click', (e) => {
        if (!window.location.pathname.includes('/checkout')) {
            e.preventDefault();
            openCartDrawer();
        }
    });
}

document.querySelectorAll('[data-open-cart]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openCartDrawer();
    });
});

if (closeCartDrawerBtn) {
    closeCartDrawerBtn.addEventListener('click', closeCartDrawer);
}

if (cartDrawerOverlay) {
    cartDrawerOverlay.addEventListener('click', closeCartDrawer);
}

if (browseProductsBtn) {
    browseProductsBtn.addEventListener('click', closeCartDrawer);
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && cartDrawer?.classList.contains('active')) {
        closeCartDrawer();
    }
});

if (cartDrawer?.classList.contains('active')) {
    document.body.style.overflow = 'hidden';
}

// ── Input jumlah manual (qty-manual-input) ──────────────────────────────────
// Submit form saat user tekan Enter atau klik di luar (blur).
// Validasi: nilai minimal 1, diabaikan jika tidak berubah.
// Update subtotal & total real-time saat mengetik.
document.addEventListener('DOMContentLoaded', () => {
    function formatRupiah(num) {
        return 'Rp ' + num.toLocaleString('id-ID');
    }

    function recalcCartTotal() {
        const totalEl   = document.getElementById('cart-total-amount');
        const labelEl   = document.getElementById('cart-total-label');
        if (!totalEl) return;

        let total = 0, totalQty = 0;
        document.querySelectorAll('.qty-manual-input[data-price]').forEach((inp) => {
            if (inp.closest('#cartDrawer')) return; // skip drawer inputs
            total    += parseInt(inp.dataset.price, 10) * (parseInt(inp.value, 10) || 0);
            totalQty += parseInt(inp.value, 10) || 0;
        });

        totalEl.textContent = formatRupiah(total);
        if (labelEl) labelEl.textContent = 'Total produk (' + totalQty + ' bal)';
    }

    function recalcDrawerTotal() {
        const totalEl = document.getElementById('drawer-total-amount');
        const labelEl = document.getElementById('drawer-total-label');
        if (!totalEl) return;

        let total = 0, totalQty = 0;
        document.querySelectorAll('#cartDrawer .qty-manual-input[data-price]').forEach((inp) => {
            total    += parseInt(inp.dataset.price, 10) * (parseInt(inp.value, 10) || 0);
            totalQty += parseInt(inp.value, 10) || 0;
        });

        totalEl.textContent = formatRupiah(total);
        if (labelEl) labelEl.textContent = 'Total (' + totalQty + ' bal):';
    }

    // Real-time update saat user mengetik
    document.addEventListener('input', (e) => {
        if (!e.target.classList.contains('qty-manual-input')) return;

        const input = e.target;
        const price = parseInt(input.dataset.price || '0', 10);
        const qty   = parseInt(input.value, 10) || 0;

        // Update subtotal baris (hanya di cart page table)
        const subtotalCell = input.closest('tr')?.querySelector('[data-subtotal]');
        if (subtotalCell) {
            subtotalCell.textContent = formatRupiah(price * qty);
        }

        recalcCartTotal();
        recalcDrawerTotal();
    });

    function initQtyManualInputs() {
        document.querySelectorAll('.qty-manual-input').forEach((input) => {
            if (input.dataset.qtyInited) return;
            input.dataset.qtyInited = '1';

            const originalValue = input.value;

            // Buat tombol ✓ yang muncul saat nilai berubah
            const confirmBtn = document.createElement('button');
            confirmBtn.type = 'button';
            confirmBtn.textContent = '✓';
            confirmBtn.className = 'qty-confirm-btn';
            confirmBtn.setAttribute('aria-label', 'Konfirmasi jumlah');
            confirmBtn.style.display = 'none';
            input.closest('form').after(confirmBtn);

            const showConfirm = (show) => {
                confirmBtn.style.display = show ? 'inline-flex' : 'none';
            };

            const submitIfChanged = () => {
                const val = parseInt(input.value, 10);
                if (isNaN(val) || val < 1) {
                    input.value = originalValue;
                    showConfirm(false);
                    return;
                }
                if (String(val) !== originalValue) {
                    input.value = val;
                    input.closest('form').submit();
                } else {
                    showConfirm(false);
                }
            };

            input.addEventListener('input', () => {
                showConfirm(input.value !== originalValue);
            });

            confirmBtn.addEventListener('mousedown', (e) => {
                // Cegah blur dulu, biar confirm yang jalan
                e.preventDefault();
            });
            confirmBtn.addEventListener('click', submitIfChanged);

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitIfChanged();
                }
                if (e.key === 'Escape') {
                    input.value = originalValue;
                    showConfirm(false);
                    input.blur();
                }
            });

            input.addEventListener('blur', submitIfChanged);
        });
    }

    initQtyManualInputs();
});
