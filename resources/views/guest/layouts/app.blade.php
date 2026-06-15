<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', config('hotel.name', 'Room Service'))</title>
    
    <!-- SEO -->
    <meta name="description" content="Pesan makanan dan minuman langsung dari kamar hotel Anda.">
    
    <!-- PWA Settings -->
    <meta name="theme-color" content="#1B4332">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    @PwaHead
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons (lighter than Font Awesome) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-surface-alt text-text-primary font-sans h-full flex flex-col">
    
    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-border sticky top-0 z-50">
        <div class="px-4 py-3.5 flex items-center justify-between max-w-lg md:max-w-3xl lg:max-w-5xl mx-auto">
            <div class="flex items-center gap-3">
                @if(View::hasSection('back_url'))
                    <a href="@yield('back_url')" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-surface-muted transition-colors text-text-primary" id="nav-back-btn">
                        <i class="fa-solid fa-chevron-left text-sm"></i>
                    </a>
                @endif
                <div>
                    <h1 class="font-semibold text-base text-text-primary leading-tight" id="page-title">@yield('title', config('hotel.name', 'Room Service'))</h1>
                    @if(session('room_number'))
                    <p class="text-xs text-text-secondary mt-0.5"><i class="fa-solid fa-location-dot mr-1 text-accent"></i>Kamar {{ session('room_number') }}</p>
                    @endif
                </div>
            </div>
            
            @if(request()->routeIs('guest.menu.*'))
            <a href="{{ route('guest.cart', session('room_token', '')) }}" class="relative w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-muted transition-colors" id="cart-btn" x-data="{ count: {{ session('cart') ? collect(session('cart'))->sum('quantity') : 0 }} }" @cart-updated.window="count = $event.detail.count">
                <i class="fa-solid fa-bag-shopping text-text-primary"></i>
                <template x-if="count > 0">
                    <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] bg-primary text-white text-[10px] font-bold rounded-full px-1" x-text="count"></span>
                </template>
            </a>
            @endif
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto w-full max-w-lg md:max-w-3xl lg:max-w-5xl mx-auto bg-white shadow-sm border-x border-border/50">
        @if(session('error'))
        <div class="mx-4 mt-4 p-3 bg-danger-light text-danger rounded-xl text-sm font-medium flex items-center gap-2" id="error-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ session('error') }}
        </div>
        @endif
        
        @if(session('success'))
        <div class="mx-4 mt-4 p-3 bg-success-light text-success rounded-xl text-sm font-medium flex items-center gap-2" id="success-alert">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
        @endif

        @yield('content')
    </main>

    <!-- PWA Service Worker Registration -->
    @RegisterServiceWorkerScript
    
    @stack('scripts')
</body>
</html>
