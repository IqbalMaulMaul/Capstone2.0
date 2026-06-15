<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ config('hotel.name', 'Hotel Room Service') }}</title>
    
    <!-- PWA Setup -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1a1a2e">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="h-full font-sans antialiased text-gray-900">
    
    <!-- Layout Wrapper -->
    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        
        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 sm:hidden" @click="sidebarOpen = false" x-transition.opacity></div>

        <!-- Sidebar -->
        <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#1a1a2e] text-white transition-transform duration-300 ease-in-out sm:translate-x-0 sm:flex sm:flex-col">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-center h-16 shrink-0 bg-[#161626] px-4">
                <span class="text-lg font-bold text-white tracking-wider">HOTEL ADMIN</span>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
                @if(Auth::user()->role === 'owner')
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-[#c9a84c] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                    Dashboard
                </a>
                <a href="{{ route('admin.menus.index') }}" class="{{ request()->routeIs('admin.menus.*') ? 'bg-[#c9a84c] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                    Manajemen Menu
                </a>
                <a href="{{ route('admin.rooms.index') }}" class="{{ request()->routeIs('admin.rooms.*') ? 'bg-[#c9a84c] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                    Kamar & QR Code
                </a>
                @endif
                
                @if(in_array(Auth::user()->role, ['kitchen', 'owner']))
                <a href="{{ route('admin.kitchen.index') }}" class="{{ request()->routeIs('admin.kitchen.index') ? 'bg-[#c9a84c] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                    Layar Dapur (Kitchen)
                </a>
                @endif

                @if(in_array(Auth::user()->role, ['finance', 'owner']))
                <div class="pt-4 mt-4 border-t border-gray-700">
                    <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Keuangan</p>
                    <a href="{{ route('admin.finance.dashboard') }}" class="{{ request()->routeIs('admin.finance.dashboard') ? 'bg-[#c9a84c] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Dashboard Finance
                    </a>
                    <a href="{{ route('admin.finance.payments') }}" class="{{ request()->routeIs('admin.finance.payments') ? 'bg-[#c9a84c] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Daftar Pembayaran
                    </a>
                    <a href="{{ route('admin.finance.orders') }}" class="{{ request()->routeIs('admin.finance.orders*') ? 'bg-[#c9a84c] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Riwayat Pesanan
                    </a>
                </div>
                @endif
            </nav>

            <!-- User Info -->
            <div class="shrink-0 bg-[#161626] p-4 border-t border-gray-800">
                <div class="flex items-center">
                    <div>
                        <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs font-medium text-gray-400 capitalize">{{ Auth::user()->role }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left text-sm text-gray-400 hover:text-white">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content Wrapper -->
        <div class="flex flex-col flex-1 sm:pl-64 h-screen">
            
            <!-- Topbar (Mobile) -->
            <div class="sticky top-0 z-10 flex h-16 shrink-0 bg-white border-b border-gray-200 sm:hidden">
                <button type="button" class="px-4 text-gray-500 focus:outline-none" @click="sidebarOpen = true">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <div class="flex flex-1 items-center justify-between px-4">
                    <span class="text-lg font-bold text-gray-900">HOTEL ADMIN</span>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-8">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 p-4 rounded-lg border border-green-200">
                        <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 p-4 rounded-lg border border-red-200">
                        <p class="text-red-800 text-sm font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
    
    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
</body>
</html>
