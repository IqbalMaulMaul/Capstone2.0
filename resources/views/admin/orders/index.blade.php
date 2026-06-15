@extends('admin.layouts.admin')

@section('title', 'Riwayat Pesanan')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900">Riwayat Pesanan</h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 overflow-x-auto">
        <table id="ordersTable" class="w-full text-left border-collapse">
            <thead>
                <tr>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Order ID</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Kamar</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Tamu</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Status</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Total</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Tanggal</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr class="hover:bg-gray-50 border-b">
                    <td class="p-4 text-sm text-gray-900 font-medium">{{ $order->order_number }}</td>
                    <td class="p-4 text-sm text-gray-600">{{ $order->room->room_number }}</td>
                    <td class="p-4 text-sm text-gray-600">{{ $order->guest_name ?? '-' }}</td>
                    <td class="p-4 text-sm">
                        @php
                            $colors = [
                                'pending_payment' => 'bg-yellow-100 text-yellow-800',
                                'paid' => 'bg-blue-100 text-blue-800',
                                'accepted' => 'bg-indigo-100 text-indigo-800',
                                'processing' => 'bg-purple-100 text-purple-800',
                                'ready' => 'bg-orange-100 text-orange-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            $colorClass = $colors[$order->status] ?? 'bg-gray-100 text-gray-800';
                            $statusLabels = \App\Models\Order::STATUS_LABELS;
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                            {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="p-4 text-sm text-gray-900 font-medium">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    <td class="p-4 text-sm text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</td>
                    <td class="p-4 text-sm text-center">
                        <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center justify-center p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="inline-flex items-center justify-center p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors" title="Cetak Struk">
                            <i class="fa-solid fa-print"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- DataTables Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .dataTables_wrapper .dataTables_length select { padding-right: 2rem; }
    .dataTables_wrapper .dataTables_filter input { border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.25rem 0.5rem; margin-left: 0.5rem; }
    table.dataTable.no-footer { border-bottom: 1px solid #e5e7eb; }
</style>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#ordersTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
            },
            order: [[5, 'desc']], // Urutkan berdasarkan tanggal terbaru
        });

        if (window.Echo) {
            window.Echo.channel('kitchen')
                .listen('.status.updated', (e) => {
                    console.log('Admin update received:', e);
                    // Fetch new data silently
                    fetch(window.location.href)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newTbody = doc.querySelector('#ordersTable tbody').innerHTML;
                            
                            // Destroy, replace, re-init
                            table.destroy();
                            document.querySelector('#ordersTable tbody').innerHTML = newTbody;
                            table = $('#ordersTable').DataTable({
                                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
                                order: [[5, 'desc']],
                            });
                        });
                });
        }
    });
</script>
@endsection
