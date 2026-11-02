<div class="stock-tools">
    <a class="button button-small {{ $active === 'ringkasan' ? '' : 'button-light' }}" href="{{ route('admin.stock.index') }}">Ringkasan stok</a>
    <a class="button button-small {{ $active === 'kartu' ? '' : 'button-light' }}" href="{{ route('admin.stock.card') }}">Kartu stok</a>
    <a class="button button-small {{ $active === 'opname' ? '' : 'button-light' }}" href="{{ route('admin.stock.opname.form') }}">Stok opname</a>
    <span class="spacer"></span>
    <a class="button button-small {{ $active === 'masuk' ? '' : 'button-light' }}" href="{{ route('admin.stock.receive.form') }}">＋ Barang masuk</a>
    <a class="button button-small {{ $active === 'keluar' ? '' : 'button-light' }}" href="{{ route('admin.stock.issue.form') }}">－ Barang keluar</a>
</div>
