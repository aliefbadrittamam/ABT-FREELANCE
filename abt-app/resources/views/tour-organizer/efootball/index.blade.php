@extends('layouts.app')

@section('title', 'eFootball Mobile — Tour Organizer')
@section('header', 'eFootball Mobile')
@section('favicon', asset('assets/logo-abt-efootball-tur.jpg'))
@section('header_logo', asset('assets/logo-abt-efootball-tur.jpg'))

@section('content')
<div x-data="{ resetModalOpen: false }">
<!-- Header Greeting -->
<div class="mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <div class="flex items-center gap-2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mb-1">
            <a href="{{ route('tour-organizer.index') }}" class="hover:text-on-surface dark:hover:text-white transition">Tour Organizer</a>
            <span class="material-symbols-outlined text-base">chevron_right</span>
            <span class="text-on-surface dark:text-white font-medium">eFootball Mobile</span>
        </div>
        <h1 class="text-2xl sm:text-[30px] font-black text-on-surface dark:text-white tracking-tight leading-tight">Manajemen Turnamen eFootball</h1>
        <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5">Kelola sesi turnamen kilat, slot tim peserta, dan rekap profit.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="{{ url('/turnamen/efootball/live') }}" target="_blank" 
           class="px-3.5 py-2 bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#333] text-on-surface dark:text-gray-200 text-xs font-bold rounded-lg hover:bg-surface-variant dark:hover:bg-[#252525] transition-all flex items-center gap-1.5 shadow-xs">
            <span class="material-symbols-outlined text-base text-primary dark:text-primary-container">open_in_new</span>
            Halaman Publik Live
        </a>

        @if($activeSessionsCount > 0)
        <!-- Reset Sesi Button with Confirmation Modal -->
        <button type="button" @click="resetModalOpen = true"
                class="px-3.5 py-2 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold rounded-lg hover:bg-red-100 transition-all flex items-center gap-1.5 shadow-xs">
            <span class="material-symbols-outlined text-base">restart_alt</span>
            Reset Sesi
        </button>
        @endif

        <a href="{{ route('tour-organizer.efootball.create') }}" 
           class="px-4 py-2 bg-primary-container text-on-surface text-xs font-bold rounded-lg shadow-sm hover:brightness-95 transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base">add</span>
            Buat Sesi Baru
        </a>
    </div>
</div>

<!-- 3 Strategic Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 mb-8">
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border-2 border-primary-container relative overflow-hidden shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Sesi Sedang Buka</span>
            <span class="w-7 h-7 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary dark:text-primary-container">
                <span class="material-symbols-outlined text-base">sports_esports</span>
            </span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-on-surface dark:text-white tracking-tight">{{ $activeSessionsCount }} Sesi</h2>
        <p class="text-[11px] text-secondary dark:text-gray-400 mt-2">Dapat berjalan paralel</p>
    </div>

    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Total Turnamen Selesai</span>
            <span class="w-7 h-7 rounded-lg bg-emerald-500/15 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <span class="material-symbols-outlined text-base">verified</span>
            </span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-on-surface dark:text-white tracking-tight">{{ $totalCompleted }} Turnamen</h2>
        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-2">Pemenang telah ditentukan</p>
    </div>

    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Total Laba Bersih Admin</span>
            <span class="w-7 h-7 rounded-lg bg-status-lunas/15 flex items-center justify-center text-status-lunas">
                <span class="material-symbols-outlined text-base">payments</span>
            </span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-status-lunas tracking-tight">Rp {{ number_format($totalProfitAccumulated, 0, ',', '.') }}</h2>
        <p class="text-[11px] text-secondary dark:text-gray-400 mt-2">Akumulasi laba bersih</p>
    </div>
</div>

