@extends('layouts.app')

@section('title', 'Custom Cup Bagan — Tour Organizer')
@section('header', 'Tour Organizer')
@section('favicon', asset('assets/logo-abt-efootball-tur.jpg'))
@section('header_logo', asset('assets/logo-abt-efootball-tur.jpg'))

@section('content')
<!-- Header Greeting -->
<div class="mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <div class="flex items-center gap-2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mb-1">
            <a href="{{ route('tour-organizer.index') }}" class="hover:text-on-surface dark:hover:text-white transition">Tour Organizer</a>
            <span class="material-symbols-outlined text-base">chevron_right</span>
            <span class="text-on-surface dark:text-white font-medium">Custom Cup Bagan</span>
        </div>
        <h1 class="text-2xl sm:text-[30px] font-black text-on-surface dark:text-white tracking-tight leading-tight">Turnamen Custom Cup & Bagan</h1>
        <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5">Kelola turnamen laga besar dengan bagan pohon eliminasi visual (8, 16, 32, hingga 64 Tim).</p>
    </div>
    <div class="flex items-center gap-2.5">
        <a href="{{ route('tour-organizer.custom-bracket.create') }}" 
           class="px-4 py-2 bg-primary-container text-on-surface text-xs font-bold rounded-lg shadow-sm hover:brightness-95 transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base">add</span>
            Buka Turnamen Bagan Baru
        </a>
    </div>
</div>

<!-- 3 Summary Metrics -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 mb-8">
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border-2 border-primary-container relative overflow-hidden shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Turnamen Aktif</span>
            <span class="w-7 h-7 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary dark:text-primary-container">
                <span class="material-symbols-outlined text-base">account_tree</span>
            </span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-on-surface dark:text-white tracking-tight">{{ $activeCount }} Event</h2>
        <p class="text-[11px] text-secondary dark:text-gray-400 mt-2">Bagan sedang berjalan</p>
    </div>

    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Turnamen Selesai</span>
            <span class="w-7 h-7 rounded-lg bg-emerald-500/15 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <span class="material-symbols-outlined text-base">emoji_events</span>
            </span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-on-surface dark:text-white tracking-tight">{{ $completedCount }} Event</h2>
        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-2">Juara 1 telah dinobatkan</p>
    </div>

    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Total Laba Bersih</span>
            <span class="w-7 h-7 rounded-lg bg-status-lunas/15 flex items-center justify-center text-status-lunas">
                <span class="material-symbols-outlined text-base">payments</span>
            </span>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-status-lunas tracking-tight">Rp {{ number_format($totalProfit, 0, ',', '.') }}</h2>
        <p class="text-[11px] text-secondary dark:text-gray-400 mt-2">Dari event bagan selesai</p>
    </div>
</div>

<!-- Tournaments Grid -->
<div class="space-y-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-base font-bold text-on-surface dark:text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-xl text-primary dark:text-primary-container">sports_esports</span>
            Daftar Turnamen Custom Cup
        </h3>
        <span class="text-xs text-secondary dark:text-gray-400">Bagan 8, 16, 32, 64 Tim</span>
    </div>

    @if($tournaments->isEmpty())
    <div class="p-12 rounded-xl bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] text-center max-w-md mx-auto">
        <span class="material-symbols-outlined text-5xl text-secondary/30 mb-2">account_tree</span>
        <h4 class="text-sm font-bold text-on-surface dark:text-white">Belum Ada Turnamen Custom Bagan</h4>
        <p class="text-xs text-secondary dark:text-gray-400 mt-1">Buat turnamen custom baru dengan pilihan bagan 8, 16, 32, atau 64 tim untuk memulai laga eliminasi pohon.</p>
        <div class="mt-4">
            <a href="{{ route('tour-organizer.custom-bracket.create') }}" class="px-4 py-2 bg-primary-container text-on-surface text-xs font-bold rounded-lg shadow-sm hover:brightness-95 transition inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">add</span>
                Buka Turnamen Bagan Baru
            </a>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($tournaments as $t)
        <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 shadow-xs hover:border-primary-container/80 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <span class="px-2 py-0.5 rounded bg-surface-container dark:bg-[#252525] text-on-surface dark:text-white text-[10px] font-black uppercase tracking-wider">
                            {{ $t->session_label }} • {{ $t->max_slots }} Tim
                        </span>
                        <h4 class="text-base font-bold text-on-surface dark:text-white mt-1.5 line-clamp-1">{{ $t->name }}</h4>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase shrink-0 {{ $t->status === 'completed' ? 'bg-gray-100 text-gray-700' : ($t->status === 'ongoing' ? 'bg-blue-50 text-blue-600 border border-blue-200' : ($t->status === 'full' ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600')) }}">
                        {{ $t->status === 'completed' ? 'SELESAI' : ($t->status === 'ongoing' ? 'LAGA ONGOING' : ($t->status === 'full' ? 'PENUH' : 'OPEN')) }}
                    </span>
                </div>

                <!-- Slot Fill Progress Bar -->
                <div class="space-y-1.5 my-3">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-secondary dark:text-gray-400">Tim Terdaftar:</span>
                        <span class="text-on-surface dark:text-white font-mono">{{ $t->filled_slots_count }} / {{ $t->max_slots }} Tim</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 dark:bg-[#252525] rounded-full overflow-hidden">
                        <div class="h-full bg-primary-container transition-all duration-300" 
                             style="width: {{ ($t->filled_slots_count / $t->max_slots) * 100 }}%"></div>
                    </div>
                </div>

                <!-- Financial Specs -->
                <div class="grid grid-cols-2 gap-2 p-2.5 bg-surface dark:bg-[#181818] rounded-lg border border-border-subtle dark:border-[#2a2a2a] text-xs mb-4">
                    <div>
                        <span class="text-[10px] text-secondary block">Regis / Tim:</span>
                        <strong class="text-on-surface dark:text-white font-mono">Rp {{ number_format($t->entry_fee, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-[10px] text-secondary block">Hadiah Juara:</span>
                        <strong class="text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($t->prize_pool, 0, ',', '.') }}</strong>
                    </div>
                </div>

                @if($t->winner)
                <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 text-xs flex items-center gap-2 mb-3">
                    <span class="text-lg">👑</span>
                    <div class="truncate">
                        <span class="text-[10px] uppercase font-bold text-amber-800 dark:text-amber-400 block">Juara 1:</span>
                        <strong class="text-on-surface dark:text-white truncate">{{ $t->winner->team_name }}</strong>
                    </div>
                </div>
                @endif
            </div>

            <!-- Card Actions -->
            <div class="pt-3 border-t border-border-subtle dark:border-[#2a2a2a] flex items-center justify-between gap-2">
                <span class="text-[11px] font-bold text-status-lunas">Profit: Rp {{ number_format($t->admin_profit, 0, ',', '.') }}</span>
                <a href="{{ route('tour-organizer.custom-bracket.show', $t) }}" 
                   class="px-3.5 py-1.5 bg-primary-container text-on-surface text-xs font-bold rounded-lg hover:brightness-95 transition flex items-center gap-1 shadow-xs">
                    Kelola Bagan
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $tournaments->links() }}</div>
    @endif
</div>
@endsection
