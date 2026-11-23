@extends('layouts.admin')

@section('title', 'Data Pelanggan')
@section('section', 'Pelanggan')

@section('content')
<div class="content-heading" style="margin-bottom: 24px;">
    <div>
        <span class="eyebrow">KELOLA AKUN</span>
        <h1>Data Pelanggan</h1>
        <p>Pantau data akun pembeli terdaftar, kontak, dan riwayat pesanan online.</p>
    </div>
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
                    <th style="padding: 13px 16px; text-align: center; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 5%;">No</th>
                    <th style="padding: 13px 20px; text-align: left; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 26%;">Pelanggan</th>
                    <th style="padding: 13px 18px; text-align: left; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 25%;">Kontak</th>
                    <th style="padding: 13px 18px; text-align: left; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 26%;">Alamat Pengiriman</th>
                    <th style="padding: 13px 16px; text-align: center; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 10%;">Pesanan</th>
                    <th style="padding: 13px 20px; text-align: center; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 0.06em; color: #7c6d62; width: 8%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($customers as $customer)
                <tr style="border-bottom: 1px solid #f5ede4; transition: background .12s ease;">
                    <td style="text-align: center; vertical-align: middle; color: var(--muted); font-size: 12px; font-weight: 600;">
                        @if (method_exists($customers, 'firstItem') && $customers->firstItem())
                            {{ $customers->firstItem() + $loop->index }}
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </td>
                    <td style="padding: 16px 20px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #f7eee2 0%, #ecd9c4 100%); color: var(--brown, #5c3a21); border: 1px solid #e2cfbd; display: flex; align-items: center; justify-content: center; font-weight: 750; font-size: 0.95rem; flex-shrink: 0;">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                            <div>
                                <strong style="display: block; font-size: 0.94rem; color: #2b1d16; line-height: 1.3;">{{ $customer->name }}</strong>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 16px 18px;">
                        @if ($customer->phone)
                            <div style="font-size: 0.88rem; font-weight: 600; color: #2b1d16;">
                                {{ $customer->phone }}
                            </div>
                        @else
                            <div style="color: #a4968d; font-size: 0.82rem; font-style: italic;">No HP belum diisi</div>
                        @endif
                        <div style="font-size: 0.82rem; color: #786b62; margin-top: 4px; display: flex; align-items: center; gap: 5px;">
                            <svg style="width: 12px; height: 12px; stroke: #9c8e84; fill: none; stroke-width: 2; flex-shrink: 0;" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            <span>{{ $customer->email }}</span>
                        </div>
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
                        @if (($customer->active_orders_count ?? 0) > 0)
                            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 20px; font-size: 0.74rem; font-weight: 700; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; white-space: nowrap;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #0284c7;"></span>
                                {{ $customer->active_orders_count }} Pesanan Aktif
                            </span>
                        @else
                            <span style="color: #a4968d; font-size: 0.88rem; font-weight: 500;">-</span>
                        @endif
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
