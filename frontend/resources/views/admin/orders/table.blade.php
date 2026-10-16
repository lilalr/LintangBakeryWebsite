<div class="table-scroll"><table><thead><tr><th>Kode / Pembeli</th><th>Tanggal</th><th>Produk</th><th>Total</th><th>Status / aksi</th></tr></thead><tbody>
@forelse ($orders as $order)
    <tr>
        <td><strong>{{ $order->code }}</strong><small>{{ $order->buyer }} · {{ $order->phone }}</small>@if ($order->payment_proof)<small><a class="text-link" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof) }}" target="_blank" rel="noopener noreferrer">Lihat bukti bayar</a></small>@endif</td>
        <td>{{ $order->created_at->format('d/m/Y') }}</td>
        <td>{{ $order->items->pluck('name')->join(', ') }}</td>
        <td>Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}<small>{{ $order->payment_type === 'dp' ? 'DP Rp '.number_format($order->dp, 0, ',', '.') : 'Pembayaran lunas' }}</small></td>
        <td><form method="post" action="{{ route('admin.orders.update', $order) }}" class="status-form">@csrf @method('PATCH')<select name="status" aria-label="Status pesanan {{ $order->code }}">@foreach ($statuses as $status)<option value="{{ $status }}" @selected($order->status === $status)>{{ $status }}</option>@endforeach</select><button class="button button-small">Simpan</button></form></td>
    </tr>
@empty<tr><td colspan="5" class="table-empty">Belum ada pesanan masuk.</td></tr>@endforelse
</tbody></table></div>
