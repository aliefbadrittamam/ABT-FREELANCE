@extends('layouts.app')

@section('title', $tournament->name . ' — Custom Cup Bagan')
@section('header', 'Tour Organizer')
@section('favicon', asset('assets/logo-abt-efootball-tur.jpg'))
@section('header_logo', asset('assets/logo-abt-efootball-tur.jpg'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{
    activeTab: 'bracket',
    registerModalOpen: false,
    selectedSlot: 1,
    teamName: '',
    contactWa: '',
    copiedBroadcast: false,
    advanceModalOpen: false,
    selectedMatchId: null,
    selectedMatchActionUrl: '',
    selectedMatchTitle: '',
    matchTeam1Name: '',
    matchTeam1Id: '',
    matchTeam2Name: '',
    matchTeam2Id: '',
    openRegister(slot) {
        this.selectedSlot = slot;
        this.teamName = '';
        this.contactWa = '';
        this.registerModalOpen = true;
    },
    openAdvanceModal(matchId, url, roundName, matchNum, t1Name, t1Id, t2Name, t2Id) {
        this.selectedMatchId = matchId;
        this.selectedMatchActionUrl = url;
        this.selectedMatchTitle = roundName + ' - Match #' + matchNum;
        this.matchTeam1Name = t1Name || 'Tim 1';
        this.matchTeam1Id = t1Id;
        this.matchTeam2Name = t2Name || 'Tim 2';
        this.matchTeam2Id = t2Id;
        this.advanceModalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-secondary dark:text-gray-400 mb-1">
                <a href="{{ route('tour-organizer.custom-bracket.index') }}" class="hover:underline">Daftar Custom Cup</a>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
                <span>{{ $tournament->session_label }}</span>
            </div>
            <h1 class="text-2xl font-black text-on-surface dark:text-white tracking-tight flex items-center gap-2">
                {{ $tournament->name }}
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $tournament->status === 'completed' ? 'bg-gray-100 dark:bg-[#333] text-gray-700 dark:text-gray-300' : ($tournament->status === 'ongoing' ? 'bg-blue-50 text-blue-600 border border-blue-200 animate-pulse' : ($tournament->status === 'full' ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600')) }}">
                    {{ $tournament->status === 'completed' ? 'SELESAI' : ($tournament->status === 'ongoing' ? 'LAGA ONGOING' : ($tournament->status === 'full' ? 'PENUH' : 'OPEN')) }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <!-- Start Tournament Button -->
            @if(($tournament->status === 'open' || $tournament->status === 'full') && $tournament->filled_slots_count >= 2)
            <form action="{{ route('tour-organizer.custom-bracket.start', $tournament) }}" method="POST" onsubmit="return confirm('Mulai laga turnamen sekarang?')" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-primary-container text-on-surface text-xs font-black rounded-lg shadow-xs hover:brightness-95 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">sports_soccer</span>
                    Mulai Laga
                </button>
            </form>
            @endif

            <!-- 1-Click Copy Broadcast Button -->
            <button type="button" 
                    @click="navigator.clipboard.writeText(`{{ addslashes($broadcastMessage) }}`); copiedBroadcast = true; setTimeout(() => copiedBroadcast = false, 2500)"
                    class="px-3.5 py-2 bg-on-surface text-white dark:bg-white dark:text-on-surface text-xs font-bold rounded-lg shadow-xs hover:brightness-110 transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base" x-text="copiedBroadcast ? 'check' : 'content_copy'"></span>
                <span x-text="copiedBroadcast ? 'Tersalin!' : 'Salin Broadcast WA'"></span>
            </button>

            @if($tournament->status !== 'completed')
            <form action="{{ route('tour-organizer.custom-bracket.complete', $tournament) }}" method="POST" onsubmit="return confirm('Selesaikan turnamen ini dan catat profit admin?')" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-status-lunas text-white text-xs font-bold rounded-lg shadow-xs hover:brightness-110 transition flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">task_alt</span>
                    Selesaikan
                </button>
            </form>
            @endif

            <form action="{{ route('tour-organizer.custom-bracket.destroy', $tournament) }}" method="POST" onsubmit="return confirm('Hapus turnamen ini secara permanen?')" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="p-2 border border-red-200 text-red-600 rounded-lg transition hover:bg-red-50" title="Hapus Turnamen">
                    <span class="material-symbols-outlined text-base">delete</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Template WhatsApp Juara jika ada -->
    @if(session('winner_wa_message'))
    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-amber-900 dark:text-amber-300 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">emoji_events</span>
                Template Pesan WhatsApp untuk Juara 1:
            </span>
            @if(session('winner_wa_phone'))
            <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/[^0-9]/', '', session('winner_wa_phone')) }}&text={{ urlencode(session('winner_wa_message')) }}" target="_blank"
               class="px-3 py-1 bg-[#25D366] text-white text-[11px] font-bold rounded-lg hover:brightness-95 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">chat</span>
                Buka WA Juara
            </a>
            @endif
        </div>
        <pre class="bg-white dark:bg-[#181818] p-3 rounded-lg border border-amber-200 dark:border-[#333] text-xs font-mono whitespace-pre-wrap select-all text-on-surface dark:text-white">{{ session('winner_wa_message') }}</pre>
    </div>
    @endif

    <!-- Specs Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white dark:bg-[#1e1e1e] p-4 rounded-xl border border-border-subtle dark:border-[#2a2a2a] shadow-xs text-xs">
        <div>
            <span class="text-[10px] text-secondary dark:text-gray-400 uppercase font-semibold block">Biaya Registrasi</span>
            <strong class="text-sm font-mono font-bold text-on-surface dark:text-white">Rp {{ number_format($tournament->entry_fee, 0, ',', '.') }}</strong>
        </div>
        <div>
            <span class="text-[10px] text-secondary dark:text-gray-400 uppercase font-semibold block">Hadiah Juara 1</span>
            <strong class="text-sm font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($tournament->prize_pool, 0, ',', '.') }}</strong>
        </div>
        <div>
            <span class="text-[10px] text-secondary dark:text-gray-400 uppercase font-semibold block">Laba Bersih Admin</span>
            <strong class="text-sm font-mono font-bold text-status-lunas">Rp {{ number_format($tournament->admin_profit, 0, ',', '.') }}</strong>
        </div>
        <div>
            <span class="text-[10px] text-secondary dark:text-gray-400 uppercase font-semibold block">Kapasitas Bagan</span>
            <strong class="text-sm font-bold text-on-surface dark:text-white">
                {{ $tournament->filled_slots_count }} / {{ $tournament->max_slots }} Tim Terdaftar
            </strong>
        </div>
    </div>

    <!-- View Mode Switcher -->
    <div class="flex items-center justify-between border-b border-border-subtle dark:border-[#2a2a2a] pb-3">
        <div class="flex items-center gap-2">
            <button type="button" @click="activeTab = 'bracket'"
                    :class="activeTab === 'bracket' ? 'bg-on-surface text-white dark:bg-white dark:text-on-surface font-bold shadow-xs' : 'bg-white dark:bg-[#1e1e1e] text-secondary hover:text-on-surface border border-border-subtle dark:border-[#333]'"
                    class="px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">account_tree</span>
                Pohon Bagan Eliminasi ({{ $tournament->max_slots }} Tim)
            </button>

            <button type="button" @click="activeTab = 'slots'"
                    :class="activeTab === 'slots' ? 'bg-on-surface text-white dark:bg-white dark:text-on-surface font-bold shadow-xs' : 'bg-white dark:bg-[#1e1e1e] text-secondary hover:text-on-surface border border-border-subtle dark:border-[#333]'"
                    class="px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">format_list_numbered</span>
                Daftar Tim Terdaftar ({{ $tournament->filled_slots_count }}/{{ $tournament->max_slots }})
            </button>
        </div>

        <a href="{{ url('/turnamen/efootball/live') }}" target="_blank" class="text-xs font-bold text-primary dark:text-primary-container hover:underline inline-flex items-center gap-1">
            Lihat Halaman Publik Live &rarr;
        </a>
    </div>

    <!-- TAB 1: POHON BAGAN ELIMINASI -->
    <div x-show="activeTab === 'bracket'" x-transition class="space-y-4">
        <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 shadow-xs">
            
            <!-- Bracket Toolbar Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6 pb-4 border-b border-border-subtle dark:border-[#2a2a2a]">
                <div>
                    <h3 class="text-base font-bold text-on-surface dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-xl text-primary dark:text-primary-container">account_tree</span>
                        Struktur Bagan {{ $tournament->max_slots }} Tim
                    </h3>
                    <p class="text-xs text-secondary dark:text-gray-400 mt-0.5">Klik pada kotak pertandingan untuk menentukan tim yang menang dan melaju ke babak berikutnya.</p>
                </div>

                @if($tournament->status !== 'completed')
                <div class="flex items-center gap-2">
                    <!-- Randomize Bracket -->
                    <form action="{{ route('tour-organizer.custom-bracket.generateBracket', $tournament) }}" method="POST" onsubmit="return confirm('Acak posisi bagan pertandingan secara acak (Randomized)? Hasil pertandingan saat ini akan direset.')" class="inline">
                        @csrf
                        <input type="hidden" name="randomize" value="1">
                        <button type="submit" class="px-3 py-1.5 bg-primary-container text-on-surface font-bold text-xs rounded-lg hover:brightness-95 transition flex items-center gap-1 shadow-xs">
                            <span class="material-symbols-outlined text-sm">casino</span>
                            Acak Bagan (Randomize)
                        </button>
                    </form>

                    <!-- Sequential Bracket -->
                    <form action="{{ route('tour-organizer.custom-bracket.generateBracket', $tournament) }}" method="POST" onsubmit="return confirm('Susun bagan berurutan sesuai nomor pendaftaran?')" class="inline">
                        @csrf
                        <input type="hidden" name="randomize" value="0">
                        <button type="submit" class="px-3 py-1.5 border border-border-subtle dark:border-[#333] hover:bg-surface-variant text-secondary text-xs font-semibold rounded-lg transition flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">reorder</span>
                            Urut Nomor
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Bracket Visualization Container (Horizontal Scrollable Tree) -->
            @if($matchesByRound->isEmpty())
            <div class="text-center py-12 space-y-3">
                <span class="material-symbols-outlined text-5xl text-secondary/30">account_tree</span>
                <h4 class="text-sm font-bold text-on-surface dark:text-white">Bagan Belum Dibuat</h4>
                <p class="text-xs text-secondary max-w-md mx-auto">
                    Pastikan minimal ada 2 tim terdaftar, lalu klik tombol <strong>Acak Bagan</strong> di atas untuk membuat pohon bagan eliminasi.
                </p>
            </div>
            @else
            <div class="overflow-x-auto pb-6">
                <div class="flex items-stretch gap-8 min-w-[750px]">
                    @foreach($matchesByRound as $roundNum => $matches)
                    <div class="flex-1 flex flex-col justify-around space-y-6">
                        <!-- Round Header -->
                        <div class="text-center py-1.5 px-3 bg-surface dark:bg-[#181818] border border-border-subtle dark:border-[#2a2a2a] rounded-lg mb-2">
                            <span class="text-xs font-bold text-on-surface dark:text-white block">
                                {{ $matches->first()->round_name }}
                            </span>
                            <span class="text-[10px] text-secondary font-mono">{{ $matches->count() }} Match</span>
                        </div>

                        <!-- Matches in Round -->
                        @foreach($matches as $m)
                        <div class="bg-white dark:bg-[#181818] border rounded-xl p-3 shadow-xs space-y-2 transition-all {{ $m->winner_id ? 'border-emerald-300 dark:border-emerald-800' : ($m->status === 'ready' ? 'border-primary-container/80 dark:border-primary-container/40' : 'border-border-subtle dark:border-[#333] opacity-80') }}">
                            <div class="flex justify-between items-center text-[10px] text-secondary font-mono">
                                <span>Match #{{ $m->match_number }}</span>
                                @if($m->winner_id)
                                <span class="text-emerald-600 font-bold">SELESAI</span>
                                @elseif($m->status === 'ready')
                                <span class="text-blue-600 font-bold animate-pulse">SIAP</span>
                                @else
                                <span>MENUNGGU</span>
                                @endif
                            </div>

                            <!-- Team 1 -->
                            <div class="flex items-center justify-between p-2 rounded-lg border text-xs {{ $m->team1_id && $m->team1_id === $m->winner_id ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 text-emerald-800 font-bold' : 'border-border-subtle dark:border-[#333] bg-surface dark:bg-[#222]' }}">
                                <span class="truncate max-w-[140px]">{{ $m->team1?->team_name ?? 'Menunggu Pemenang...' }}</span>
                                @if($m->team1_id && $m->team1_id === $m->winner_id)
                                <span class="text-emerald-600 font-bold text-xs">WIN 🏆</span>
                                @endif
                            </div>

                            <!-- Team 2 -->
                            <div class="flex items-center justify-between p-2 rounded-lg border text-xs {{ $m->team2_id && $m->team2_id === $m->winner_id ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 text-emerald-800 font-bold' : 'border-border-subtle dark:border-[#333] bg-surface dark:bg-[#222]' }}">
                                <span class="truncate max-w-[140px]">{{ $m->team2?->team_name ?? 'Menunggu Pemenang...' }}</span>
                                @if($m->team2_id && $m->team2_id === $m->winner_id)
                                <span class="text-emerald-600 font-bold text-xs">WIN 🏆</span>
                                @endif
                            </div>

                            <!-- Action: Set Winner Button -->
                            @if(!$m->winner_id && $m->team1_id && $m->team2_id && $tournament->status !== 'completed')
                            <button type="button" 
                                    @click="openAdvanceModal({{ $m->id }}, '{{ route('tour-organizer.custom-bracket.advanceMatch', [$tournament, $m]) }}', '{{ $m->round_name }}', {{ $m->match_number }}, '{{ addslashes($m->team1->team_name) }}', {{ $m->team1_id }}, '{{ addslashes($m->team2->team_name) }}', {{ $m->team2_id }})"
                                    class="w-full py-1.5 bg-primary-container text-on-surface text-[11px] font-bold rounded-lg hover:brightness-95 transition flex items-center justify-center gap-1 shadow-2xs">
                                <span class="material-symbols-outlined text-xs">emoji_events</span>
                                Tentukan Pemenang
                            </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: DAFTAR SLOT TIM -->
    <div x-show="activeTab === 'slots'" x-transition class="space-y-4">
        <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-secondary dark:text-gray-400">
                    Slot Pendaftaran Tim ({{ $tournament->max_slots }} Tim)
                </h3>
                <span class="text-[11px] text-secondary">Klik slot kosong untuk mendaftarkan tim</span>
            </div>

            @php $participantsMap = $tournament->participants->keyBy('slot_number'); @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @for($slot = 1; $slot <= $tournament->max_slots; $slot++)
                @php $p = $participantsMap[$slot] ?? null; @endphp
                <div class="p-3 rounded-xl border transition-all flex flex-col justify-between min-h-[90px] {{ $p ? ($p->is_winner ? 'border-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-border-subtle dark:border-[#333] bg-surface dark:bg-[#181818]') : 'border-dashed border-border-subtle dark:border-[#333] hover:border-primary-container bg-white dark:bg-[#1e1e1e]' }}">
                    <div class="flex items-start justify-between gap-1.5 mb-2">
                        <span class="w-6 h-6 rounded-md flex items-center justify-center font-mono font-black text-xs shrink-0 {{ $p ? ($p->is_winner ? 'bg-amber-400 text-black' : 'bg-on-surface text-white dark:bg-white dark:text-on-surface') : 'bg-gray-100 dark:bg-[#252525] text-secondary' }}">
                            {{ $slot }}
                        </span>

                        @if($p)
                        <div class="flex items-center gap-1">
                            @if($p->contact_wa)
                            <a href="{{ $p->whats_app_url }}" target="_blank" class="p-1 text-[#25D366] hover:bg-emerald-50 rounded transition" title="WA Tim">
                                <span class="material-symbols-outlined text-sm">chat</span>
                            </a>
                            @endif

                            @if($tournament->status !== 'completed')
                            <form action="{{ route('tour-organizer.custom-bracket.removeParticipant', [$tournament, $p]) }}" method="POST" onsubmit="return confirm('Keluarkan tim {{ $p->team_name }} dari slot {{ $slot }}?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1 text-red-500 hover:bg-red-50 rounded transition" title="Keluarkan">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </form>
                            @endif
                        </div>
                        @endif
                    </div>

                    @if($p)
                    <div>
                        <strong class="text-xs font-bold text-on-surface dark:text-white block truncate" title="{{ $p->team_name }}">{{ $p->team_name }}</strong>
                        @if($p->is_winner)
                        <span class="inline-block mt-1 px-1.5 py-0.2 bg-amber-400 text-black text-[9px] font-black rounded uppercase">JUARA 1</span>
                        @endif
                    </div>
                    @else
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] font-semibold text-secondary/60 italic">Tersedia</span>
                        @if($tournament->status !== 'completed')
                        <button type="button" @click="openRegister({{ $slot }})"
                                class="px-2.5 py-1 bg-primary-container text-on-surface text-[10px] font-bold rounded-md hover:brightness-95 transition shadow-2xs">
                            + Isi Tim
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Modal Form Isi Slot Tim -->
    <div x-show="registerModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white dark:bg-[#1e1e1e] rounded-2xl border border-border-subtle dark:border-[#2a2a2a] max-w-sm w-full p-6 shadow-xl space-y-4"
             @click.outside="registerModalOpen = false">
            <h3 class="text-sm font-bold text-on-surface dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-primary">person_add</span>
                Daftarkan Tim ke Slot #<span x-text="selectedSlot"></span>
            </h3>

            <form action="{{ route('tour-organizer.custom-bracket.register', $tournament) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="slot_number" :value="selectedSlot">

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary mb-1">Nama Tim (Wajib)</label>
                    <input type="text" name="team_name" x-model="teamName" required placeholder="Contoh: GARUDA FC"
                           class="w-full px-3 py-2 bg-surface dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs font-semibold text-on-surface dark:text-white outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary mb-1">Kontak WhatsApp (Opsional)</label>
                    <input type="text" name="contact_wa" x-model="contactWa" placeholder="Contoh: 08123456789"
                           class="w-full px-3 py-2 bg-surface dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs text-on-surface dark:text-white outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="registerModalOpen = false" class="px-3.5 py-1.5 border border-border-subtle rounded-lg text-xs font-semibold text-secondary">Batal</button>
                    <button type="submit" class="px-4 py-1.5 bg-primary-container text-on-surface text-xs font-bold rounded-lg hover:brightness-95 transition">Simpan Tim</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tentukan Pemenang Match Bagan -->
    <div x-show="advanceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-[#1e1e1e] rounded-2xl border border-border-subtle dark:border-[#2a2a2a] max-w-md w-full p-6 shadow-2xl space-y-4"
             @click.outside="advanceModalOpen = false">
            
            <div class="flex items-center justify-between pb-2 border-b border-border-subtle dark:border-[#2a2a2a]">
                <h3 class="text-sm font-bold text-on-surface dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base">emoji_events</span>
                    <span x-text="selectedMatchTitle"></span>
                </h3>
                <button type="button" @click="advanceModalOpen = false" class="text-secondary hover:text-on-surface">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <p class="text-xs text-secondary dark:text-gray-400">Pilih tim yang memenangkan pertandingan ini untuk melaju ke babak berikutnya:</p>

            <form :action="selectedMatchActionUrl" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <!-- Option Team 1 -->
                    <label class="flex items-center justify-between p-3.5 rounded-xl border border-border-subtle dark:border-[#333] hover:border-primary-container cursor-pointer transition">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="winner_id" :value="matchTeam1Id" required class="w-4 h-4 text-black focus:ring-black">
                            <strong class="text-xs sm:text-sm font-bold text-on-surface dark:text-white" x-text="matchTeam1Name"></strong>
                        </div>
                        <span class="text-[10px] uppercase font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Pemenang 1</span>
                    </label>

                    <!-- Option Team 2 -->
                    <label class="flex items-center justify-between p-3.5 rounded-xl border border-border-subtle dark:border-[#333] hover:border-primary-container cursor-pointer transition">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="winner_id" :value="matchTeam2Id" required class="w-4 h-4 text-black focus:ring-black">
                            <strong class="text-xs sm:text-sm font-bold text-on-surface dark:text-white" x-text="matchTeam2Name"></strong>
                        </div>
                        <span class="text-[10px] uppercase font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Pemenang 2</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="advanceModalOpen = false" class="px-4 py-2 border border-border-subtle rounded-lg text-xs font-semibold text-secondary">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-primary-container text-on-surface font-bold text-xs rounded-lg hover:brightness-95 transition">
                        Konfirmasi Pemenang Lolos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
