<!DOCTYPE html>
<html lang="id" x-data="{
    darkMode: localStorage.getItem('theme') === 'dark',
    sidebarOpen: false,
    toggleTheme() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" x-init="if (darkMode) document.documentElement.classList.add('dark')">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ABT-FREELANCE')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'surface': '#f9f9f9',
                        'surface-variant': '#e2e2e2',
                        'surface-container-low': '#f3f3f4',
                        'surface-container': '#eeeeee',
                        'surface-container-high': '#e8e8e8',
                        'on-surface': '#1a1c1c',
                        'on-surface-variant': '#464832',
                        'primary': '#5a6400',
                        'on-primary': '#ffffff',
                        'primary-container': '#e8ff00',
                        'on-primary-container': '#697400',
                        'primary-fixed-dim': '#bed100',
                        'secondary': '#5d5e60',
                        'secondary-container': '#dfdfe0',
                        'on-secondary-container': '#616364',
                        'border-subtle': '#E4E4E7',
                        'status-lunas': '#22C55E',
                        'status-dp': '#3B82F6',
                        'status-pending': '#F59E0B',
                        'inverse-surface': '#2f3131',
                        'inverse-on-surface': '#f0f1f1',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                    spacing: {
                        'sidebar': '260px',
                    },
                    borderRadius: {
                        'xl': '0.75rem',
                    },
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface dark:bg-[#121212] text-on-surface dark:text-[#f0f0f0] font-sans text-sm overflow-x-hidden transition-colors duration-200">
    <!-- Top Bar with Hamburger for Mobile -->
    <header class="bg-white dark:bg-[#1a1a1a] fixed top-0 right-0 w-full lg:w-[calc(100%-260px)] h-16 border-b border-border-subtle dark:border-[#2a2a2a] flex items-center justify-between px-4 sm:px-8 z-20 transition-colors duration-200">
        <div class="flex items-center gap-3">
            <!-- Mobile Hamburger Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 rounded-lg border border-border-subtle dark:border-[#333] lg:hidden text-on-surface dark:text-white hover:bg-surface-variant dark:hover:bg-[#252525]">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>

            @if(file_exists(storage_path('app/public/assets/logo.png')))
            <img src="{{ asset('storage/assets/logo.png') }}?v={{ time() }}" alt="ABT" class="w-8 h-8 rounded-lg object-contain border border-border-subtle dark:border-[#333] bg-white p-0.5">
            @endif
            <h2 class="text-base sm:text-lg font-semibold text-on-surface dark:text-white tracking-tight truncate">@yield('header', 'ABT-FREELANCE')</h2>
        </div>
        
        <div class="flex items-center gap-2 sm:gap-4">
            <!-- Dark / Light Mode Toggle Button -->
            <button @click="toggleTheme()" 
                    type="button"
                    class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg bg-surface-container-low dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-[#f0f0f0] text-xs font-semibold hover:border-primary transition-all shadow-sm"
                    :title="darkMode ? 'Beralih ke Light Mode' : 'Beralih ke Dark Mode'">
                <span class="material-symbols-outlined text-base text-status-pending" x-show="!darkMode">light_mode</span>
                <span class="material-symbols-outlined text-base text-primary-container" x-show="darkMode" x-cloak>dark_mode</span>
                <span class="hidden xs:inline" x-text="darkMode ? 'Dark' : 'Light'"></span>
            </button>

            <!-- Date -->
            <div class="text-xs text-secondary dark:text-gray-400 hidden md:block">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </header>

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-black/50 z-30 lg:hidden backdrop-blur-sm"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
    </div>

    <!-- Sidebar with Logo (Responsive Drawer on Mobile, Fixed on Desktop) -->
    <nav class="bg-secondary-container dark:bg-[#181818] w-sidebar h-screen fixed left-0 top-0 border-r border-border-subtle dark:border-[#2a2a2a] flex flex-col p-4 z-40 lg:z-30 transition-transform duration-300 ease-in-out"
         :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'">
        
        <!-- Sidebar Brand Header -->
        <div class="mb-6 px-2 mt-2 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if(file_exists(storage_path('app/public/assets/logo.png')))
                <img src="{{ asset('storage/assets/logo.png') }}?v={{ time() }}" alt="ABT Logo" class="w-12 h-12 rounded-xl object-contain border border-border-subtle dark:border-[#333] bg-white p-1 shadow-sm shrink-0">
                @endif
                <div>
                    <h1 class="text-base font-bold text-on-surface dark:text-white tracking-tight leading-tight">ABT-FREELANCE</h1>
                    <p class="text-on-surface-variant dark:text-gray-400 text-[10px] font-semibold tracking-wider uppercase opacity-70 mt-0.5">Management Tool</p>
                </div>
            </div>
            <!-- Close Button for Mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden p-1 text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        <!-- Navigation Menu -->
        <ul class="flex-1 space-y-1 mt-2">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-primary dark:bg-primary text-on-primary font-semibold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
                    <span class="material-symbols-outlined text-xl" {{ request()->routeIs('dashboard') ? "style=font-variation-settings:'FILL'1" : '' }}>dashboard</span>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-primary dark:bg-primary text-on-primary font-semibold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
                    <span class="material-symbols-outlined text-xl" {{ request()->routeIs('categories.*') ? "style=font-variation-settings:'FILL'1" : '' }}>category</span>
                    Kategori
                </a>
            </li>
            <li>
                <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('invoices.*') ? 'bg-primary dark:bg-primary text-on-primary font-semibold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
                    <span class="material-symbols-outlined text-xl" {{ request()->routeIs('invoices.*') ? "style=font-variation-settings:'FILL'1" : '' }}>receipt_long</span>
                    Invoice
                </a>
            </li>
            <li>
                <a href="{{ route('testimonials.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('testimonials.*') ? 'bg-primary dark:bg-primary text-on-primary font-semibold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
                    <span class="material-symbols-outlined text-xl" {{ request()->routeIs('testimonials.*') ? "style=font-variation-settings:'FILL'1" : '' }}>reviews</span>
                    Testimoni
                </a>
            </li>
            <li>
                <a href="{{ route('payment.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('payment.*') ? 'bg-primary dark:bg-primary text-on-primary font-semibold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
                    <span class="material-symbols-outlined text-xl" {{ request()->routeIs('payment.*') ? "style=font-variation-settings:'FILL'1" : '' }}>payments</span>
                    Pembayaran
                </a>
            </li>
        </ul>

        <div class="mt-auto pt-4 border-t border-border-subtle dark:border-[#2a2a2a]">
            <p class="text-xs text-on-surface-variant/50 dark:text-gray-500 px-2">v1.0 &mdash; Local App</p>
        </div>
    </nav>

    <!-- Main Content Area (Responsive margins and paddings) -->
    <main class="ml-0 lg:ml-sidebar pt-16 min-h-screen p-4 sm:p-6 lg:p-8 transition-all duration-300">
        <div class="max-w-7xl mx-auto">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-status-lunas/10 border border-status-lunas/20 text-status-lunas rounded-xl text-sm flex items-center gap-2" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
                <span class="material-symbols-outlined text-lg">check_circle</span>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-6 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl text-sm flex items-center gap-2" x-data="{ show: true }" x-show="show">
                <span class="material-symbols-outlined text-lg">error</span>
                {{ session('error') }}
            </div>
            @endif
            @if(session('warning'))
            <div class="mb-6 px-4 py-3 bg-status-pending/10 border border-status-pending/20 text-status-pending rounded-xl text-sm flex items-center gap-2" x-data="{ show: true }" x-show="show">
                <span class="material-symbols-outlined text-lg">warning</span>
                {{ session('warning') }}
            </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
