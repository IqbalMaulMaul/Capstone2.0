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
