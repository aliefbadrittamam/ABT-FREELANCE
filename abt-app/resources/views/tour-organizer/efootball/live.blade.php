<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Slot Turnamen eFootball Mobile — ABT-FREELANCE</title>

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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .neon-grid-bg {
            background-color: #ffffff;
            background-image: 
                linear-gradient(to right, rgba(232, 255, 0, 0.09) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(232, 255, 0, 0.09) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="neon-grid-bg text-on-surface font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- Navbar Publik -->
    <header class="bg-white/90 backdrop-blur-sm border-b border-border-subtle sticky top-0 z-30 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-primary-container flex items-center justify-center font-black text-xs text-on-surface shadow-xs">
                    ABT
                </div>
                <div>
                    <span class="text-sm font-extrabold text-on-surface tracking-tight block">ABT eFootball Tournament</span>
                    <span class="text-[10px] text-secondary font-semibold uppercase tracking-wider">Live Slot Monitoring</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Live Update
                </span>
            </div>
        </div>
    </header>

    <!-- Main Live Content -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 w-full flex-1 space-y-8">
        <!-- Hero Title -->
        <div class="text-center max-w-xl mx-auto space-y-2">
            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary-container/25 text-on-surface text-xs font-bold border border-primary-container/40">
                <span class="material-symbols-outlined text-sm">sports_esports</span>
                eFootball Mobile Fast Tournament
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-on-surface tracking-tight">Ketersediaan Slot Tim Peserta</h1>
            <p class="text-xs sm:text-sm text-secondary leading-relaxed">
                Pantau ketersediaan slot turnamen secara langsung. Untuk registrasi dan kunci slot, hubungi Admin via WhatsApp.
            </p>
        </div>

        @if($activeTournaments->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-2xl border border-border-subtle p-12 text-center max-w-md mx-auto shadow-xs">
            <span class="material-symbols-outlined text-5xl text-secondary/30 mb-2">event_busy</span>
            <h3 class="text-base font-bold text-on-surface">Belum Ada Sesi Turnamen Buka</h3>
            <p class="text-xs text-secondary mt-1">Sesi baru akan segera dibuka oleh Admin. Pantau info di grup WhatsApp!</p>
        </div>
        @else
        <!-- Sesi Aktif Grid (Tabel Responsif Per Sesi) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($activeTournaments as $tournament)
            @php 
                $participantsMap = $tournament->participants->keyBy('slot_number');
                $isFull = $tournament->isFull();
            @endphp
            <div class="bg-white rounded-2xl border border-border-subtle p-5 sm:p-6 shadow-sm flex flex-col justify-between relative overflow-hidden"
                 x-data="{ showList: true }">
                
                <!-- Accent Bar -->
                <div class="absolute top-0 left-0 w-full h-1.5 {{ $isFull ? 'bg-red-500' : 'bg-primary-container' }}"></div>

                <div>
                    <!-- Sesi Header -->
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <span class="px-2.5 py-0.5 rounded bg-gray-100 text-on-surface text-[10px] font-extrabold uppercase tracking-wider">
                                {{ $tournament->session_label }}
                            </span>
                            <h2 class="text-lg font-black text-on-surface tracking-tight mt-1">{{ $tournament->name }}</h2>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase {{ $isFull ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                            {{ $isFull ? 'SLOT PENUH' : 'BUKA (' . $tournament->remaining_slots_count . ' SLOT LAGI)' }}
                        </span>
                    </div>

                    <!-- Specs Bar -->
                    <div class="grid grid-cols-2 gap-2 p-3 bg-surface rounded-xl border border-border-subtle mb-4 text-xs">
                        <div>
                            <span class="text-[10px] text-secondary font-semibold uppercase block">Biaya Registrasi:</span>
                            <strong class="text-sm font-bold font-mono text-on-surface">Rp {{ number_format($tournament->entry_fee, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span class="text-[10px] text-secondary font-semibold uppercase block">Hadiah Juara 1:</span>
                            <strong class="text-sm font-bold font-mono text-emerald-600">Rp {{ number_format($tournament->prize_pool, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <!-- Slot List Table -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-[11px] font-bold text-secondary uppercase tracking-wider mb-2">
                            <span>Slot Tim ({{ $tournament->filled_slots_count }}/{{ $tournament->max_slots }})</span>
                            <button type="button" @click="showList = !showList" class="text-primary hover:underline" x-text="showList ? 'Tutup Detail' : 'Buka Detail'"></button>
                        </div>

                        <div x-show="showList" class="space-y-1.5" x-transition>
                            @for($s = 1; $s <= $tournament->max_slots; $s++)
                            @php $team = $participantsMap[$s] ?? null; @endphp
                            <div class="px-3 py-2 rounded-lg border text-xs flex items-center justify-between {{ $team ? 'bg-white border-border-subtle' : 'bg-gray-50 border-dashed border-gray-200 text-secondary' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-black {{ $team ? 'bg-on-surface text-white' : 'bg-gray-200 text-secondary' }}">
                                        {{ $s }}
                                    </span>
                                    <span class="font-bold {{ $team ? 'text-on-surface' : 'italic text-secondary/60' }}">
                                        {{ $team ? $team->team_name : '[ SLOT TERSEDIA ]' }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-bold uppercase {{ $team ? 'text-emerald-600' : 'text-primary-container bg-black px-1.5 py-0.5 rounded' }}">
                                    {{ $team ? 'TERDAFTAR' : 'TERSEDIA' }}
                                </span>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Registration CTA Button -->
                <div class="pt-4 mt-4 border-t border-border-subtle flex justify-between items-center">
                    <span class="text-xs text-secondary">
                        {{ $isFull ? 'Slot telah terkunci.' : 'Daftar sekarang sebelum penuh!' }}
                    </span>
                    @if(!$isFull)
                    @php
                        $waText = "Halo Admin ABT, saya ingin mendaftarkan tim untuk Turnamen eFootball {$tournament->name} ({$tournament->session_label}). Apakah masih bisa daftar?";
                        $waUrl = "https://api.whatsapp.com/send?phone=6288989504780&text=" . urlencode($waText);
                    @endphp
                    <a href="{{ $waUrl }}" target="_blank"
                       class="px-4 py-2 bg-[#25D366] text-white text-xs font-bold rounded-lg hover:brightness-95 transition flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        Hubungi Admin
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </main>

    <!-- Footer Publik -->
    <footer class="bg-white border-t border-border-subtle py-4 text-center text-xs text-secondary">
        <p>&copy; {{ date('Y') }} ABT-FREELANCE eFootball Tournament. All rights reserved.</p>
    </footer>

</body>
</html>
