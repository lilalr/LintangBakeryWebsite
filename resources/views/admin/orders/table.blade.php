<div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 16%;">Kode Pesanan</th>
                <th style="width: 14%;">Tanggal</th>
                <th style="width: 19%;">Pembeli</th>
                <th style="width: 15%;">Total Tagihan</th>
                <th style="width: 20%;">Status</th>
                <th style="width: 11%; text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($orders as $order)
            <tr>
                <td style="text-align: center; vertical-align: middle; color: var(--muted); font-size: 12px; font-weight: 600;">
                    @if (method_exists($orders, 'firstItem') && $orders->firstItem())
                        {{ $orders->firstItem() + $loop->index }}
                    @else
                        {{ $loop->iteration }}
                    @endif
                </td>
                <td>
                    <strong style="font-family: monospace; font-size: 12.5px;">
                        <a href="{{ route('admin.orders.show', $order) }}" style="color: var(--brown-dark); text-decoration: underline;">
                            {{ $order->code }}
                        </a>
                    </strong>
                    @if ($order->settlement_status === 'Menunggu Verifikasi Admin')
                        <span class="status-pill" style="display: block; margin-top: 4px; background: #e0e7ff; color: #3730a3; font-size: 9.5px; font-weight: 700; width: fit-content; padding: 2px 6px;">
                            Verifikasi Pelunasan
                        </span>
                    @endif
                </td>
                <td>
                    <strong style="font-size: 12.5px; display: block; color: var(--brown-dark);">
                        {{ $order->created_at->format('d/m/Y') }}
                    </strong>
                    <small class="muted" style="font-size: 11px;">
                        {{ $order->created_at->format('H:i') }}
                    </small>
                </td>
                <td>
                    <strong style="display: block; font-size: 13px; color: var(--brown-dark);">{{ $order->buyer }}</strong>
                    @if(!empty($order->phone))
                        <small class="muted" style="font-size: 11px;">{{ $order->phone }}</small>
                    @endif
                </td>
                <td>
                    <strong style="color: var(--brown); font-size: 13.5px; display: block;">
                        Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}
                    </strong>
                    <small class="muted" style="font-size: 11px;">
                        {{ $order->items->sum('quantity') }} bal &bull; {{ $order->payment_type === 'dp' ? 'DP' : 'Lunas' }}
                    </small>
                </td>
                <td>
                    @if ($compact ?? false)
                        <span class="status-pill">{{ $order->status }}</span>
                    @else
                        <form method="post" action="{{ route('admin.orders.update', $order) }}" class="status-form" style="display: inline-flex; align-items: center; gap: 4px; margin: 0;">
                            @csrf @method('PATCH')
                            <select name="status" aria-label="Status pesanan {{ $order->code }}" style="font-size: 11.5px; padding: 4px 8px; min-height: 30px; border-radius: 6px;">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($order->status === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <button class="button button-small" style="padding: 4px 8px; font-size: 11px;">Ubah</button>
                        </form>
                    @endif
                </td>
                <td style="text-align: right;">
                    <div style="display: inline-flex; gap: 4px; align-items: center; justify-content: flex-end;">
                        <a href="{{ route('admin.orders.show', $order) }}" class="button button-small button-light" style="padding: 4px 8px; font-size: 11px;">Detail</a>
                        <a href="{{ route('admin.orders.delivery-order', $order) }}" target="_blank" class="button button-small button-light" title="Cetak Surat Jalan" style="padding: 4px 8px; font-size: 11px;">Surat Jalan</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="table-empty">Belum ada pesanan masuk.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
