@extends('layouts.admin')

@section('title', 'Detail Pelanggan - ' . $customer->name)
@section('section', 'Pelanggan')

@section('content')
<div class="content-heading" style="margin-bottom: 24px;">
    <div>
        <span class="eyebrow">DETAIL AKUN PELANGGAN</span>
        <h1>{{ $customer->name }}</h1>
        <p>Informasi profil, alamat pengiriman, dan riwayat pesanan online.</p>
    </div>
    <a class="button button-light" href="{{ route('admin.customers.index') }}" style="display: inline-flex; align-items: center; gap: 6px;">
        <svg style="width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2.2;" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
        <span>Kembali ke Pelanggan</span>
    </a>
</div>

@php
    $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone ?? '');
    if (str_starts_with($cleanPhone, '0')) {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    }
@endphp

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- Profile Card -->
    <section class="panel" style="background: white; border: 1px solid #ebd9c8; border-radius: 14px; padding: 26px; box-shadow: 0 4px 14px rgba(92,58,33,0.03);">
        <div style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #f2e9df;">
            <div style="width: 76px; height: 76px; border-radius: 50%; background: linear-gradient(135deg, #f7eee2 0%, #ecd9c4 100%); color: var(--brown, #5c3a21); border: 2px solid #e2cfbd; display: flex; align-items: center; justify-content: center; font-weight: 750; font-size: 2rem; margin: 0 auto 14px; box-shadow: 0 4px 12px rgba(92, 58, 33, 0.08);">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 750; margin: 0; color: #2b1d16;">{{ $customer->name }}</h2>
            <div style="font-size: 0.85rem; color: #786b62; margin-top: 4px;">{{ $customer->email }}</div>
            <span style="display: inline-flex; align-items: center; gap: 5px; margin-top: 10px; padding: 4px 12px; border-radius: 20px; font-size: 0.74rem; font-weight: 700; background: #edf7ed; color: #1e7e34; border: 1px solid #c3e6cb;">
                <svg style="width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2.2;" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                Pelanggan Online Terverifikasi
            </span>
        </div>

        <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 16px;">
            <div>
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 750; color: #8a7c73; letter-spacing: 0.05em; margin-bottom: 4px;">Nomor Telepon & WhatsApp</label>
                @if ($customer->phone)
                    <div style="font-size: 0.95rem; font-weight: 650; color: #2b1d16;">{{ $customer->phone }}</div>
                    @if ($cleanPhone)
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener noreferrer" style="margin-top: 8px; display: inline-flex; align-items: center; gap: 7px; font-weight: 650; text-decoration: none; padding: 7px 14px; border-radius: 8px; font-size: 0.82rem; background: #15803d; color: #ffffff; box-shadow: 0 2px 6px rgba(21,128,61,0.2);">
                            <svg style="width: 14px; height: 14px; fill: currentColor;" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.584 1.745.882 2.791.882 3.182 0 5.768-2.587 5.768-5.766.001-3.187-2.575-5.768-5.768-5.768zm7.397 5.766c-.001 4.078-3.32 7.397-7.398 7.397-1.258 0-2.433-.319-3.468-.887l-3.844 1.008 1.028-3.753c-.662-1.077-1.021-2.327-1.021-3.765.001-4.078 3.32-7.398 7.398-7.398 4.079 0 7.399 3.32 7.405 7.398zm-4.321 3.518c-.183-.092-1.082-.534-1.25-.595-.168-.061-.29-.092-.413.092-.122.183-.474.595-.581.718-.107.122-.214.138-.397.046-.183-.092-.773-.285-1.472-.908-.544-.485-.911-1.085-1.018-1.268-.107-.183-.011-.282.08-.373.082-.082.183-.214.275-.321.092-.107.122-.183.183-.306.061-.122.031-.229-.015-.321s-.413-.993-.566-1.36c-.149-.356-.301-.307-.413-.313-.107-.005-.229-.007-.352-.007s-.321.046-.489.229c-.168.183-.642.627-.642 1.529 0 .902.657 1.773.749 1.895.092.122 1.293 1.974 3.132 2.766.438.188.78.3 1.047.385.44.14.84.12 1.157.073.353-.053 1.082-.443 1.235-.87.153-.427.153-.794.107-.87-.046-.076-.168-.122-.351-.214z"/>
                            </svg>
                            <span>Chat WhatsApp</span>
                        </a>
                    @endif
                @else
                    <div style="font-size: 0.85rem; color: #a4968d; font-style: italic;">Belum ditambahkan</div>
                @endif
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 750; color: #8a7c73; letter-spacing: 0.05em; margin-bottom: 4px;">Alamat Pengiriman</label>
                <div style="font-size: 0.86rem; color: #3b2f27; line-height: 1.45; background: #faf6f0; padding: 12px 14px; border-radius: 8px; border: 1px solid #ebd9c8; display: flex; align-items: flex-start; gap: 8px;">
                    <svg style="width: 16px; height: 16px; stroke: #8c7b70; fill: none; stroke-width: 2; flex-shrink: 0; margin-top: 2px;" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>{{ $customer->address ?: 'Belum ada alamat pengiriman tersimpan.' }}</span>
                </div>
            </div>

            <div style="border-top: 1px solid #f2e9df; padding-top: 16px; display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                    <span style="color: #786b62;">Akun Dibuat:</span>
                    <strong style="color: #2b1d16;">{{ $customer->created_at->translatedFormat('d F Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                    <span style="color: #786b62;">Total Transaksi:</span>
                    <strong style="color: #2b1d16;">{{ $customer->orders->count() }} pesanan</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.95rem; padding-top: 10px; border-top: 1px dashed #d9cebe; margin-top: 4px;">
                    <span style="font-weight: 700; color: #2b1d16;">Total Belanja:</span>
                    <strong style="color: var(--brown, #5c3a21); font-size: 1.05rem;">Rp {{ number_format($totalSpent, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </section>

    <!-- Orders Section -->
    <section class="panel" style="background: white; border: 1px solid #ebd9c8; border-radius: 14px; padding: 24px; box-shadow: 0 4px 14px rgba(92,58,33,0.03);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #f2e9df;">
            <div>
                <h2 style="font-size: 1.15rem; font-weight: 750; margin: 0; color: #2b1d16;">Riwayat Pesanan Pelanggan</h2>
                <p style="margin: 3px 0 0; font-size: 0.84rem; color: #786b62;">Daftar transaksi online {{ $customer->name }}.</p>
            </div>
            <span style="font-size: 0.82rem; font-weight: 700; background: #fdf5eb; color: var(--brown, #5c3a21); padding: 5px 12px; border-radius: 20px; border: 1px solid #ebd9c8;">
                {{ $customer->orders->count() }} Pesanan
            </span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            @forelse ($customer->orders as $order)
                <article style="background: #faf6f0; border: 1px solid #ebd9c8; border-radius: 12px; padding: 16px 18px; transition: all 0.2s ease;">
                    {{-- Baris Atas: Kode, Tanggal, dan Status --}}
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #e6d5c5;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <a href="{{ route('admin.orders.show', $order) }}" style="font-size: 0.98rem; font-weight: 800; color: var(--brown-dark, #2b1d16); text-decoration: none;" title="Buka Detail Pesanan">
                                    {{ $order->code }}
                                </a>
                                <span style="font-size: 0.76rem; color: #8a7c73;">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                        </div>

                        {{-- Status & Quick Update --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <form method="post" action="{{ route('admin.orders.update', $order) }}" style="margin: 0;">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" title="Ganti Status Pesanan" style="font-size: 0.78rem; font-weight: 700; padding: 5px 9px; border-radius: 6px; border: 1px solid #d9cebe; background: #ffffff; color: #2b1d16; cursor: pointer;">
                                    @foreach (\App\Http\Controllers\Admin\OrderController::STATUSES as $st)
                                        <option value="{{ $st }}" @selected($order->status === $st)>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <a href="{{ route('admin.orders.show', $order) }}" class="button button-small button-light" style="font-size: 0.78rem; padding: 5px 10px; text-decoration: none; white-space: nowrap;">
                                Detail &rarr;
                            </a>
                        </div>
                    </div>

                    {{-- Baris Tengah: Rincian Produk --}}
                    <div style="margin-bottom: 10px; font-size: 0.86rem; color: #3b2f27; line-height: 1.5;">
                        <span style="font-weight: 600;">Item:</span>
                        {{ $order->items->map(fn($it) => $it->name . ' (' . $it->quantity . ' bal)')->join(', ') }}
                        <span style="color: #8a7c73; font-size: 0.8rem;">(Total: {{ $order->items->sum('quantity') }} bal)</span>
                    </div>

                    {{-- Baris Bawah: Total Pembayaran & Bukti Bayar --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.85rem;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span style="font-weight: 700; color: #2b1d16; font-size: 0.95rem;">
                                Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}
                            </span>
                            <span style="color: #786b62; font-size: 0.8rem; background: #ffffff; padding: 2px 8px; border-radius: 4px; border: 1px solid #e8dbcd;">
                                {{ $order->payment_type === 'dp' ? 'DP: Rp '.number_format($order->dp, 0, ',', '.').' (Sisa: Rp '.number_format($order->remaining, 0, ',', '.').')' : 'Lunas (100%)' }} · {{ strtoupper($order->payment_method ?? 'Transfer') }}
                            </span>
                        </div>

                        @if ($order->payment_proof)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof) }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 5px; font-size: 0.78rem; font-weight: 700; color: #b45309; text-decoration: none;">
                                <svg style="width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                                <span>Lihat Bukti Bayar</span>
                            </a>
                        @endif
                    </div>
                </article>
            @empty
                <div style="text-align: center; padding: 36px 20px; background: #faf6f0; border-radius: 10px; border: 1px dashed #ebd9c8;">
                    <svg style="width: 28px; height: 28px; stroke: #a4968d; fill: none; stroke-width: 1.8; margin: 0 auto 8px; display: block;" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <strong style="display: block; font-size: 0.92rem; color: #2b1d16; margin-bottom: 2px;">Belum Ada Riwayat Pesanan</strong>
                    <p style="margin: 0; font-size: 0.82rem; color: #786b62;">Pelanggan ini belum pernah melakukan checkout pesanan online.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
