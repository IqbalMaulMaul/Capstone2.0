@extends('admin.layouts.admin')

@section('title', 'Daftar Pembayaran')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900">Daftar Pembayaran</h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 overflow-x-auto">
        <table id="paymentsTable" class="w-full text-left border-collapse">
            <thead>
                <tr>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Trx ID / Token</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Order ID</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Kamar</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Metode</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Status</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Nominal</th>
                    <th class="border-b-2 p-4 text-sm font-semibold text-gray-600">Waktu Bayar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr class="hover:bg-gray-50 border-b">
                    <td class="p-4 text-sm text-gray-900 font-medium">
                        {{ $payment->transaction_id ?? ($payment->snap_token ? 'Token: ' . substr($payment->snap_token, 0, 10) . '...' : '-') }}
                    </td>
                    <td class="p-4 text-sm text-blue-600 hover:underline">
                        <a href="{{ route('admin.orders.show', $payment->order_id) }}">
                            {{ $payment->order->order_number ?? '#' . $payment->order_id }}
                        </a>
                    </td>
                    <td class="p-4 text-sm text-gray-600">{{ $payment->order->room->room_number ?? '-' }}</td>
                    <td class="p-4 text-sm text-gray-600">{{ strtoupper($payment->method ?? '-') }}</td>
                    <td class="p-4 text-sm">
                        @php
                            $colors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'success' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                                'expired' => 'bg-gray-100 text-gray-800',
                                'refunded' => 'bg-purple-100 text-purple-800',
                            ];
                            $colorClass = $colors[$payment->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td class="p-4 text-sm text-gray-900 font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td class="p-4 text-sm text-gray-500">{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d M Y H:i') : '-' }}</td>
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
        $('#paymentsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
            },
            order: [[6, 'desc']], // Urutkan berdasarkan waktu bayar terbaru
        });
    });
</script>
@endsection
