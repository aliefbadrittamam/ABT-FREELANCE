<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Slot Turnamen eFootball Mobile — ABT-FREELANCE</title>

    <!-- Favicon eFootball -->
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/logo-abt-efootball-tur.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('assets/logo-abt-efootball-tur.jpg') }}">

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
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-weight: normal;
            font-style: normal;
            font-size: 20px;
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
<body class="neon-grid-bg text-on-surface font-sans antialiased min-h-screen flex flex-col justify-between"
      x-data="liveTournamentViewer()">

    <!-- Navbar Publik -->
    <header class="bg-white/90 backdrop-blur-sm border-b border-border-subtle sticky top-0 z-30 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/logo-abt-efootball-tur.jpg') }}" alt="eFootball Logo" class="w-10 h-10 rounded-xl object-contain border border-border-subtle p-0.5 bg-white shadow-xs shrink-0">
                <div>
                    <span class="text-sm font-black text-on-surface tracking-tight block leading-tight">ABT eFootball Tournament</span>
                    <span class="text-[10px] text-secondary font-semibold uppercase tracking-wider">Live Slot Monitoring</span>
                </div>
            </div>

            <!-- Live Status Indicator -->
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span class="hidden sm:inline">Live Sync Aktif:</span> <span x-text="lastSync"></span>
                </span>
            </div>
        </div>
    </header>

    <!-- Main Live Content -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 w-full flex-1 space-y-8">
        <!-- Hero Title -->
        <div class="text-center max-w-xl mx-auto space-y-2">
            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary-container/25 text-on-surface text-xs font-bold border border-primary-container/40">
                <span class="material-symbols-outlined text-sm">sports_soccer</span>
                eFootball Mobile Fast Tournament
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-on-surface tracking-tight">Ketersediaan Slot Tim Peserta</h1>
            <p class="text-xs sm:text-sm text-secondary leading-relaxed">
                Halaman ini <strong>otomatis terupdate secara real-time tanpa perlu me-reload browser</strong>. Untuk registrasi dan kunci slot, silakan bayar via QRIS lalu hubungi Admin via WhatsApp.
            </p>
        </div>

        <!-- Empty State (When no open sessions) -->
        <template x-if="tournaments.length === 0">
            <div class="bg-white rounded-2xl border border-border-subtle p-12 text-center max-w-md mx-auto shadow-xs">
                <span class="material-symbols-outlined text-5xl text-secondary/30 mb-2">event_busy</span>
                <h3 class="text-base font-bold text-on-surface">Belum Ada Sesi Turnamen Buka</h3>
                <p class="text-xs text-secondary mt-1">Sesi baru akan segera dibuka oleh Admin. Pantau info di grup WhatsApp!</p>
            </div>
        </template>

        <!-- Sesi Aktif Grid (Auto-Refreshed Real-Time Without Page Reload) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="tournaments.length > 0">
            <template x-for="tournament in tournaments" :key="tournament.id">
                <div class="bg-white rounded-2xl border border-border-subtle p-5 sm:p-6 shadow-sm flex flex-col justify-between relative overflow-hidden transition-all"
                     x-data="{ viewTab: 'slots', showList: true }">
                    
                    <!-- Accent Bar -->
                    <div class="absolute top-0 left-0 w-full h-1.5" :class="tournament.is_full ? 'bg-red-500' : 'bg-primary-container'"></div>

                    <div>
                        <!-- Sesi Header -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <span class="px-2.5 py-0.5 rounded bg-gray-100 text-on-surface text-[10px] font-extrabold uppercase tracking-wider" x-text="tournament.session_label"></span>
                                <h2 class="text-lg font-black text-on-surface tracking-tight mt-1" x-text="tournament.name"></h2>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase shadow-2xs"
                                  :class="tournament.is_full ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200'"
                                  x-text="tournament.is_full ? 'SLOT PENUH' : 'BUKA (' + tournament.remaining_slots_count + ' SLOT LAGI)'">
                            </span>
                        </div>

                        <!-- Specs Bar -->
                        <div class="grid grid-cols-2 gap-2 p-3 bg-surface rounded-xl border border-border-subtle mb-4 text-xs">
                            <div>
                                <span class="text-[10px] text-secondary font-semibold uppercase block">Biaya Registrasi:</span>
                                <strong class="text-sm font-bold font-mono text-on-surface" x-text="'Rp ' + tournament.formatted_entry_fee"></strong>
                            </div>
                            <div>
                                <span class="text-[10px] text-secondary font-semibold uppercase block">Hadiah Juara 1:</span>
                                <strong class="text-sm font-bold font-mono text-emerald-600" x-text="'Rp ' + tournament.formatted_prize_pool"></strong>
                            </div>
                        </div>

                        <!-- View Tab Switcher (Slots vs Bagan) -->
                        <div class="flex items-center gap-2 mb-3 border-b border-border-subtle pb-2">
                            <button type="button" @click="viewTab = 'slots'" 
                                    :class="viewTab === 'slots' ? 'bg-on-surface text-white font-bold' : 'text-secondary hover:text-on-surface'"
                                    class="px-2.5 py-1 text-[11px] rounded-lg transition flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">format_list_numbered</span>
                                <span>Slot (<span x-text="tournament.filled_slots_count + '/' + tournament.max_slots"></span>)</span>
                            </button>

                            <button type="button" @click="viewTab = 'bracket'" 
                                    :class="viewTab === 'bracket' ? 'bg-on-surface text-white font-bold' : 'text-secondary hover:text-on-surface'"
                                    class="px-2.5 py-1 text-[11px] rounded-lg transition flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">account_tree</span>
                                <span>Bagan</span>
                                <template x-if="tournament.matches && Object.keys(tournament.matches).length > 0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary-container"></span>
                                </template>
                            </button>
                        </div>

                        <!-- TAB 1: Slot List Table -->
                        <div x-show="viewTab === 'slots'" class="space-y-2">
                            <div class="space-y-1.5 max-h-[280px] overflow-y-auto pr-1">
                                <template x-for="s in tournament.max_slots" :key="s">
                                    <div class="px-3 py-2 rounded-lg border text-xs flex items-center justify-between transition-all"
                                         :class="tournament.participants[s] ? (tournament.participants[s].is_winner ? 'bg-amber-50/70 border-amber-300' : 'bg-white border-border-subtle') : 'bg-gray-50 border-dashed border-gray-200 text-secondary'">
                                        
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-black shrink-0"
                                                  :class="tournament.participants[s] ? (tournament.participants[s].is_winner ? 'bg-amber-400 text-black' : 'bg-on-surface text-white') : 'bg-gray-200 text-secondary'"
                                                  x-text="s">
                                            </span>
                                            <div class="flex items-center gap-1.5 truncate">
                                                <span class="font-bold truncate"
                                                      :class="tournament.participants[s] ? 'text-on-surface' : 'italic text-secondary/60'"
                                                      x-text="tournament.participants[s] ? tournament.participants[s].team_name : '[ SLOT TERSEDIA ]'">
                                                </span>
                                                <template x-if="tournament.participants[s] && tournament.participants[s].is_winner">
                                                    <span class="px-1.5 py-0.2 bg-amber-400 text-black text-[9px] font-black rounded uppercase shrink-0">JUARA 1</span>
                                                </template>
                                            </div>
                                        </div>

                                        <span class="text-[10px] font-bold uppercase shrink-0"
                                              :class="tournament.participants[s] ? 'text-emerald-600 font-extrabold' : 'text-primary-container bg-black px-1.5 py-0.5 rounded'"
                                              x-text="tournament.participants[s] ? 'TERDAFTAR' : 'TERSEDIA'">
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- TAB 2: Bagan Pertandingan (Bracket Tree View) -->
                        <div x-show="viewTab === 'bracket'" class="space-y-2">
                            <template x-if="!tournament.matches || Object.keys(tournament.matches).length === 0">
                                <div class="text-center py-6 text-xs text-secondary italic">
                                    Bagan belum diacak oleh Admin. Pantau ketersediaan slot pendaftaran!
                                </div>
                            </template>

                            <template x-if="tournament.matches && Object.keys(tournament.matches).length > 0">
                                <div class="overflow-x-auto pb-2 max-h-[300px]">
                                    <div class="flex items-stretch gap-4 min-w-[500px]">
                                        <template x-for="(matches, roundNum) in tournament.matches" :key="roundNum">
                                            <div class="flex-1 flex flex-col justify-around space-y-3">
                                                <div class="text-center py-1 bg-gray-100 rounded text-[10px] font-bold uppercase text-secondary">
                                                    <span x-text="matches[0].round_name"></span>
                                                </div>
                                                <template x-for="m in matches" :key="m.id">
                                                    <div class="p-2 rounded-lg border text-xs bg-gray-50 border-border-subtle space-y-1">
                                                        <div class="p-1 rounded bg-white border truncate text-[11px]"
                                                             :class="m.team1_id && m.team1_id === m.winner_id ? 'border-emerald-400 text-emerald-700 font-bold bg-emerald-50' : 'border-border-subtle'">
                                                            <span x-text="m.team1"></span>
                                                        </div>
                                                        <div class="p-1 rounded bg-white border truncate text-[11px]"
                                                             :class="m.team2_id && m.team2_id === m.winner_id ? 'border-emerald-400 text-emerald-700 font-bold bg-emerald-50' : 'border-border-subtle'">
                                                            <span x-text="m.team2"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Registration & QRIS Payment Buttons -->
                    <div class="pt-4 mt-4 border-t border-border-subtle flex flex-wrap justify-between items-center gap-2">
                        <span class="text-xs text-secondary font-medium">
                            <span x-text="tournament.is_full ? 'Slot telah terkunci penuh.' : 'Kunci slot sebelum kehabisan!'"></span>
                        </span>
                        
                        <div class="flex items-center gap-2">
                            <!-- QRIS Modal Button (Besar & Responsif) -->
                            <button type="button" 
                                    @click="openQrisModal(tournament.name + ' (' + tournament.session_label + ')', tournament.formatted_entry_fee)"
                                    class="px-3 py-2 bg-primary-container text-on-surface text-xs font-black rounded-lg hover:brightness-95 transition flex items-center gap-1.5 shadow-xs">
                                <span class="material-symbols-outlined text-sm">qr_code_2</span>
                                Bayar QRIS
                            </button>

                            <!-- WhatsApp Register Button -->
                            <template x-if="!tournament.is_full">
                                <a :href="'https://api.whatsapp.com/send?phone=6288989504780&text=' + encodeURIComponent('Halo Admin ABT, saya ingin mendaftarkan tim untuk Turnamen eFootball ' + tournament.name + ' (' + tournament.session_label + '). Apakah masih ada slot?')" 
                                   target="_blank"
                                   class="px-3.5 py-2 bg-[#25D366] text-white text-xs font-bold rounded-lg hover:brightness-95 transition flex items-center gap-1.5 shadow-xs">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    Hubungi Admin
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </main>

    <!-- Footer Publik -->
    <footer class="bg-white border-t border-border-subtle py-4 text-center text-xs text-secondary">
        <p>&copy; {{ date('Y') }} ABT-FREELANCE eFootball Tournament. All rights reserved.</p>
    </footer>

    <!-- QRIS & Rekening Pembayaran Modal Pop-up (Besar, Responsif & Dinamis) -->
    <div x-show="qrisModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/70 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl border border-border-subtle max-w-lg w-full p-5 sm:p-7 shadow-2xl space-y-4 relative overflow-y-auto max-h-[92vh]"
             @click.outside="qrisModalOpen = false">
            
            <!-- Close Button -->
            <button type="button" @click="qrisModalOpen = false" 
                    class="absolute top-4 right-4 p-1.5 rounded-full bg-gray-100 hover:bg-gray-200 text-secondary hover:text-on-surface transition">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>

            <!-- Header Info -->
            <div class="text-center pr-6 pl-2">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-container/25 text-on-surface text-[10px] font-bold uppercase tracking-wider mb-1.5">
                    <span class="material-symbols-outlined text-xs">qr_code_2</span>
                    Pembayaran Registrasi Turnamen
                </div>
                <h3 class="text-base sm:text-lg font-black text-on-surface tracking-tight" x-text="currentSessionTitle"></h3>
                
                <!-- Dynamic Fee Notice (Exact Amount Dinamis) -->
                <div class="mt-2.5 p-2 rounded-xl bg-primary-container/20 border border-primary-container/40 inline-block">
                    <span class="text-xs text-on-surface font-semibold">👉 Transfer Sesuai Biaya Registrasi:</span>
                    <strong class="text-base font-black font-mono text-on-surface ml-1 bg-primary-container px-2.5 py-0.5 rounded shadow-xs" x-text="'Rp ' + currentFee"></strong>
                </div>
            </div>

            <!-- QRIS Barcode Box (Besar, Bisa Diklik untuk Zoom Selayar) -->
            @if(isset($qrisBase64) && $qrisBase64)
            <div class="p-3 sm:p-4 bg-[#fafafa] rounded-2xl border border-border-subtle text-center space-y-2.5">
                <div class="relative group inline-block cursor-pointer" @click="qrisZoomed = true" title="Klik untuk perbesar selayar (Zoom)">
                    <div class="bg-white p-2.5 rounded-xl border border-border-subtle shadow-xs inline-block w-full max-w-[320px] sm:max-w-[340px] group-hover:scale-102 transition-transform">
                        <img src="{{ $qrisBase64 }}" alt="QRIS ABT" class="w-full h-auto aspect-square object-contain mx-auto rounded-lg">
                    </div>
                    <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                        <span class="px-3 py-1.5 bg-black/80 text-white text-xs font-bold rounded-full flex items-center gap-1 shadow-md">
                            <span class="material-symbols-outlined text-sm">zoom_in</span>
                            Klik untuk Zoom Selayar
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                    <button type="button" @click="qrisZoomed = true" 
                            class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-on-surface text-xs font-bold rounded-lg transition flex items-center gap-1 shadow-2xs">
                        <span class="material-symbols-outlined text-sm">fullscreen</span>
                        Perbesar QRIS
                    </button>

                    <!-- Download QRIS Button -->
                    <a href="{{ $qrisBase64 }}" download="QRIS-ABT-FREELANCE-EFOOTBALL.png"
                       class="px-3.5 py-1.5 bg-on-surface text-white text-xs font-bold rounded-lg hover:brightness-110 transition flex items-center gap-1 shadow-2xs">
                        <span class="material-symbols-outlined text-sm">download</span>
                        Download Gambar QRIS
                    </a>
                </div>

                <p class="text-[11px] text-secondary font-medium">
                    Bisa scan via GoPay, OVO, DANA, ShopeePay, BCA Mobile, Livin, BRImo, dll.
                </p>
            </div>
            @endif

            <!-- Petunjuk Konfirmasi Cepat -->
            <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-start gap-2">
                <span class="material-symbols-outlined text-blue-600 text-base shrink-0 mt-0.5">info</span>
                <p class="leading-relaxed">
                    Setelah melakukan transfer, <strong>harap langsung kirimkan screenshot bukti transfer</strong> ke WhatsApp Admin agar tim Anda segera dimasukkan dan dikunci ke slot.
                </p>
            </div>

            <!-- Tombol Toggle Pilihan Rekening Lainnya -->
            <div class="pt-1">
                <button type="button" @click="showBankAccounts = !showBankAccounts"
                        class="w-full py-2 px-3 rounded-xl border border-border-subtle hover:border-on-surface bg-surface text-xs font-bold text-on-surface flex items-center justify-between transition">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">account_balance</span>
                        Informasi Rekening & E-Wallet Lainnya
                    </span>
                    <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="showBankAccounts ? 'rotate-180' : ''">expand_more</span>
                </button>

                <!-- Collapsible Bank Accounts List (Tanpa Nama Pemilik, Bersih) -->
                <div x-show="showBankAccounts" x-collapse x-cloak class="mt-2 space-y-2 text-xs">
                    @if(isset($settings->bca_account) && $settings->bca_account)
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#fafafa] border border-border-subtle">
                        <div class="flex items-center gap-2">
                            @if(isset($bcaBase64) && $bcaBase64)
                            <img src="{{ $bcaBase64 }}" alt="BCA" class="h-4 object-contain">
                            @else
                            <strong class="font-bold text-[11px]">BCA:</strong>
                            @endif
                        </div>
                        <span class="font-mono font-bold text-xs">{{ $settings->bca_account }}</span>
                    </div>
                    @endif

                    @if(isset($settings->dana_account) && $settings->dana_account)
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#fafafa] border border-border-subtle">
                        <div class="flex items-center gap-2">
                            @if(isset($danaBase64) && $danaBase64)
                            <img src="{{ $danaBase64 }}" alt="DANA" class="h-4 object-contain">
                            @else
                            <strong class="font-bold text-[11px]">DANA:</strong>
                            @endif
                        </div>
                        <span class="font-mono font-bold text-xs">{{ $settings->dana_account }}</span>
                    </div>
                    @endif

                    @if(isset($settings->seabank_account) && $settings->seabank_account)
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#fafafa] border border-border-subtle">
                        <div class="flex items-center gap-2">
                            @if(isset($seaBase64) && $seaBase64)
                            <img src="{{ $seaBase64 }}" alt="SeaBank" class="h-4 object-contain">
                            @else
                            <strong class="font-bold text-[11px]">SeaBank:</strong>
                            @endif
                        </div>
                        <span class="font-mono font-bold text-xs">{{ $settings->seabank_account }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Direct CTA to WhatsApp Admin -->
            <div class="pt-2">
                <a :href="'https://api.whatsapp.com/send?phone=6288989504780&text=' + encodeURIComponent('Halo Admin, saya ingin konfirmasi pendaftaran turnamen ' + currentSessionTitle + ' sebesar Rp ' + currentFee + '. Berikut bukti transfernya:')"
                   target="_blank"
                   class="w-full py-3 bg-[#25D366] text-white text-xs sm:text-sm font-extrabold rounded-xl hover:brightness-95 transition flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Kirim Bukti Transfer ke WA Admin
                </a>
            </div>
        </div>
    </div>

    <!-- QRIS Lightbox Zoom Selayar (Fullscreen Lightbox) -->
    @if(isset($qrisBase64) && $qrisBase64)
    <div x-show="qrisZoomed" x-cloak 
         class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="qrisZoomed = false">
        
        <div class="relative max-w-lg w-full text-center space-y-4" @click.stop>
            <!-- Close button top right -->
            <button type="button" @click="qrisZoomed = false" 
                    class="absolute -top-12 right-0 p-2 rounded-full bg-white/20 hover:bg-white/40 text-white transition">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>

            <!-- Zoomed Image Container -->
            <div class="bg-white p-4 rounded-2xl shadow-2xl inline-block max-w-[420px] w-full">
                <img src="{{ $qrisBase64 }}" alt="QRIS Fullscreen" class="w-full h-auto aspect-square object-contain mx-auto rounded-lg">
                <p class="text-xs text-secondary font-bold mt-2 text-center">
                    QRIS ABT eFootball Tournament
                </p>
            </div>

            <!-- Download Button in Fullscreen -->
            <div>
                <a href="{{ $qrisBase64 }}" download="QRIS-ABT-FREELANCE-EFOOTBALL.png"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary-container text-on-surface text-sm font-black rounded-xl hover:brightness-95 transition shadow-lg">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Simpan Barcode QRIS ke HP
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Alpine.js Auto-Fetch Real-Time Logic -->
    <script>
    function liveTournamentViewer() {
        @php
            $initialTournaments = $activeTournaments->map(function ($t) {
                $pMap = [];
                foreach ($t->participants as $p) {
                    $pMap[$p->slot_number] = [
                        'team_name' => $p->team_name,
                        'is_winner' => (bool)$p->is_winner,
                    ];
                }

                $matchesGrouped = [];
                foreach ($t->matches as $m) {
                    $matchesGrouped[$m->round][] = [
                        'id' => $m->id,
                        'round' => $m->round,
                        'round_name' => $m->round_name,
                        'match_number' => $m->match_number,
                        'team1' => $m->team1?->team_name ?? '-',
                        'team1_id' => $m->team1_id,
                        'team2' => $m->team2?->team_name ?? '-',
                        'team2_id' => $m->team2_id,
                        'winner_id' => $m->winner_id,
                        'score1' => $m->score1,
                        'score2' => $m->score2,
                        'status' => $m->status,
                    ];
                }

                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'session_label' => $t->session_label,
                    'entry_fee' => (float)$t->entry_fee,
                    'formatted_entry_fee' => number_format($t->entry_fee, 0, ',', '.'),
                    'prize_pool' => (float)$t->prize_pool,
                    'formatted_prize_pool' => number_format($t->prize_pool, 0, ',', '.'),
                    'max_slots' => (int)$t->max_slots,
                    'filled_slots_count' => (int)$t->filled_slots_count,
                    'remaining_slots_count' => (int)$t->remaining_slots_count,
                    'is_full' => $t->isFull(),
                    'status' => $t->status,
                    'participants' => $pMap,
                    'matches' => $matchesGrouped,
                ];
            });
        @endphp

        return {
            tournaments: @json($initialTournaments),
            qrisModalOpen: false,
            qrisZoomed: false,
            showBankAccounts: false,
            currentSessionTitle: '',
            currentFee: '',
            lastSync: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),

            init() {
                // Poll live data every 3.5 seconds automatically
                setInterval(async () => {
                    try {
                        const response = await fetch('{{ route('tour-organizer.efootball.live.data') }}');
                        if (response.ok) {
                            const data = await response.json();
                            this.tournaments = data.tournaments;
                            this.lastSync = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        }
                    } catch (err) {
                        console.error('Real-time sync error:', err);
                    }
                }, 3500);
            },

            openQrisModal(title, fee) {
                this.currentSessionTitle = title;
                this.currentFee = fee;
                this.showBankAccounts = false;
                this.qrisModalOpen = true;
            }
        };
    }
    </script>

</body>
</html>
