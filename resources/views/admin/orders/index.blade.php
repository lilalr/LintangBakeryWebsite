@extends('layouts.admin')

@section('title', 'Pesanan')
@section('section', 'Pesanan')

@section('content')
<div class="content-heading"><div><span class="eyebrow">PESANAN ONLINE</span><h1>Kelola pesanan</h1><p>Perbarui status produksi, pembayaran, dan pengiriman.</p></div></div>
<section class="panel">
    @include('admin.orders.table', ['compact' => false])
    <div class="pagination-wrap">{{ $orders->links() }}</div>
</section>
@endsection
