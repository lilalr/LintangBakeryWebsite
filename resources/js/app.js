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

