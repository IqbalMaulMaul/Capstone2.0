@extends('admin.layouts.admin')

@section('title', isset($room) ? 'Edit Kamar' : 'Tambah Kamar Baru')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.rooms.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 mb-2 inline-block">&larr; Kembali ke Daftar Kamar</a>
    <h1 class="text-2xl font-bold text-gray-900">{{ isset($room) ? 'Edit Kamar: ' . $room->room_number : 'Tambah Kamar Baru' }}</h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-2xl">
    <form action="{{ isset($room) ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}" method="POST" class="p-6">
        @csrf
        @if(isset($room))
            @method('PUT')
        @endif

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Nomor Kamar -->
                <div>
                    <label for="room_number" class="block text-sm font-medium text-gray-700">Nomor Kamar <span class="text-red-500">*</span></label>
                    <input type="text" name="room_number" id="room_number" required value="{{ old('room_number', $room->room_number ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                    @error('room_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Lantai -->
                <div>
                    <label for="floor" class="block text-sm font-medium text-gray-700">Lantai <span class="text-red-500">*</span></label>
                    <input type="number" name="floor" id="floor" min="1" required value="{{ old('floor', $room->floor ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                    @error('floor') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Ketersediaan -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $room->is_active ?? true) ? 'checked' : '' }} class="focus:ring-[#c9a84c] h-4 w-4 text-[#1a1a2e] border-gray-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="is_active" class="font-medium text-gray-700">Kamar Aktif (Bisa Memesan)</label>
                    <p class="text-gray-500">Hapus centang jika kamar sedang dalam perbaikan (Out of Order).</p>
                </div>
            </div>

            @if(isset($room))
            <div class="pt-4 border-t border-gray-200">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="regenerate_token" name="regenerate_token" type="checkbox" value="1" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="regenerate_token" class="font-medium text-red-700">Generate Ulang QR Token</label>
                        <p class="text-red-500">Peringatan: Melakukan ini akan membuat QR Code lama tidak bisa digunakan lagi. Lakukan hanya jika QR Code sebelumnya bocor/disalahgunakan.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end">
            <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#1a1a2e] hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1a1a2e]">
                {{ isset($room) ? 'Simpan Perubahan' : 'Tambahkan Kamar' }}
            </button>
        </div>
    </form>
</div>
@endsection
