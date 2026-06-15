@extends('admin.layouts.admin')

@section('title', 'Layar Dapur (Kitchen)')

@section('content')
<div x-data="{ showMenuModal: false }">
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Pesanan Aktif (Kitchen)</h1>
        <p class="text-gray-600 mt-1">Pesanan yang sudah dibayar dan siap untuk diproses.</p>
    </div>
    <div class="flex items-center space-x-3">
        <button @click="showMenuModal = true" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#1a1a2e] hover:bg-gray-800 transition-colors">
            <i class="fa-solid fa-list-check mr-2"></i> Atur Stok Menu
        </button>
        <div id="poll-status" class="flex items-center space-x-2 text-sm text-gray-500 bg-white px-3 py-1.5 rounded-full shadow-sm border border-gray-100 hidden md:flex transition-colors duration-300">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <span>Auto-refresh aktif</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="kitchen-orders">
    @forelse($orders as $order)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full">
            <!-- Order Header -->
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h3 class="font-bold text-lg text-gray-900">Kamar {{ $order->room->room_number }}</h3>
                    <p class="text-xs text-gray-500">{{ $order->order_number }} • {{ $order->created_at->format('H:i') }}</p>
                </div>
                
                <div class="flex flex-col items-end gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium 
                        @if($order->status == 'paid') bg-blue-100 text-blue-800
                        @elseif($order->status == 'accepted') bg-yellow-100 text-yellow-800
                        @elseif($order->status == 'processing') bg-orange-100 text-orange-800
                        @elseif($order->status == 'ready') bg-green-100 text-green-800
                        @endif
                    ">
                        {{ $order->status_label }}
                    </span>
                    <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="text-xs text-gray-600 hover:text-gray-900 flex items-center gap-1 bg-white border border-gray-200 px-2 py-1 rounded shadow-sm">
                        <i class="fa-solid fa-print"></i> Cetak
                    </a>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="px-5 py-4 flex-1">
                <ul class="space-y-4">
                    @foreach($order->items as $item)
                        <li class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $item->quantity }}x {{ $item->menu_name }}</p>
                                @if($item->notes)
                                    <p class="text-xs text-red-600 mt-1 flex items-start">
                                        <svg class="w-3.5 h-3.5 mr-1 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ $item->notes }}
                                    </p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                
                @if($order->notes)
                    <div class="mt-4 pt-3 border-t border-dashed border-gray-200">
                        <p class="text-xs text-gray-500 font-medium mb-1">Catatan Pesanan:</p>
                        <p class="text-sm text-gray-800">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
            
            <!-- Actions -->
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 mt-auto">
                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    @if($order->status == 'paid')
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            Terima Pesanan
                        </button>
                    @elseif($order->status == 'accepted')
                        <input type="hidden" name="status" value="processing">
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 transition-colors">
                            Mulai Masak
                        </button>
                    @elseif($order->status == 'processing')
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                            Selesai & Siap Diantar
                        </button>
                    @elseif($order->status == 'ready')
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-[#1a1a2e] hover:bg-gray-800 transition-colors">
                            Tandai Sedang Diantar
                        </button>
                    @endif
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-full flex flex-col items-center justify-center p-12 bg-white rounded-xl shadow-sm border border-gray-200 border-dashed">
            <div class="p-4 rounded-full bg-gray-50 mb-4">
                <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Dapur Sedang Kosong</h3>
            <p class="text-gray-500 text-center">Belum ada pesanan aktif. Pesanan yang masuk akan otomatis muncul di sini.</p>
        </div>
    @endforelse
</div>

<!-- Menu Stock Modal -->
<div x-show="showMenuModal" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showMenuModal = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                            Atur Ketersediaan Menu
                        </h3>
                        
                        <div class="mt-2 max-h-[60vh] overflow-y-auto pr-2">
                            @foreach($categories as $category)
                                @if($category->menus->count() > 0)
                                    <div class="mb-6">
                                        <h4 class="font-bold text-gray-800 mb-3 border-b pb-1">{{ $category->name }}</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            @foreach($category->menus as $menu)
                                                <div class="flex items-center justify-between p-3 border rounded-lg {{ $menu->is_available ? 'bg-white border-gray-200' : 'bg-red-50 border-red-100' }}">
                                                    <span class="text-sm font-medium {{ $menu->is_available ? 'text-gray-900' : 'text-red-700' }}">{{ $menu->name }}</span>
                                                    
                                                    <form action="{{ route('admin.menus.toggle', $menu) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="focus:outline-none transition-transform hover:scale-105 active:scale-95">
                                                            @if($menu->is_available)
                                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200 shadow-sm">
                                                                    Tersedia
                                                                </span>
                                                            @else
                                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200 shadow-sm">
                                                                    Habis
                                                                </span>
                                                            @endif
                                                        </button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                <button type="button" @click="showMenuModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('kitchen-orders');
        let lastOrderCount = container ? container.children.length : 0;
        let pollInterval = null;

        function pollKitchenOrders() {
            fetch('{{ route("admin.kitchen.poll") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (container) {
                    container.innerHTML = data.html;
                    
                    // Notify if new orders arrived
                    if (data.count > lastOrderCount && lastOrderCount > 0) {
                        // Play notification sound
                        try {
                            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                            const oscillator = audioCtx.createOscillator();
                            const gainNode = audioCtx.createGain();
                            oscillator.connect(gainNode);
                            gainNode.connect(audioCtx.destination);
                            oscillator.frequency.value = 800;
                            oscillator.type = 'sine';
                            gainNode.gain.value = 0.3;
                            oscillator.start();
                            setTimeout(() => { oscillator.frequency.value = 1000; }, 150);
                            setTimeout(() => { oscillator.stop(); }, 300);
                        } catch(e) {}
                        
                        // Flash notification badge
                        const badge = document.getElementById('poll-status');
                        if (badge) {
                            badge.classList.add('!bg-yellow-500');
                            badge.querySelector('span:last-child').textContent = 'Pesanan Baru!';
                            setTimeout(() => {
                                badge.classList.remove('!bg-yellow-500');
                                badge.querySelector('span:last-child').textContent = 'Auto-refresh aktif';
                            }, 3000);
                        }
                    }
                    lastOrderCount = data.count;
                }
            })
            .catch(err => console.error('Poll error:', err));
        }

        // Start polling every 5 seconds
        pollInterval = setInterval(pollKitchenOrders, 5000);

        // Also poll immediately on page focus (tab switch back)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                pollKitchenOrders();
            }
        });

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (pollInterval) clearInterval(pollInterval);
        });
    });
</script>
@endpush
