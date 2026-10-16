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
