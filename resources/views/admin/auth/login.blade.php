<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ config('hotel.name', 'Hotel Room Service') }}</title>
    <!-- PWA Setup -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1a1a2e">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="h-full font-sans antialiased flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
        <div>
            <h2 class="mt-2 text-center text-3xl font-bold tracking-tight text-gray-900">Admin Panel</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Silakan login menggunakan akun staf Anda
            </p>
        </div>
        
        <!-- PWA Install Button (Hidden by default, shown by JS if available) -->
        <div id="pwa-install-container" class="hidden mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-center">
            <p class="text-xs text-yellow-800 mb-2 font-medium">Buka aplikasi lebih cepat tanpa browser!</p>
            <button id="pwa-install-btn" type="button" class="w-full flex justify-center items-center gap-2 rounded-md bg-[#c9a84c] px-4 py-2 text-sm font-bold text-white hover:bg-[#b09038] transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Install Aplikasi ke HP
            </button>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            
            <div class="space-y-4 rounded-md shadow-sm">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#c9a84c] focus:ring-[#c9a84c] sm:text-sm px-4 py-2 border">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#c9a84c] focus:ring-[#c9a84c]">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-[#1a1a2e] py-2.5 px-4 text-sm font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1a1a2e] focus:ring-offset-2 transition-colors">
                    Sign in
                </button>
            </div>
        </form>
        
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-center text-gray-500">
                Gunakan kredensial yang dibuat oleh seeder.<br>
                Owner: owner@hotel.com<br>
                Kitchen: kitchen@hotel.com<br>
                Finance: finance@hotel.com<br>
                Pass: password
            </p>
        </div>
    </div>
    
    <!-- PWA Service Worker & Install Logic -->
    <script>
        // 1. Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => console.log('SW registered'))
                    .catch(err => console.log('SW failed', err));
            });
        }

        // 2. Handle PWA Install Prompt
        let deferredPrompt;
        const installContainer = document.getElementById('pwa-install-container');
        const installBtn = document.getElementById('pwa-install-btn');

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later.
            deferredPrompt = e;
            // Update UI to notify the user they can add to home screen
            installContainer.classList.remove('hidden');
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                // Show the install prompt
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                    installContainer.classList.add('hidden'); // Hide button after install
                }
                deferredPrompt = null;
            }
        });
        
        window.addEventListener('appinstalled', () => {
            // Hide the app-provided install promotion
            installContainer.classList.add('hidden');
            console.log('PWA was installed');
        });
    </script>
</body>
</html>
