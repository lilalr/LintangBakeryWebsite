@extends('layouts.admin')

@section('title', 'Data Pelanggan')
@section('section', 'Pelanggan')

@section('content')
<div class="content-heading" style="margin-bottom: 24px;">
    <div>
        <span class="eyebrow">KELOLA AKUN</span>
        <h1>Data Pelanggan</h1>
        <p>Pantau data akun pembeli terdaftar, kontak WhatsApp, dan riwayat pesanan online.</p>
    </div>
</div>

<!-- 3 Kartu Ringkasan Bergaya Modern -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 24px;">
    <article style="background: white; border: 1px solid #ebd9c8; border-radius: 12px; padding: 20px 22px; display: flex; justify-content: space-between; align-items: flex-start; box-shadow: 0 2px 6px rgba(92,58,33,0.03);">
        <div>
            <span style="display: block; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8c7b70; margin-bottom: 6px;">Total Pelanggan</span>
            <strong style="display: block; font-size: 2rem; font-weight: 800; color: #2b1d16; line-height: 1.1;">{{ $totalCustomers }}</strong>
            <small style="display: block; font-size: 0.8rem; color: #9c8e84; margin-top: 6px;">Akun pembeli terdaftar</small>
        </div>
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #fdf4eb; color: #8c532b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
    </article>

    <article style="background: white; border: 1px solid #ebd9c8; border-radius: 12px; padding: 20px 22px; display: flex; justify-content: space-between; align-items: flex-start; box-shadow: 0 2px 6px rgba(92,58,33,0.03);">
        <div>
            <span style="display: block; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8c7b70; margin-bottom: 6px;">Pelanggan Aktif</span>
            <strong style="display: block; font-size: 2rem; font-weight: 800; color: #2b1d16; line-height: 1.1;">{{ $activeCustomers }}</strong>
            <small style="display: block; font-size: 0.8rem; color: #9c8e84; margin-top: 6px;">Pernah melakukan pemesanan</small>
        </div>
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #edf7ed; color: #2e7d32; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
        </div>
    </article>

    <article style="background: white; border: 1px solid #ebd9c8; border-radius: 12px; padding: 20px 22px; display: flex; justify-content: space-between; align-items: flex-start; box-shadow: 0 2px 6px rgba(92,58,33,0.03);">
        <div>
            <span style="display: block; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #8c7b70; margin-bottom: 6px;">Pesanan Online</span>
            <strong style="display: block; font-size: 2rem; font-weight: 800; color: #2b1d16; line-height: 1.1;">{{ $totalOrdersCount }}</strong>
            <small style="display: block; font-size: 0.8rem; color: #9c8e84; margin-top: 6px;">Total pesanan yang masuk</small>
        </div>
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #fff8e6; color: #b45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
    </article>
</div>

