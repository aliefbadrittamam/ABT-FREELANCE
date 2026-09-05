<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin — ABT-FREELANCE</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#000000',
                        'primary-container': '#E8FF00',
                        'on-surface': '#111111',
                        'secondary': '#666666',
                        'border-subtle': '#e5e7eb',
                        'surface': '#f8fafc',
                        'status-lunas': '#059669',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['Space Grotesk', 'monospace'],
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .neon-grid-bg {
            background-color: #ffffff;
            background-image: 
                linear-gradient(to right, rgba(232, 255, 0, 0.08) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(232, 255, 0, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .neon-corner-tl { position: absolute; top: -1px; left: -1px; width: 14px; height: 14px; border-top: 2px solid rgba(232, 255, 0, 0.8); border-left: 2px solid rgba(232, 255, 0, 0.8); }
        .neon-corner-tr { position: absolute; top: -1px; right: -1px; width: 14px; height: 14px; border-top: 2px solid rgba(232, 255, 0, 0.8); border-right: 2px solid rgba(232, 255, 0, 0.8); }
        .neon-corner-bl { position: absolute; bottom: -1px; left: -1px; width: 14px; height: 14px; border-bottom: 2px solid rgba(232, 255, 0, 0.8); border-left: 2px solid rgba(232, 255, 0, 0.8); }
        .neon-corner-br { position: absolute; bottom: -1px; right: -1px; width: 14px; height: 14px; border-bottom: 2px solid rgba(232, 255, 0, 0.8); border-right: 2px solid rgba(232, 255, 0, 0.8); }
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-weight: normal;
            font-style: normal;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            font-feature-settings: 'liga';
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="neon-grid-bg text-on-surface font-sans antialiased min-h-screen flex flex-col justify-between" x-data="{ showPassword: false }">

    <!-- Top Simple Bar -->
    <header class="w-full px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            @if(file_exists(storage_path('app/public/assets/logo.png')))
            <img src="{{ asset('storage/assets/logo.png') }}" alt="ABT" class="w-7 h-7 rounded-lg object-contain border border-border-subtle p-0.5 bg-white shadow-xs">
            @endif
            <span class="text-sm font-black text-on-surface tracking-tight">ABT-FREELANCE</span>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-wider text-secondary bg-gray-100 px-2.5 py-1 rounded-full border border-border-subtle">
            Admin Portal v1.0
        </span>
    </header>

    <!-- Center Login Card -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md relative">
            
            <!-- Neon Accented Card Box -->
            <div class="bg-white text-on-surface border border-border-subtle shadow-[0_15px_35px_-5px_rgba(0,0,0,0.08)] rounded-2xl p-7 sm:p-9 relative overflow-hidden">
                <!-- Neon Corner Accents -->
                <div class="neon-corner-tl"></div>
                <div class="neon-corner-tr"></div>
                <div class="neon-corner-bl"></div>
                <div class="neon-corner-br"></div>

                <!-- Top Yellow Line Accent -->
                <div class="absolute top-0 left-0 w-full h-1.5 bg-primary-container"></div>

                <!-- Brand & Greeting Header -->
                <div class="text-center mb-7">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-primary-container/25 border border-primary-container flex items-center justify-center mb-3 shadow-xs">
                        <span class="material-symbols-outlined text-2xl text-on-surface font-bold">admin_panel_settings</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-on-surface tracking-tight">Login Dashboard Admin</h1>
                    <p class="text-xs text-secondary mt-1">Masuk untuk mengelola invoice, pembayaran & turnamen.</p>
                </div>

                <!-- Session Flash Messages -->
                @if(session('success'))
                <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs space-y-1">
                    <div class="flex items-center gap-1.5 font-bold">
                        <span class="material-symbols-outlined text-base text-red-600">error</span>
                        <span>Autentikasi Gagal</span>
                    </div>
                    @foreach($errors->all() as $err)
                    <p class="leading-relaxed pl-5">{{ $err }}</p>
                    @endforeach
                </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Email or Username -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary mb-1.5">Email atau Username</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary/60 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-lg">person</span>
                            </span>
                            <input type="text" name="email" value="{{ old('email') }}" required autofocus placeholder="aliefbadrittamam@gmail.com"
                                   class="w-full pl-10 pr-3.5 py-2.5 bg-[#fafafa] border border-border-subtle rounded-xl text-xs sm:text-sm font-medium text-on-surface focus:bg-white focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                        </div>
                    </div>

                    <!-- Password with Show/Hide Toggle -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary">Password</label>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary/60 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-lg">lock</span>
                            </span>
                            <input :type="showPassword ? 'text' : 'password'" name="password" required placeholder="••••••••"
                                   class="w-full pl-10 pr-10 py-2.5 bg-[#fafafa] border border-border-subtle rounded-xl text-xs sm:text-sm font-medium text-on-surface focus:bg-white focus:ring-2 focus:ring-primary focus:border-primary outline-none transition font-mono">
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary hover:text-on-surface p-1 transition"
                                    :title="showPassword ? 'Sembunyikan password' : 'Lihat password'">
                                <span class="material-symbols-outlined text-base" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-black focus:ring-black border-gray-300">
                            <span class="text-xs font-semibold text-secondary">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-2.5 px-4 bg-primary-container text-on-surface font-extrabold text-xs sm:text-sm rounded-xl hover:brightness-95 transition shadow-xs flex items-center justify-center gap-2 tracking-wide mt-2">
                        <span class="material-symbols-outlined text-base">login</span>
                        Masuk ke Dashboard
                    </button>
                </form>

                <!-- Security Footer Badge -->
                <div class="mt-6 pt-5 border-t border-border-subtle text-center">
                    <p class="text-[10px] text-secondary flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-xs text-status-lunas">lock</span>
                        Protected by Rate Limiter & Bcrypt Session Auth
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-xs text-secondary border-t border-border-subtle bg-white">
        &copy; {{ date('Y') }} ABT-FREELANCE Management System. All rights reserved.
    </footer>

</body>
</html>