<!-- Sesi Aktif (Active / Parallel Sessions) -->
<div class="mb-10">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-on-surface dark:text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Sesi Turnamen Aktif (Sedang Berjalan)
        </h3>
        <span class="text-xs text-secondary dark:text-gray-400">Bisa paralel banyak sesi</span>
    </div>

    @if($activeTournaments->isEmpty())
    <div class="p-8 rounded-xl bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] text-center">
        <span class="material-symbols-outlined text-4xl text-secondary/40 mb-2">sports_esports</span>
        <h4 class="text-sm font-bold text-on-surface dark:text-white">Tidak Ada Sesi Turnamen yang Sedang Aktif</h4>
        <p class="text-xs text-secondary dark:text-gray-400 mt-1 max-w-sm mx-auto">Buat sesi turnamen baru untuk mulai membuka slot pendaftaran bagi para pemain.</p>
        <div class="mt-4">
            <a href="{{ route('tour-organizer.efootball.create') }}" class="px-4 py-2 bg-primary-container text-on-surface text-xs font-bold rounded-lg shadow-sm hover:brightness-95 transition inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">add</span>
                Buka Sesi Turnamen Baru
            </a>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($activeTournaments as $tournament)
        <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 shadow-xs hover:border-primary-container/80 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <span class="px-2 py-0.5 rounded bg-surface-container dark:bg-[#252525] text-on-surface dark:text-white text-[10px] font-black uppercase tracking-wider">
                            {{ $tournament->session_label }}
                        </span>
                        <h4 class="text-base font-bold text-on-surface dark:text-white mt-1.5">{{ $tournament->name }}</h4>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase {{ $tournament->status === 'full' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                        {{ $tournament->status === 'full' ? 'PENUH' : 'OPEN' }}
                    </span>
                </div>

                <!-- Slot Fill Progress Bar -->
                <div class="space-y-1.5 my-3.5">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-secondary dark:text-gray-400">Slot Terisi:</span>
                        <span class="text-on-surface dark:text-white font-mono">{{ $tournament->filled_slots_count }} / {{ $tournament->max_slots }} Tim</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 dark:bg-[#252525] rounded-full overflow-hidden">
                        <div class="h-full bg-primary-container transition-all duration-300" 
                             style="width: {{ ($tournament->filled_slots_count / $tournament->max_slots) * 100 }}%"></div>
                    </div>
                </div>

                <!-- Financial Mini Specs -->
                <div class="grid grid-cols-2 gap-2 p-2.5 bg-surface dark:bg-[#181818] rounded-lg border border-border-subtle dark:border-[#2a2a2a] text-xs mb-4">
                    <div>
                        <span class="text-[10px] text-secondary block">Regis:</span>
                        <strong class="text-on-surface dark:text-white font-mono">Rp {{ number_format($tournament->entry_fee, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-[10px] text-secondary block">Hadiah Juara:</span>
                        <strong class="text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($tournament->prize_pool, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Card Actions -->
            <div class="pt-3 border-t border-border-subtle dark:border-[#2a2a2a] flex items-center justify-between gap-2">
                <span class="text-[11px] font-bold text-status-lunas">Profit: Rp {{ number_format($tournament->admin_profit, 0, ',', '.') }}</span>
                <a href="{{ route('tour-organizer.efootball.show', $tournament) }}" 
                   class="px-3.5 py-1.5 bg-primary-container text-on-surface text-xs font-bold rounded-lg hover:brightness-95 transition flex items-center gap-1 shadow-xs">
                    Kelola Slot
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<!-- Riwayat Turnamen Selesai (Completed Tournaments) -->
<div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 shadow-xs">
    <h3 class="text-base font-bold text-on-surface dark:text-white mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-xl text-secondary">history</span>
        Riwayat Turnamen Selesai
    </h3>

    @if($completedTournaments->isEmpty())
    <p class="text-xs text-secondary text-center py-6">Belum ada riwayat turnamen yang selesai.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-border-subtle dark:border-[#2a2a2a] text-secondary dark:text-gray-400 uppercase font-bold text-[10px]">
                    <th class="py-2.5 px-3">Sesi / Nama</th>
                    <th class="py-2.5 px-3">Tanggal Selesai</th>
                    <th class="py-2.5 px-3">Biaya Regis</th>
                    <th class="py-2.5 px-3">Hadiah Juara</th>
                    <th class="py-2.5 px-3">Juara 1</th>
                    <th class="py-2.5 px-3">Profit Bersih</th>
                    <th class="py-2.5 px-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle dark:divide-[#2a2a2a]">
                @foreach($completedTournaments as $comp)
                <tr class="hover:bg-surface-variant/30 dark:hover:bg-[#252525]/40 transition">
                    <td class="py-3 px-3">
                        <strong class="text-on-surface dark:text-white block">{{ $comp->name }}</strong>
                        <span class="text-[10px] text-secondary">{{ $comp->session_label }}</span>
                    </td>
                    <td class="py-3 px-3 text-secondary dark:text-gray-400">
                        {{ $comp->completed_at ? $comp->completed_at->translatedFormat('d M Y, H:i') : '-' }}
                    </td>
                    <td class="py-3 px-3 font-mono font-medium">Rp {{ number_format($comp->entry_fee, 0, ',', '.') }}</td>
                    <td class="py-3 px-3 font-mono font-bold text-emerald-600">Rp {{ number_format($comp->prize_pool, 0, ',', '.') }}</td>
                    <td class="py-3 px-3">
                        @if($comp->winner)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/30 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800 font-bold text-[11px]">
                            🏆 {{ $comp->winner->team_name }}
                        </span>
                        @else
                        <span class="text-secondary italic">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-3 font-mono font-bold text-status-lunas">+ Rp {{ number_format($comp->admin_profit, 0, ',', '.') }}</td>
                    <td class="py-3 px-3 text-right">
                        <a href="{{ route('tour-organizer.efootball.show', $comp) }}" class="text-primary dark:text-primary-container font-semibold hover:underline">
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $completedTournaments->links() }}</div>
    @endif
</div>

<!-- Modal Konfirmasi Reset Sesi Hari Ini -->
<div x-show="resetModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
    <div class="bg-white dark:bg-[#1e1e1e] rounded-2xl border border-border-subtle dark:border-[#2a2a2a] max-w-md w-full p-6 shadow-2xl space-y-4"
         @click.outside="resetModalOpen = false">
        
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">warning</span>
            </div>
            <div>
                <h3 class="text-base font-bold text-on-surface dark:text-white">Reset Semua Sesi Turnamen?</h3>
                <p class="text-xs text-secondary dark:text-gray-400">{{ $activeSessionsCount }} sesi aktif akan direset</p>
            </div>
        </div>

        <p class="text-xs text-secondary dark:text-gray-300 leading-relaxed">
            Apakah Anda yakin ingin mereset semua sesi aktif hari ini? 
            Sesi yang sudah ada pesertanya akan <strong>otomatis direkap selesai (profit tercatat aman)</strong>, sedangkan sesi yang kosong akan dibersihkan agar Anda bisa membuka sesi baru dari awal (Sesi 1).
        </p>

        <form action="{{ route('tour-organizer.efootball.resetSessions') }}" method="POST" class="flex justify-end gap-2.5 pt-2">
            @csrf
            <button type="button" @click="resetModalOpen = false" 
                    class="px-4 py-2 border border-border-subtle dark:border-[#333] rounded-lg text-xs font-semibold text-secondary hover:bg-surface-variant transition">
                Batal
            </button>
            <button type="submit" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-sm">restart_alt</span>
                Ya, Reset Sesi Sekarang
            </button>
        </form>
    </div>
</div>
</div>
@endsection
