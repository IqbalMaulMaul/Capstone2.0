@extends('guest.layouts.app')

@section('title', config('hotel.name', 'Room Service'))

@section('content')
<div class="px-4 py-5 pb-8">
    <!-- Welcome Header -->
    <div class="mb-5">
        <p class="text-text-secondary text-sm">Selamat datang 👋</p>
        <h2 class="text-xl font-bold text-text-primary mt-0.5">Mau pesan apa hari ini?</h2>
    </div>

    <!-- Search Bar -->
    <div class="mb-5">
        <form action="{{ route('guest.menu.index', $token) }}" method="GET" class="relative" id="search-form">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari menu..." class="w-full bg-surface-muted border border-border rounded-xl py-3 pl-10 pr-4 text-sm text-text-primary placeholder-text-muted focus:border-primary focus:ring-1 focus:ring-primary/20 focus:bg-white transition-all outline-none" id="search-input">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-text-muted text-sm"></i>
            @if($categoryId)
                <input type="hidden" name="category" value="{{ $categoryId }}">
            @endif
        </form>
    </div>

    <!-- Categories Filter -->
    @if(!$search)
    <div class="mb-6 overflow-x-auto -mx-4 px-4 hide-scrollbar">
        <div class="flex gap-2">
            <a href="{{ route('guest.menu.index', $token) }}" class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all {{ !$categoryId ? 'bg-primary text-white' : 'bg-surface-muted text-text-secondary hover:bg-border' }}" id="filter-all">
                Semua
            </a>
            @foreach($categories as $category)
                <a href="{{ route('guest.menu.index', ['token' => $token, 'category' => $category->id]) }}" class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-1.5 {{ $categoryId == $category->id ? 'bg-primary text-white' : 'bg-surface-muted text-text-secondary hover:bg-border' }}" id="filter-cat-{{ $category->id }}">
                    @if($category->icon)
                        <i class="fa-solid fa-{{ $category->icon }} text-xs"></i>
                    @endif
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Menu List -->
    <div class="space-y-7">
        @forelse($menus as $categoryName => $categoryMenus)
            @if($categoryMenus->count() > 0)
                <section>
                    <h3 class="text-sm font-semibold text-text-secondary uppercase tracking-wider mb-3">{{ $categoryName }}</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
                        @foreach($categoryMenus as $menu)
                            <a href="{{ route('guest.menu.show', ['token' => $token, 'slug' => $menu->slug]) }}" class="bg-white rounded-2xl p-3 flex gap-3.5 border border-border hover:border-primary/40 hover:shadow-md transition-all duration-300 group h-full" id="menu-item-{{ $menu->id }}">
                                <div class="w-20 h-20 rounded-xl bg-surface-muted flex-shrink-0 overflow-hidden">
                                    @if($menu->image_url)
                                        <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-text-muted">
                                            <i class="fa-solid fa-utensils text-lg"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1 py-0.5 flex flex-col min-w-0">
                                    <h4 class="font-semibold text-text-primary text-sm leading-tight mb-1 truncate">{{ $menu->name }}</h4>
                                    <p class="text-xs text-text-secondary line-clamp-2 mb-auto leading-relaxed">{{ $menu->description }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="text-sm font-bold text-primary">{{ $menu->formatted_price }}</p>
                                        <span class="w-7 h-7 bg-primary/10 text-primary rounded-lg flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                                            <i class="fa-solid fa-plus text-xs"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        @empty
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mx-auto mb-4 text-text-muted">
                    <i class="fa-solid fa-utensils text-xl"></i>
                </div>
                <h3 class="text-base font-semibold text-text-primary mb-1">Menu Tidak Ditemukan</h3>
                <p class="text-sm text-text-secondary">Coba kata kunci lain atau lihat semua menu.</p>
                @if($search || $categoryId)
                    <a href="{{ route('guest.menu.index', $token) }}" class="inline-block mt-4 text-primary font-medium text-sm hover:underline" id="reset-filter-link">Lihat Semua Menu</a>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection
