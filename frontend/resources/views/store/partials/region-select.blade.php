@php $regions = config('regions'); @endphp

<label>Provinsi</label>
<select name="province" id="province" required>
  <option value="">Pilih provinsi</option>
  @foreach (array_keys($regions) as $p)
    <option value="{{ $p }}" @selected(old('province', $province) === $p)>{{ $p }}</option>
  @endforeach
</select>

<label>Kota/Kabupaten</label>
<select name="city" id="city" required></select>

<script>
  const regions = @json($regions);
  const prov = document.getElementById('province');
  const city = document.getElementById('city');
  let selectedCity = @json(old('city', $city));

  function fillCities() {
    const list = regions[prov.value] || [];
    city.innerHTML = '<option value="">Pilih kota/kabupaten</option>' +
      list.map(c => `<option value="${c}" ${c === selectedCity ? 'selected' : ''}>${c}</option>`).join('');
    selectedCity = '';
  }
  prov.addEventListener('change', fillCities);
  fillCities();
</script>