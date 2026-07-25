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
    
    <!-- Fonts — self-hosted via Google Fonts CSS with display=swap (non-blocking) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"></noscript>
    
    <!-- Font Awesome — SUBSET: only icons used in guest views (~3KB vs 90KB+ full CDN) -->
    <style>
        @font-face {
            font-family: 'Font Awesome 6 Free';
            font-style: normal;
            font-weight: 900;
            font-display: swap;
            src: url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/fa-solid-900.woff2') format('woff2');
        }
        .fa-solid, .fas {
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            line-height: 1;
            display: inline-block;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        /* Only the icons used in guest pages */
        .fa-chevron-left::before { content: "\f053"; }
        .fa-chevron-right::before { content: "\f054"; }
        .fa-chevron-down::before { content: "\f078"; }
        .fa-location-dot::before { content: "\f3c5"; }
        .fa-bag-shopping::before { content: "\f290"; }
        .fa-magnifying-glass::before { content: "\f002"; }
        .fa-utensils::before { content: "\f2e7"; }
        .fa-plus::before { content: "\2b"; }
        .fa-minus::before { content: "\f068"; }
        .fa-cart-plus::before { content: "\f217"; }
        .fa-spinner::before { content: "\f110"; }
        .fa-check::before { content: "\f00c"; }
        .fa-check-circle::before { content: "\f058"; }
        .fa-check-double::before { content: "\f560"; }
        .fa-circle-exclamation::before { content: "\f06a"; }
        .fa-circle-check::before { content: "\f058"; }
        .fa-circle-info::before { content: "\f05a"; }
        .fa-circle-notch::before { content: "\f1ce"; }
        .fa-trash-can::before { content: "\f2ed"; }
        .fa-receipt::before { content: "\f543"; }
        .fa-clock::before { content: "\f017"; }
        .fa-clock-rotate-left::before { content: "\f1da"; }
        .fa-money-bill-wave::before { content: "\f53a"; }
        .fa-qrcode::before { content: "\f029"; }
        .fa-arrow-right::before { content: "\f061"; }
        .fa-rotate::before { content: "\f2f1"; }
        .fa-xmark::before { content: "\f00d"; }
        .fa-times-circle::before { content: "\f057"; }
        .fa-motorcycle::before { content: "\f21c"; }
        .fa-bell::before { content: "\f0f3"; }
        .fa-bell-concierge::before { content: "\f562"; }
        .fa-fire::before { content: "\f06d"; }
        .fa-fire-burner::before { content: "\e4f1"; }
        .fa-pen::before { content: "\f304"; }
        .fa-lock::before { content: "\f023"; }
        .fa-wallet::before { content: "\f555"; }
        .fa-play::before { content: "\f04b"; }
        .fa-person-walking::before { content: "\f554"; }
        .fa-walking::before { content: "\f554"; }
        .fa-flag-checkered::before { content: "\f11e"; }
        .fa-triangle-exclamation::before { content: "\f071"; }
        .fa-mug-hot::before { content: "\f7b6"; }
        .fa-bowl-food::before { content: "\e4c6"; }
        .fa-ice-cream::before { content: "\f810"; }
        .fa-wine-glass::before { content: "\f4e3"; }
        .fa-cookie-bite::before { content: "\f564"; }
        .fa-drumstick-bite::before { content: "\f6d7"; }
        .fa-burger::before { content: "\f805"; }
        .fa-pizza-slice::before { content: "\f818"; }
        @keyframes fa-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .fa-spin { animation: fa-spin 2s infinite linear; }
        .animate-bounce { animation: bounce 1s infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(-25%); animation-timing-function: cubic-bezier(0.8,0,1,1); } 50% { transform: translateY(0); animation-timing-function: cubic-bezier(0,0,0.2,1); } }
    </style>

    <!-- Preload critical Vite assets -->
    @php
        $cssFile = null;
        $jsFile = null;
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (is_array($manifest)) {
                $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
                $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
            }
        }
    @endphp
    @if($cssFile)
    <link rel="preload" href="{{ asset('build/' . $cssFile) }}" as="style">
    @endif
    @if($jsFile)
    <link rel="preload" href="{{ asset('build/' . $jsFile) }}" as="script">
    @endif

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