<!-- Panel Data Pelanggan -->
<section class="panel" style="background: white; border: 1px solid #ebd9c8; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 14px rgba(92,58,33,0.03);">
    <!-- Baris Judul & Pencarian -->
    <div style="padding: 20px 24px; border-bottom: 1px solid #f2e9df; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.15rem; font-weight: 750; margin: 0; color: #2b1d16;">Daftar Pelanggan</h2>
            <p style="margin: 3px 0 0; font-size: 0.84rem; color: #786b62;">Daftar lengkap pengguna yang mendaftar di toko online Lintang Bakery.</p>
        </div>
        
        <form method="get" action="{{ route('admin.customers.index') }}" style="display: flex; gap: 8px; align-items: center; margin: 0;">
            <div style="position: relative; display: flex; align-items: center;">
                <svg style="position: absolute; left: 12px; width: 16px; height: 16px; stroke: #9c8e84; fill: none; stroke-width: 2; pointer-events: none;" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, email, hp, alamat..." style="padding: 9px 14px 9px 36px; border: 1px solid #d9cebe; border-radius: 8px; font-size: 0.88rem; min-width: 270px; background: #fdfbf9; color: #2b1d16; outline: none; transition: border-color .15s ease;">
            </div>
            <button type="submit" class="button" style="padding: 9px 16px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px;">
                <svg style="width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2.2;" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <span>Cari</span>
            </button>
            @if ($search)
                <a href="{{ route('admin.customers.index') }}" class="button button-light" style="padding: 9px 14px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px;">
                    <svg style="width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    <span>Reset</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Tabel Data Pelanggan -->
    <div class="table-scroll">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #faf6f0; border-bottom: 1px solid #ebd9c8;">
                    <th style="padding: 13px 20px; text-align: left; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 30%;">Pelanggan</th>
                    <th style="padding: 13px 18px; text-align: left; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 20%;">Kontak & WhatsApp</th>
                    <th style="padding: 13px 18px; text-align: left; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 22%;">Alamat Pengiriman</th>
                    <th style="padding: 13px 16px; text-align: center; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 12%;">Pesanan</th>
                    <th style="padding: 13px 18px; text-align: right; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 14%;">Total Belanja</th>
                    <th style="padding: 13px 20px; text-align: center; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 10%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($customers as $customer)
                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone ?? '');
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                @endphp
                <tr style="border-bottom: 1px solid #f5ede4; transition: background .12s ease;">
                    <td style="padding: 16px 20px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #f7eee2 0%, #ecd9c4 100%); color: var(--brown, #5c3a21); border: 1px solid #e2cfbd; display: flex; align-items: center; justify-content: center; font-weight: 750; font-size: 1rem; flex-shrink: 0;">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                            <div>
                                <strong style="display: block; font-size: 0.94rem; color: #2b1d16; line-height: 1.3;">{{ $customer->name }}</strong>
                                <span style="display: block; color: #786b62; font-size: 0.82rem; margin-top: 1px;">{{ $customer->email }}</span>
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.72rem; color: #a4968d; margin-top: 3px;">
                                    <svg style="width: 11px; height: 11px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                    Terdaftar {{ $customer->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 16px 18px;">
                        @if ($customer->phone)
                            <div style="font-size: 0.88rem; font-weight: 600; color: #2b1d16;">{{ $customer->phone }}</div>
                            @if ($cleanPhone)
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 20px; font-size: 0.74rem; font-weight: 700; color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; text-decoration: none; margin-top: 5px;">
                                    <!-- Official Clean WhatsApp SVG Icon -->
                                    <svg style="width: 12px; height: 12px; fill: currentColor;" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.584 1.745.882 2.791.882 3.182 0 5.768-2.587 5.768-5.766.001-3.187-2.575-5.768-5.768-5.768zm7.397 5.766c-.001 4.078-3.32 7.397-7.398 7.397-1.258 0-2.433-.319-3.468-.887l-3.844 1.008 1.028-3.753c-.662-1.077-1.021-2.327-1.021-3.765.001-4.078 3.32-7.398 7.398-7.398 4.079 0 7.399 3.32 7.405 7.398zm-4.321 3.518c-.183-.092-1.082-.534-1.25-.595-.168-.061-.29-.092-.413.092-.122.183-.474.595-.581.718-.107.122-.214.138-.397.046-.183-.092-.773-.285-1.472-.908-.544-.485-.911-1.085-1.018-1.268-.107-.183-.011-.282.08-.373.082-.082.183-.214.275-.321.092-.107.122-.183.183-.306.061-.122.031-.229-.015-.321s-.413-.993-.566-1.36c-.149-.356-.301-.307-.413-.313-.107-.005-.229-.007-.352-.007s-.321.046-.489.229c-.168.183-.642.627-.642 1.529 0 .902.657 1.773.749 1.895.092.122 1.293 1.974 3.132 2.766.438.188.78.3 1.047.385.44.14.84.12 1.157.073.353-.053 1.082-.443 1.235-.87.153-.427.153-.794.107-.87-.046-.076-.168-.122-.351-.214z"/>
                                    </svg>
                                    <span>WhatsApp</span>
                                </a>
                            @endif
                        @else
                            <span style="color: #a4968d; font-size: 0.82rem; font-style: italic;">Belum diisi</span>
                        @endif
                    </td>
                    <td style="padding: 16px 18px;">
                        @if ($customer->address)
                            <div style="font-size: 0.85rem; color: #443730; line-height: 1.4; max-width: 260px; display: flex; align-items: flex-start; gap: 5px;">
                                <svg style="width: 14px; height: 14px; stroke: #9c8e84; fill: none; stroke-width: 2; flex-shrink: 0; margin-top: 2px;" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span>{{ \Illuminate\Support\Str::limit($customer->address, 65) }}</span>
                            </div>
                        @else
                            <span style="color: #a4968d; font-size: 0.82rem; font-style: italic;">Belum ada alamat</span>
                        @endif
                    </td>
                    <td style="padding: 16px 16px; text-align: center;">
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 650; background: #fdf5eb; color: var(--brown, #5c3a21); border: 1px solid #ebd9c8;">
                            {{ $customer->orders_count }} pesanan
                        </span>
                    </td>
                    <td style="padding: 16px 18px; text-align: right;">
                        <strong style="color: #2b1d16; font-size: 0.95rem; font-weight: 750;">
                            Rp {{ number_format($customer->total_spent, 0, ',', '.') }}
                        </strong>
                    </td>
                    <td style="padding: 16px 20px; text-align: center;">
                        <a href="{{ route('admin.customers.show', $customer) }}" class="button button-small button-light" style="padding: 6px 12px; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; border-radius: 6px;">
                            <svg style="width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span>Detail</span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 48px 20px;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: #faf6f0; color: #a4968d; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                            <svg style="width: 26px; height: 26px; stroke: currentColor; fill: none; stroke-width: 1.8;" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <strong style="display: block; font-size: 0.95rem; color: #2b1d16; margin-bottom: 4px;">
                            @if ($search)
                                Tidak ada pelanggan dengan kata kunci "{{ $search }}"
                            @else
                                Belum ada pelanggan terdaftar
                            @endif
                        </strong>
                        <p style="margin: 0; font-size: 0.84rem; color: #786b62;">
                            @if ($search)
                                Coba periksa kembali ejaan atau gunakan kata kunci pencarian yang lain.
                            @else
                                Data pembeli online akan otomatis muncul di sini saat user mendaftar akun.
                            @endif
                        </p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($customers->hasPages())
        <div style="padding: 16px 24px; border-top: 1px solid #f2e9df;">
            {{ $customers->links() }}
        </div>
    @endif
</section>
@endsection
