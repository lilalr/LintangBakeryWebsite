@extends('layouts.admin')

@section('title', 'Transaksi Offline')
@section('section', 'Transaksi Offline')

@section('content')
<div class="content-heading"><div><span class="eyebrow">PENJUALAN TOKO FISIK</span><h1>Transaksi offline</h1><p>Catat transaksi langsung. Stok produk akan berkurang otomatis.</p></div></div>
<section class="form-card admin-form-card">
    <h2>Catat transaksi baru</h2>
    <form method="post" action="{{ route('admin.transactions.store') }}" class="stack-form">@csrf
        <div class="form-row"><label>Nama pembeli<input name="buyer_name" value="{{ old('buyer_name') }}" required></label><label>Tanggal<input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required></label></div>
        <div class="form-row"><label>Produk<select name="product_id" required><option value="">Pilih produk</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} · stok {{ $product->stock }} bal</option>@endforeach</select></label><label>Jumlah (bal)<input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}" required></label></div>
        <div class="form-row"><label>Metode pembayaran<select name="payment_method" required><option value="cash">Tunai</option><option value="qris-tf">QRIS / transfer</option></select></label><label>Catatan<input name="notes" value="{{ old('notes') }}"></label></div>
        <button class="button" type="submit">Simpan transaksi</button>
    </form>
</section>
<section class="panel section-gap"><div class="panel-heading"><div><h2>Riwayat transaksi</h2><p>{{ $transactions->total() }} transaksi tersimpan</p></div></div>
    <div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Pembeli</th><th>Produk</th><th>Pembayaran</th><th>Total</th><th></th></tr></thead><tbody>
    @forelse ($transactions as $transaction)
        <tr><td>{{ $transaction->date->format('d/m/Y') }}</td><td>{{ $transaction->buyer_name }}<small>{{ $transaction->notes }}</small></td><td>{{ $transaction->items->pluck('name')->join(', ') }}<small>{{ $transaction->items->sum('quantity') }} bal</small></td><td>{{ $transaction->payment_method === 'cash' ? 'Tunai' : 'QRIS / Transfer' }}</td><td><strong>Rp {{ number_format($transaction->total, 0, ',', '.') }}</strong></td><td><form method="post" action="{{ route('admin.transactions.destroy', $transaction) }}" onsubmit="return confirm('Hapus transaksi ini? Stok yang terjual tidak akan dikembalikan.')">@csrf @method('DELETE')<button class="text-danger">Hapus</button></form></td></tr>
    @empty<tr><td colspan="6" class="table-empty">Belum ada transaksi offline.</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination-wrap">{{ $transactions->links() }}</div>
</section>
@endsection
