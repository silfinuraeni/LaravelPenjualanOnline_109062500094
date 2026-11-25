@extends('layouts.admin')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Stok & Barang Masuk</h2>
    <a href="{{ route('admin.stock.create') }}" class="btn">+ Catat Barang Masuk</a>
</div>

@if(session('success'))
    <div class="alert">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Produk</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Referensi / Catatan</th>
                <th>Admin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement->movement_at }}</td>
                    <td>{{ $movement->product->name ?? 'Produk Dihapus' }}</td>
                    <td>
                        @if($movement->type == 'IN')
                            <span style="font-weight: bold; color: #065F46;">IN</span>
                        @else
                            <span style="font-weight: bold; color: #991B1B;">OUT</span>
                        @endif
                    </td>
                    <td>{{ $movement->quantity }}</td>
                    <td>
                        <strong>{{ $movement->reference }}</strong><br>
                        <small style="color: #6B7280;">{{ $movement->note }}</small>
                    </td>
                    <td>{{ $movement->user->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #6B7280; padding: 20px;">Belum ada riwayat pergerakan stok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $movements->links() }}
    </div>
</div>
@endsection