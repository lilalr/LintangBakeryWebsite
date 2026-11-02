<div class="table-scroll"><table><thead><tr><th>Kode / Pembeli</th><th>Tanggal</th><th>Produk</th><th>Total & Pembayaran</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead><tbody>
@forelse ($orders as $order)
    <tr>
        <td>
            <strong><a href="{{ route('admin.orders.show', $order) }}" style="color:var(--brown-dark); text-decoration:underline;">{{ $order->code }}</a></strong>
            <small>{{ $order->buyer }} · {{ $order->phone }}</small>
            @if ($order->settlement_status === 'Menunggu Verifikasi Admin')
                <span class="status-pill" style="display:inline-block; margin-top:3px; background:#e0e7ff; color:#3730a3; font-size:10px; font-weight:700;">Bukti Pelunasan Diunggah</span>
            @endif
        </td>
        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
        <td>{{ $order->items->pluck('name')->join(', ') }} <small class="muted" style="display:block;">({{ $order->items->sum('quantity') }} bal)</small></td>
        <td>
            <strong>Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}</strong>
            <small style="display:block; color:var(--muted);">
                {{ $order->payment_type === 'dp' ? 'DP Rp '.number_format($order->dp, 0, ',', '.').' · Sisa Rp '.number_format($order->remaining, 0, ',', '.') : 'Lunas (100%)' }}
            </small>
        </td>
        <td>
            <form method="post" action="{{ route('admin.orders.update', $order) }}" class="status-form" style="display:inline-flex; align-items:center; gap:4px;">
                @csrf @method('PATCH')
                <select name="status" aria-label="Status pesanan {{ $order->code }}" style="font-size:11.5px; padding:4px 8px; min-height:32px;">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="button button-small" style="padding:4px 8px; font-size:11px;">Ubah</button>
            </form>
        </td>
        <td style="text-align:right;">
            <div style="display:inline-flex; gap:4px; align-items:center;">
                <a href="{{ route('admin.orders.show', $order) }}" class="button button-small button-light" style="padding:4px 8px; font-size:11px;">Detail</a>
                <a href="{{ route('admin.orders.delivery-order', $order) }}" target="_blank" class="button button-small button-light" title="Cetak Surat Jalan" style="padding:4px 8px; font-size:11px;">Surat Jalan</a>
            </div>
        </td>
    </tr>
@empty<tr><td colspan="6" class="table-empty">Belum ada pesanan masuk.</td></tr>@endforelse
</tbody></table></div>
