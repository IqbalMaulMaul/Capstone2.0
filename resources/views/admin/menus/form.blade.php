@extends('admin.layouts.admin')

@section('title', isset($menu) ? 'Edit Menu' : 'Tambah Menu Baru')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.menus.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 mb-2 inline-block">&larr; Kembali ke Daftar Menu</a>
    <h1 class="text-2xl font-bold text-gray-900">{{ isset($menu) ? 'Edit Menu: ' . $menu->name : 'Tambah Menu Baru' }}</h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-3xl">
    <form action="{{ isset($menu) ? route('admin.menus.update', $menu) : route('admin.menus.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
        @csrf
        @if(isset($menu))
            @method('PUT')
        @endif

        <div class="space-y-6">
            <!-- Nama Menu -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Menu <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required value="{{ old('name', $menu->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Kategori -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $menu->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Harga -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="price" min="0" step="1000" required value="{{ old('price', isset($menu) ? intval($menu->price) : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                    @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">{{ old('description', $menu->description ?? '') }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Foto -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Foto Menu</label>
                @if(isset($menu) && $menu->image_path)
                    <div class="mt-2 mb-3">
                        <img src="{{ $menu->image_url }}" alt="Current Image" class="h-32 rounded-lg object-cover border border-gray-200">
                    </div>
                @endif
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[#c9a84c]">
                                <span>Upload a file</span>
                                <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                    </div>
                </div>
                @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Ketersediaan -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="is_available" name="is_available" type="checkbox" value="1" {{ old('is_available', $menu->is_available ?? true) ? 'checked' : '' }} class="focus:ring-[#c9a84c] h-4 w-4 text-[#1a1a2e] border-gray-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="is_available" class="font-medium text-gray-700">Tersedia untuk dipesan</label>
                    <p class="text-gray-500">Hapus centang jika menu ini sedang kosong/habis.</p>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end">
            <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#1a1a2e] hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1a1a2e]">
                {{ isset($menu) ? 'Simpan Perubahan' : 'Tambahkan Menu' }}
            </button>
        </div>
    </form>
</div>
@endsection
