@extends('layouts.admin')

@section('title', 'Barang Keluar')
@section('section', 'Stok / Barang Keluar')

@section('content')
<div class="content-heading">
    <div>
        <span class="eyebrow">PERSEDIAAN OFFLINE</span>
        <h1>Barang keluar</h1>
        <p>Catat barang yang keluar karena rusak, kedaluwarsa, atau alasan lain. Stok akan berkurang otomatis.</p>
    </div>
</div>
@include('admin.stock._nav', ['active' => 'keluar'])

@if ($errors->any())
    <div class="alert alert-error" style="margin-bottom:16px;">
        <ul style="margin:0; padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ route('admin.stock.issue') }}">
    @csrf
    <section class="panel">
        <div class="filter-bar" style="flex-wrap:wrap; gap:16px;">
            <label style="min-width:160px;">
                Tanggal keluar
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required>
            </label>
            <label style="min-width:220px;">
                Produk
                <select name="product_id" required id="issueProductSelect">
                    <option value="">— Pilih produk —</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}"
                                data-stock="{{ $product->stock }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} (stok: {{ $product->stock }} bal)
                        </option>
                    @endforeach
                </select>
            </label>
            <label style="min-width:130px;">
                Jumlah (bal)
                <input type="number" name="quantity" min="1" max="100000" id="issueQty"
                       value="{{ old('quantity') }}" required placeholder="0">
                <small class="form-hint" id="issueStockHint"></small>
            </label>
            <label style="min-width:180px;">
                Alasan
                <select name="reason" required>
                    <option value="">— Pilih alasan —</option>
                    @foreach ($reasons as $reason)
                        <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>
                            {{ $reason }}
                            @if (in_array($reason, \App\Services\StockService::LOSS_REASONS)) (kerugian) @endif
                        </option>
                    @endforeach
                </select>
            </label>
            <label style="flex:1; min-width:220px;">
                Catatan <span class="form-hint">(opsional)</span>
                <input type="text" name="note" value="{{ old('note') }}" maxlength="1000"
                       placeholder="Misal: produk jatuh saat pengiriman">
            </label>
        </div>
    </section>
    <button class="button" type="submit" style="margin-top:16px">Simpan barang keluar</button>
</form>
<p class="form-hint" style="margin-top:12px">
    Barang keluar dengan alasan <strong>Rusak</strong> atau <strong>Kedaluwarsa</strong>
    akan dicatat sebagai kerugian berdasarkan harga pokok produk.
</p>
<script>
const issueSelect = document.getElementById('issueProductSelect');
const issueQty = document.getElementById('issueQty');
const issueHint = document.getElementById('issueStockHint');
if (issueSelect) {
    issueSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const stock = opt.getAttribute('data-stock');
        if (stock !== null && stock !== '') {
            issueQty.max = stock;
            if (issueHint) issueHint.textContent = 'Maksimal: ' + stock + ' bal.';
        } else {
            if (issueHint) issueHint.textContent = '';
        }
    });
}
</script>
@endsection
