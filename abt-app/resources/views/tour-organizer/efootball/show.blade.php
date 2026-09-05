@extends('layouts.app')

@section('title', $tournament->name . ' (' . $tournament->session_label . ') — eFootball Fastur')
@section('header', 'eFootball Mobile')
@section('favicon', asset('assets/logo-abt-efootball-tur.jpg'))
@section('header_logo', asset('assets/logo-abt-efootball-tur.jpg'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    registerModalOpen: false,
    selectedSlot: 1,
    teamName: '',
    contactWa: '',
    copiedBroadcast: false,
    openRegister(slot) {
        this.selectedSlot = slot;
        this.teamName = '';
        this.contactWa = '';
        this.registerModalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-secondary dark:text-gray-400 mb-1">
                <a href="{{ route('tour-organizer.efootball.index') }}" class="hover:underline">Daftar Fastur</a>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
                <span>{{ $tournament->session_label }}</span>
            </div>
            <h1 class="text-2xl font-black text-on-surface dark:text-white tracking-tight flex items-center gap-2">
                {{ $tournament->name }}
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $tournament->status === 'completed' ? 'bg-gray-100 dark:bg-[#333] text-gray-700 dark:text-gray-300' : ($tournament->status === 'ongoing' ? 'bg-blue-50 text-blue-600 border border-blue-200 animate-pulse' : ($tournament->status === 'full' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200')) }}">
                    {{ $tournament->status === 'completed' ? 'SELESAI' : ($tournament->status === 'ongoing' ? '⚔️ SEDANG BERTANDING' : ($tournament->status === 'full' ? 'PENUH' : 'OPEN')) }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <!-- Start Tournament Button -->
            @if(($tournament->status === 'open' || $tournament->status === 'full') && $tournament->filled_slots_count >= 2)
            <form action="{{ route('tour-organizer.efootball.start', $tournament) }}" method="POST" onsubmit="return confirm('Mulai turnamen sekarang? Status akan berubah ke Sedang Bertanding dan Anda dapat memilih Juara 1.')" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-primary-container text-on-surface text-xs font-black rounded-lg shadow-xs hover:brightness-95 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">sports_soccer</span>
                    Mulai Turnamen
                </button>
            </form>
            @endif

            <!-- 1-Click Copy Broadcast Button -->
            <button type="button" 
                    @click="navigator.clipboard.writeText(`{{ addslashes($broadcastMessage) }}`); copiedBroadcast = true; setTimeout(() => copiedBroadcast = false, 2500)"
                    class="px-3.5 py-2 bg-on-surface text-white dark:bg-white dark:text-on-surface text-xs font-bold rounded-lg shadow-xs hover:brightness-110 transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base" x-text="copiedBroadcast ? 'check' : 'content_copy'"></span>
                <span x-text="copiedBroadcast ? 'Teks Tersalin!' : 'Salin Broadcast WA'"></span>
            </button>

            @if($tournament->status !== 'completed')
            <form action="{{ route('tour-organizer.efootball.complete', $tournament) }}" method="POST" onsubmit="return confirm('Selesaikan sesi turnamen ini dan rekap hasil ke riwayat?')" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-status-lunas text-white text-xs font-bold rounded-lg shadow-xs hover:brightness-110 transition flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">task_alt</span>
                    Selesaikan Sesi
                </button>
            </form>
            @endif

            <form action="{{ route('tour-organizer.efootball.destroy', $tournament) }}" method="POST" onsubmit="return confirm('Hapus sesi turnamen ini secara permanen?')" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="p-2 border border-red-200 text-red-600 rounded-lg transition hover:bg-red-50" title="Hapus Sesi">
                    <span class="material-symbols-outlined text-base">delete</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Template WhatsApp Juara jika baru saja ditandai -->
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

    <!-- Specs Mini Bar -->
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
            <span class="text-[10px] text-secondary dark:text-gray-400 uppercase font-semibold block">Status Slot Fastur</span>
            <strong class="text-sm font-bold {{ $tournament->isFull() ? 'text-red-500' : 'text-on-surface dark:text-white' }}">
                {{ $tournament->filled_slots_count }} / {{ $tournament->max_slots }} Tim Terisi
            </strong>
        </div>
    </div>

    <!-- Daftar Slot Peserta Fastur (4 atau 8 Slot) -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-secondary dark:text-gray-400">
                    Slot Peserta Terdaftar ({{ $tournament->max_slots }} Slot)
                </h3>
                <span class="text-[11px] text-secondary">Klik slot kosong untuk memasukkan tim yang sudah transfer regis</span>
            </div>

            <a href="{{ url('/turnamen/efootball/live') }}" target="_blank" class="text-xs font-bold text-primary dark:text-primary-container hover:underline inline-flex items-center gap-1">
                Buka Link Publik Live &rarr;
            </a>
        </div>

        @php $participantsMap = $tournament->participants->keyBy('slot_number'); @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            @for($slot = 1; $slot <= $tournament->max_slots; $slot++)
            @php $p = $participantsMap[$slot] ?? null; @endphp
            <div class="p-3.5 rounded-xl border transition-all flex items-center justify-between {{ $p ? ($p->is_winner ? 'border-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-border-subtle dark:border-[#333] bg-surface dark:bg-[#181818]') : 'border-dashed border-border-subtle dark:border-[#333] hover:border-primary-container bg-white dark:bg-[#1e1e1e]' }}">
                <!-- Left: Slot Number & Team Info -->
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center font-mono font-black text-xs shrink-0 {{ $p ? ($p->is_winner ? 'bg-amber-400 text-black' : 'bg-on-surface text-white dark:bg-white dark:text-on-surface') : 'bg-gray-100 dark:bg-[#252525] text-secondary' }}">
                        #{{ $slot }}
                    </span>

                    @if($p)
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <strong class="text-xs sm:text-sm font-bold text-on-surface dark:text-white truncate">{{ $p->team_name }}</strong>
                            @if($p->is_winner)
                            <span class="px-1.5 py-0.2 bg-amber-400 text-black text-[10px] font-black rounded uppercase">JUARA 1</span>
                            @endif
                        </div>
                        <span class="text-[10px] text-secondary dark:text-gray-400 block truncate">
                            {{ $p->contact_wa ? 'WA: ' . $p->contact_wa : 'Tanpa nomor WA' }}
                        </span>
                    </div>
                    @else
                    <div>
                        <span class="text-xs font-semibold text-secondary/60 dark:text-gray-500 italic">[ Slot Kosong ]</span>
                    </div>
                    @endif
                </div>

                <!-- Right: Slot Actions -->
                <div class="flex items-center gap-1.5 shrink-0">
                    @if($p)
                        <!-- Winner Button (Only active when status is ongoing) -->
                        @if(!$p->is_winner && $tournament->status === 'ongoing')
                        <form action="{{ route('tour-organizer.efootball.setWinner', [$tournament, $p]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-900/30 rounded-lg transition" title="Tandai Sebagai Juara 1">
                                <span class="material-symbols-outlined text-lg">emoji_events</span>
                            </button>
                        </form>
                        @elseif(!$p->is_winner && ($tournament->status === 'open' || $tournament->status === 'full'))
                        <span class="p-1.5 text-secondary/30 dark:text-gray-600 cursor-not-allowed" title="Mulai turnamen terlebih dahulu untuk menentukan Juara 1">
                            <span class="material-symbols-outlined text-lg">emoji_events</span>
                        </span>
                        @endif

                        <!-- WA Direct Chat -->
                        @if($p->contact_wa)
                        <a href="{{ $p->whats_app_url }}" target="_blank" class="p-1.5 text-[#25D366] hover:bg-emerald-50 rounded-lg transition" title="Chat WhatsApp">
                            <span class="material-symbols-outlined text-lg">chat</span>
                        </a>
                        @endif

                        <!-- Remove Participant -->
                        @if($tournament->status !== 'completed')
                        <form action="{{ route('tour-organizer.efootball.removeParticipant', [$tournament, $p]) }}" method="POST" onsubmit="return confirm('Hapus tim {{ $p->team_name }} dari slot {{ $slot }}?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Keluarkan Tim">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </form>
                        @endif
                    @else
                        <!-- Add Participant to Empty Slot -->
                        @if($tournament->status !== 'completed')
                        <button type="button" @click="openRegister({{ $slot }})"
                                class="px-3 py-1.5 bg-primary-container text-on-surface text-xs font-bold rounded-lg hover:brightness-95 transition shadow-xs flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Isi Slot
                        </button>
                        @endif
                    @endif
                </div>
            </div>
            @endfor
        </div>
    </div>

    <!-- Upload Bukti Transfer Hadiah (Jika ada pemenang) -->
    @if($tournament->winner_participant_id)
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-3">
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-secondary dark:text-gray-400">Bukti Transfer Hadiah ke Juara 1 ({{ $tournament->winner->team_name }})</h4>
                <p class="text-xs text-secondary mt-0.5">Hadiah: Rp {{ number_format($tournament->prize_pool, 0, ',', '.') }}</p>
            </div>
            @if($tournament->prize_proof_path)
            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                ✅ Hadiah Telah Ditransfer
            </span>
            @endif
        </div>

        @if($tournament->prize_proof_path)
        <div class="mb-3">
            <a href="{{ asset('storage/' . $tournament->prize_proof_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-primary font-bold hover:underline">
                <span class="material-symbols-outlined text-sm">image</span>
                Lihat Foto Bukti Transfer
            </a>
        </div>
        @endif

        <form action="{{ route('tour-organizer.efootball.uploadPrizeProof', $tournament) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <input type="file" name="prize_proof" accept="image/*" required class="text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary-container file:text-on-surface">
            <button type="submit" class="px-3 py-1 bg-on-surface text-white rounded-lg text-xs font-bold hover:brightness-110 transition shrink-0">Upload</button>
        </form>
    </div>
    @endif

    <!-- Modal Form Isi Slot Tim -->
    <div x-show="registerModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white dark:bg-[#1e1e1e] rounded-2xl border border-border-subtle dark:border-[#2a2a2a] max-w-sm w-full p-6 shadow-xl space-y-4"
             @click.outside="registerModalOpen = false">
            <h3 class="text-sm font-bold text-on-surface dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-primary">person_add</span>
                Daftarkan Tim ke Slot #<span x-text="selectedSlot"></span>
            </h3>

            <form action="{{ route('tour-organizer.efootball.register', $tournament) }}" method="POST" class="space-y-3">
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
</div>
@endsection
