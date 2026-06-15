@extends('admin.layouts.admin')

@section('title', isset($user) ? 'Edit Staf' : 'Tambah Staf')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 mb-2 inline-block">&larr; Kembali ke Daftar Staf</a>
    <h1 class="text-2xl font-bold text-gray-900">{{ isset($user) ? 'Edit Staf: ' . $user->name : 'Tambah Staf Baru' }}</h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-2xl">
    <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" class="p-6">
        @csrf
        @if(isset($user))
            @method('PUT')
        @endif

        <div class="space-y-6">
            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required value="{{ old('name', $user->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" required value="{{ old('email', $user->email ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Peran (Role) -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Peran (Role) <span class="text-red-500">*</span></label>
                    <select id="role" name="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                        <option value="owner" {{ old('role', $user->role ?? '') == 'owner' ? 'selected' : '' }}>Owner / Admin</option>
                        <option value="kitchen" {{ old('role', $user->role ?? '') == 'kitchen' ? 'selected' : '' }}>Kitchen / Dapur</option>
                        <option value="finance" {{ old('role', $user->role ?? '') == 'finance' ? 'selected' : '' }}>Finance / Kasir</option>
                    </select>
                    @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <hr class="border-gray-200">

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password {{ isset($user) ? '(Kosongkan jika tidak diubah)' : '*' }}</label>
                    <input type="password" name="password" id="password" {{ isset($user) ? '' : 'required' }} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password {{ isset($user) ? '' : '*' }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" {{ isset($user) ? '' : 'required' }} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                </div>
            </div>
            
            <p class="text-xs text-gray-500 mt-2">Password minimal harus 8 karakter.</p>
        </div>

        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end">
            <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#1a1a2e] hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1a1a2e]">
                {{ isset($user) ? 'Simpan Perubahan' : 'Tambahkan Staf' }}
            </button>
        </div>
    </form>
</div>
@endsection
