@extends('layouts.store')

@section('title', 'Keranjang · Lintang Bakery')

@section('content')
@php
    $totalBal = collect($cart)->sum('quantity');
    $minTotalRequired = 100;
    $minPerItemRequired = 50;

    $underMinItems = collect($cart)->filter(fn ($row) => $row['quantity'] < $minPerItemRequired);
    $hasUnderMinItems = $underMinItems->isNotEmpty();

    $isTotalEligible = $totalBal >= $minTotalRequired;
    $isMixEligible = !$hasUnderMinItems;
    $isEligible = $isTotalEligible && $isMixEligible;

    $remainingTotalBal = max(0, $minTotalRequired - $totalBal);
    $progressPct = min(100, round(($totalBal / $minTotalRequired) * 100));
@endphp

<section class="page-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <span class="eyebrow">PESANAN RESELLER</span>
        <h1>Keranjang belanja</h1>
        <p>Minimal order total <strong>100 bal</strong>. Bisa di-mix (campur produk) dengan minimal <strong>50 bal per varian</strong> (contoh: 50/50).</p>
    </div>
    @if (count($cart))
        <a class="button button-light" href="{{ route('store.products') }}">← Tambah Produk Lain</a>
    @endif
</section>

@if (count($cart))
    {{-- Notifikasi Aturan Reseller: Min 100 bal total & Min 50 bal per produk saat mix --}}
    @if ($hasUnderMinItems)
        <div class="reseller-notice-box" style="border-color: #f59e0b; background: #fffbeb;">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #fde68a; display: grid; place-items: center; flex-shrink: 0;">
                    <svg style="width: 20px; height: 20px; stroke: #b45309; fill: none; stroke-width: 2.2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 4px; color: #b45309; font-size: 14.5px; font-weight: 750;">Ketentuan Mix Produk: Minimal 50 Bal per Varian</h4>
                    <p style="margin: 0; font-size: 12.5px; color: #92400e; line-height: 1.55;">
                        Pemesanan online dapat di-mix (campur produk) dengan syarat <strong>minimal 50 bal per varian produk</strong> (misal: 50 bal Kue Tambang + 50 bal Kue Keciput untuk memenuhi total 100 bal).
                    </p>
                    <p style="margin: 6px 0 0; font-size: 12px; font-weight: 700; color: #b45309;">
                        Produk yang belum mencapai minimal 50 bal:
                        {{ $underMinItems->map(fn($r) => $r['product']->name . ' (' . $r['quantity'] . ' bal, kurang ' . ($minPerItemRequired - $r['quantity']) . ' bal lagi)')->join(', ') }}.
                    </p>
                </div>
            </div>
        </div>
    @elseif (!$isTotalEligible)
        <div class="reseller-notice-box">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #fed7aa; display: grid; place-items: center; flex-shrink: 0;">
                    <svg style="width: 20px; height: 20px; stroke: #92400e; fill: none; stroke-width: 2.2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 4px; color: #92400e; font-size: 14.5px; font-weight: 750;">Total Pembelian Belum Mencapai 100 Bal</h4>
                    <p style="margin: 0; font-size: 12.5px; color: #78350f; line-height: 1.55;">
                        Total pesanan Anda saat ini baru <strong>{{ $totalBal }} bal</strong> (kurang <strong>{{ $remainingTotalBal }} bal</strong> lagi untuk checkout). Anda dapat menambahkan produk lain minimal 50 bal untuk mix 50/50!
                    </p>
                    <div class="reseller-progress-wrap">
                        <div style="display: flex; justify-content: space-between; font-size: 11.5px; margin-bottom: 4px; font-weight: 700; color: #92400e;">
                            <span>Progres Syarat Total Reseller:</span>
                            <span>{{ $totalBal }} / 100 bal ({{ $progressPct }}%)</span>
                        </div>
                        <div class="reseller-progress-bar">
                            <div class="reseller-progress-fill" style="width: {{ $progressPct }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="reseller-notice-box success">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #d4edda; display: grid; place-items: center; flex-shrink: 0;">
                    <svg style="width: 22px; height: 22px; stroke: #155724; fill: none; stroke-width: 2.5;" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div>
                    <h4 style="margin: 0 0 3px; color: #155724; font-size: 14.5px; font-weight: 750;">Syarat Minimal Reseller Terpenuhi! ({{ $totalBal }} Bal)</h4>
                    <p style="margin: 0; font-size: 12.5px; color: #1e6a32;">
                        Total pesanan telah mencapai batas minimal (100 bal) dan seluruh produk telah memenuhi ketentuan minimal 50 bal. Siap melanjutkan ke checkout!
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga / bal</th>
                    <th>Jumlah (Bal)</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($cart as $row)
                @php
                    $isRowUnder50 = $row['quantity'] < 50;
                @endphp
                <tr style="{{ $isRowUnder50 ? 'background: #fffdf5;' : '' }}">
                    <td>
                        <div class="table-product">
                            <img src="{{ $row['product']->image }}" alt="{{ $row['product']->name }}" style="width: 46px; height: 46px; object-fit: cover; border-radius: 6px;">
                            <div>
                                <strong>{{ $row['product']->name }}</strong>
                                <span style="display: block; font-size: 11px; color: var(--muted);">{{ $row['product']->category }}</span>
                                @if ($isRowUnder50)
                                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 5px; flex-wrap: wrap;">
                                        <span class="badge-warning-mini">
                                            Min. mix 50 bal (kurang {{ 50 - $row['quantity'] }} bal)
                                        </span>
                                        <form method="post" action="{{ route('cart.update', $row['product']) }}" style="margin: 0; display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="50">
                                            <button type="submit" class="btn-quick-50" title="Setel langsung menjadi 50 bal">+ Jadi 50 Bal</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>Rp {{ number_format($row['product']->price_per_bal, 0, ',', '.') }}</td>
                    <td>
                        <div class="quantity-control" aria-label="Jumlah {{ $row['product']->name }}">
                            <form method="post" action="{{ route('cart.update', $row['product']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ $row['quantity'] > 10 ? $row['quantity'] - 10 : max(1, $row['quantity'] - 1) }}">
                                <button class="quantity-button" type="submit" aria-label="Kurangi jumlah" @disabled($row['quantity'] <= 1)>−</button>
                            </form>
                            <span class="quantity-value" aria-live="polite" style="color: {{ $isRowUnder50 ? '#b45309' : 'inherit' }};">
                                {{ $row['quantity'] }}
                            </span>
                            <form method="post" action="{{ route('cart.update', $row['product']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ $row['quantity'] < 10 ? 10 : $row['quantity'] + 10 }}">
                                <button class="quantity-button" type="submit" aria-label="Tambah jumlah" @disabled($row['quantity'] >= 10000)>+</button>
                            </form>
                        </div>
                    </td>
                    <td><strong>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</strong></td>
                    <td>
                        <form method="post" action="{{ route('cart.destroy', $row['product']) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-danger" type="submit" style="background: none; border: none; cursor: pointer; font-size: 12px; font-weight: 600;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="cart-summary">
        <div>
            <span>Total produk ({{ $totalBal }} bal)</span>
            <strong>Rp {{ number_format(collect($cart)->sum('subtotal'), 0, ',', '.') }}</strong>
        </div>
        <p style="margin: 6px 0 12px; font-size: 11px; color: var(--muted);">Ongkos kirim (Rp 10.000 × bal) & opsi DP/lunas dihitung saat checkout.</p>
        
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @if ($isEligible)
                <a class="button button-full" href="{{ route('checkout.index') }}">Lanjut ke Checkout ({{ $totalBal }} Bal) →</a>
            @else
                @php
                    $alertMsg = $hasUnderMinItems 
                        ? 'Setiap produk harus minimal 50 bal jika di-mix (campur produk). Mohon lengkapi produk yang masih di bawah 50 bal.' 
                        : 'Minimal total pemesanan online adalah 100 bal. Silakan tambahkan ' . $remainingTotalBal . ' bal lagi.';
                @endphp
                <button type="button" class="button button-full" style="opacity: 0.65; cursor: not-allowed; background: #92400e; border-color: #92400e;" onclick="alert('{{ $alertMsg }}')">
                    @if ($hasUnderMinItems)
                        Lanjut ke Checkout (Ada Produk < 50 Bal) →
                    @else
                        Lanjut ke Checkout (Kurang {{ $remainingTotalBal }} Bal) →
                    @endif
                </button>
            @endif
            <a class="button button-light button-full" href="{{ route('store.products') }}" style="margin-top: 0;">+ Tambah / Mix Produk Lain</a>
        </div>
    </div>
@else
    <div class="empty-state">
        <h2>Keranjang masih kosong</h2>
        <p>Yuk, pilih aneka camilan & kue kering reseller favoritmu di katalog.</p>
        <a class="button" href="{{ route('store.products') }}">Lihat katalog produk</a>
    </div>
@endif
@endsection
