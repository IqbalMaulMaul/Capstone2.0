@extends('admin.layouts.admin')

@section('title', isset($category) ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.categories.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 mb-2 inline-block">&larr; Kembali ke Daftar Kategori</a>
    <h1 class="text-2xl font-bold text-gray-900">{{ isset($category) ? 'Edit Kategori: ' . $category->name : 'Tambah Kategori Baru' }}</h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-2xl">
    <form action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" class="p-6">
        @csrf
        @if(isset($category))
            @method('PUT')
        @endif

        <div class="space-y-6">
            <!-- Nama Kategori -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required value="{{ old('name', $category->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Ikon FontAwesome -->
            <div>
                <label for="icon" class="block text-sm font-medium text-gray-700">Ikon FontAwesome (Opsional)</label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                        fa-
                    </span>
                    <input type="text" name="icon" id="icon" placeholder="bowl-food, mug-hot, dll" value="{{ old('icon', $category->icon ?? '') }}" class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                </div>
                <p class="mt-1 text-xs text-gray-500">Ketik nama ikon dari <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" class="text-blue-600 hover:underline">FontAwesome Free</a> (tanpa awalan fa-).</p>
                @error('icon') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Urutan -->
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700">Urutan Tampil</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $category->sort_order ?? '') }}" placeholder="Biarkan kosong untuk otomatis ditaruh di akhir" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                <p class="mt-1 text-xs text-gray-500">Angka lebih kecil akan tampil lebih dulu (contoh: 1, 2, 3).</p>
                @error('sort_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end">
            <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#1a1a2e] hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1a1a2e]">
                {{ isset($category) ? 'Simpan Perubahan' : 'Tambahkan Kategori' }}
            </button>
        </div>
    </form>
</div>
@endsection
