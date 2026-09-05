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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
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
<body class="bg-surface dark:bg-[#121212] text-on-surface dark:text-[#f0f0f0] font-sans text-sm overflow-x-hidden transition-colors duration-200 antialiased">
    <!-- Top Bar with Hamburger for Mobile -->
    <header class="bg-white/95 dark:bg-[#1a1a1a]/95 backdrop-blur-sm fixed top-0 right-0 w-full lg:w-[calc(100%-260px)] h-16 border-b border-border-subtle dark:border-[#2a2a2a] flex items-center justify-between px-4 sm:px-8 z-20 transition-colors duration-200">
        <div class="flex items-center gap-3">
            <!-- Mobile Hamburger Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 rounded-lg border border-border-subtle dark:border-[#333] lg:hidden text-on-surface dark:text-white hover:bg-surface-variant dark:hover:bg-[#252525]">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>

            @if(file_exists(storage_path('app/public/assets/logo.png')))
            <img src="{{ asset('storage/assets/logo.png') }}?v={{ time() }}" alt="ABT" class="w-8 h-8 rounded-lg object-contain border border-border-subtle dark:border-[#333] bg-white p-0.5">
            @endif
            <h2 class="text-base sm:text-lg font-bold text-on-surface dark:text-white tracking-tight truncate">@yield('header', 'ABT-FREELANCE')</h2>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Quick Action "Buat Invoice" Button in Top Bar (Desktop) -->
            <a href="{{ route('invoices.create') }}" class="hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 bg-primary-container text-on-surface text-xs font-bold rounded-lg shadow-sm hover:brightness-95 transition-all">
                <span class="material-symbols-outlined text-base">add</span>
                Invoice Baru
            </a>

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
            <div class="text-xs text-secondary dark:text-gray-400 hidden md:block pl-2 border-l border-border-subtle dark:border-[#333]">
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
        
        <!-- Sidebar Brand Header Card -->
        <div class="mb-6 p-2 rounded-xl bg-white/60 dark:bg-[#222]/60 border border-border-subtle/70 dark:border-[#2a2a2a] flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                @if(file_exists(storage_path('app/public/assets/logo.png')))
                <img src="{{ asset('storage/assets/logo.png') }}?v={{ time() }}" alt="ABT Logo" class="w-10 h-10 rounded-lg object-contain border border-border-subtle dark:border-[#333] bg-white p-0.5 shadow-sm shrink-0">
                @endif
                <div>
                    <h1 class="text-sm font-extrabold text-on-surface dark:text-white tracking-tight leading-tight">ABT-FREELANCE</h1>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-status-lunas animate-pulse"></span>
                        <span class="text-on-surface-variant dark:text-gray-400 text-[10px] font-semibold tracking-wider uppercase opacity-80">Local Server</span>
                    </div>
                </div>
            </div>
            <!-- Close Button for Mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden p-1 text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <!-- Navigation Menu -->
        <ul class="flex-1 space-y-1 mt-1">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-primary dark:bg-primary text-on-primary font-bold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525] font-medium' }}">
                    <span class="material-symbols-outlined text-xl" {{ request()->routeIs('dashboard') ? "style=font-variation-settings:'FILL'1" : '' }}>dashboard</span>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-primary dark:bg-primary text-on-primary font-bold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525] font-medium' }}">
                    <span class="material-symbols-outlined text-xl" {{ request()->routeIs('categories.*') ? "style=font-variation-settings:'FILL'1" : '' }}>category</span>
                    Kategori Jasa
                </a>
            </li>
            <li>
                <a href="{{ route('invoices.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('invoices.*') ? 'bg-primary dark:bg-primary text-on-primary font-bold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525] font-medium' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-xl" {{ request()->routeIs('invoices.*') ? "style=font-variation-settings:'FILL'1" : '' }}>receipt_long</span>
                        Invoice
                    </div>
                </a>
            </li>
            <li>
                <a href="{{ route('testimonials.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('testimonials.*') ? 'bg-primary dark:bg-primary text-on-primary font-bold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525] font-medium' }}">
                    <span class="material-symbols-outlined text-xl" {{ request()->routeIs('testimonials.*') ? "style=font-variation-settings:'FILL'1" : '' }}>reviews</span>
                    Testimoni
                </a>
            </li>
            <li>
                <a href="{{ route('payment.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('payment.*') ? 'bg-primary dark:bg-primary text-on-primary font-bold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525] font-medium' }}">
                    <span class="material-symbols-outlined text-xl" {{ request()->routeIs('payment.*') ? "style=font-variation-settings:'FILL'1" : '' }}>payments</span>
                    Pembayaran
                </a>
            </li>

            <!-- Tour Organizer with Submenu -->
            <li x-data="{ open: {{ request()->routeIs('tour-organizer.*') ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('tour-organizer.*') ? 'bg-primary dark:bg-primary text-on-primary font-bold shadow-sm' : 'text-secondary dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525] font-medium' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-xl" {{ request()->routeIs('tour-organizer.*') ? "style=font-variation-settings:'FILL'1" : '' }}>luggage</span>
                        Tour Organizer
                    </div>
                    <span class="material-symbols-outlined text-base transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>
                <!-- Submenu items -->
                <ul x-show="open" x-collapse x-cloak class="pl-10 pr-2 py-1.5 space-y-1">
                    <li>
                        <a href="{{ route('tour-organizer.index') }}" 
                           class="block px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('tour-organizer.index') ? 'text-primary dark:text-primary-container font-bold bg-primary-container/15' : 'text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white' }}">
                            Overview
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tour-organizer.efootball') }}" 
                           class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('tour-organizer.efootball') ? 'text-primary dark:text-primary-container font-bold bg-primary-container/15' : 'text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white' }}">
                            <span class="material-symbols-outlined text-sm">sports_esports</span>
                            eFootball Mobile
                        </a>
                    </li>
                </ul>
            </li>
        </ul>

        <div class="mt-auto pt-4 border-t border-border-subtle dark:border-[#2a2a2a] flex items-center justify-between px-2">
            <span class="text-[11px] text-secondary dark:text-gray-500 font-medium">ABT v1.0</span>
            <span class="inline-flex items-center gap-1 text-[10px] bg-primary-container/20 text-on-surface dark:text-primary-container px-2 py-0.5 rounded font-bold">PRO</span>
        </div>
    </nav>

    <!-- Main Content Area (Responsive margins and paddings, safe from fixed topbar) -->
    <main class="ml-0 lg:ml-sidebar pt-20 sm:pt-24 min-h-screen px-4 sm:px-6 lg:px-8 pb-12 transition-all duration-300">
        <div class="max-w-7xl mx-auto">
            <!-- Flash Messages & Error Event Handlers -->
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl text-xs sm:text-sm flex items-start justify-between gap-3 shadow-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)" x-transition>
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-lg sm:text-xl shrink-0">check_circle</span>
                    <div class="space-y-0.5">
                        <strong class="font-bold">Berhasil!</strong>
                        <p class="leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
                <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-white transition">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl text-xs sm:text-sm flex items-start justify-between gap-3 shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-lg sm:text-xl shrink-0">error</span>
                    <div class="space-y-0.5">
                        <strong class="font-bold">Pemberitahuan Error / Gagal:</strong>
                        <p class="leading-relaxed font-mono text-[11px] sm:text-xs bg-white/50 dark:bg-black/20 p-2 rounded-lg mt-1 border border-red-200/50 dark:border-red-900/30">{{ session('error') }}</p>
                    </div>
                </div>
                <button type="button" @click="show = false" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-white transition">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
            @endif

            @if(session('warning'))
            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-300 rounded-xl text-xs sm:text-sm flex items-start justify-between gap-3 shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-lg sm:text-xl shrink-0">warning</span>
                    <div class="space-y-0.5">
                        <strong class="font-bold">Peringatan:</strong>
                        <p class="leading-relaxed font-mono text-[11px] sm:text-xs bg-white/50 dark:bg-black/20 p-2 rounded-lg mt-1 border border-amber-200/50 dark:border-amber-900/30">{{ session('warning') }}</p>
                    </div>
                </div>
                <button type="button" @click="show = false" class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-white transition">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl text-xs sm:text-sm flex items-start justify-between gap-3 shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-lg sm:text-xl shrink-0">report</span>
                    <div class="space-y-1">
                        <strong class="font-bold">Terdapat kesalahan pada input form:</strong>
                        <ul class="list-disc list-inside space-y-0.5 text-xs text-red-700 dark:text-red-400">
                            @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" @click="show = false" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-white transition">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
