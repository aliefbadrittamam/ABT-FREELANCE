# 03. PROMPT 3: BLADE VIEWS ADMIN DASHBOARD & PRESET PREVIEW

Dokumen ini berisi kode tampilan admin untuk modul Tour Organizer > eFootball Mobile, terdiri dari:
1. `index.blade.php`: Ringkasan statistik, daftar sesi aktif, dan riwayat selesai.
2. `create.blade.php`: Form pembuatan sesi dengan **tombol preset instan** (5K get 30K, 10K get 60K, 15K get 95K, 20K get 130K).
3. `show.blade.php`: Manajemen visual slot 1-8 tim, tombol daftarkan tim, 1-klik tandai juara, dan 1-klik salin broadcast WA.

---

## 📋 File 1: `resources/views/tour-organizer/efootball/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'eFootball Mobile — Tour Organizer')
@section('header', 'eFootball Mobile')

@section('content')
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
    <div class="flex items-center gap-2.5">
        <a href="{{ url('/turnamen/efootball/live') }}" target="_blank" 
           class="px-3.5 py-2 bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#333] text-on-surface dark:text-gray-200 text-xs font-bold rounded-lg hover:bg-surface-variant dark:hover:bg-[#252525] transition-all flex items-center gap-1.5 shadow-xs">
            <span class="material-symbols-outlined text-base text-primary dark:text-primary-container">open_in_new</span>
            Halaman Publik Live
        </a>
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
@endsection
```

---

## 📋 File 2: `resources/views/tour-organizer/efootball/create.blade.php` (Dengan Preset Cepat)

```blade
@extends('layouts.app')

@section('title', 'Buat Sesi Turnamen Baru — eFootball')
@section('header', 'eFootball Mobile')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{
    sessionLabel: '{{ old('session_label', $suggestedSession) }}',
    maxSlots: 8,
    entryFee: 5000,
    prizePool: 30000,
    notes: '',
    applyPreset(fee, prize, slots = 8) {
        this.entryFee = fee;
        this.prizePool = prize;
        this.maxSlots = slots;
    },
    get totalGross() {
        return (Number(this.entryFee) || 0) * (Number(this.maxSlots) || 8);
    },
    get adminProfit() {
        return Math.max(0, this.totalGross - (Number(this.prizePool) || 0));
    },
    formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
}">
    <header class="mb-6">
        <a href="{{ route('tour-organizer.efootball.index') }}" class="text-xs text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white flex items-center gap-1 mb-2">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Kembali ke Daftar Sesi
        </a>
        <h1 class="text-2xl font-black text-on-surface dark:text-white tracking-tight">Buka Sesi Turnamen Baru</h1>
        <p class="text-xs text-secondary dark:text-gray-400 mt-0.5">Pilih salah satu preset cepat atau sesuaikan nominal biaya & hadiah.</p>
    </header>

    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-6 shadow-xs">
        <form action="{{ route('tour-organizer.efootball.store') }}" method="POST">
            @csrf

            <!-- Preset Buttons -->
            <div class="mb-6">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-2">
                    ⚡ Pilihan Preset Cepat (Tinggal Klik)
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button type="button" @click="applyPreset(5000, 30000, 8)"
                            class="p-2.5 rounded-lg border text-center transition"
                            :class="entryFee == 5000 && prizePool == 30000 ? 'border-primary-container bg-primary-container/20 text-on-surface font-bold' : 'border-border-subtle dark:border-[#333] hover:border-primary-container'">
                        <span class="block text-xs font-bold">5K Get 30K</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">Profit: 10K</span>
                    </button>

                    <button type="button" @click="applyPreset(10000, 60000, 8)"
                            class="p-2.5 rounded-lg border text-center transition"
                            :class="entryFee == 10000 && prizePool == 60000 ? 'border-primary-container bg-primary-container/20 text-on-surface font-bold' : 'border-border-subtle dark:border-[#333] hover:border-primary-container'">
                        <span class="block text-xs font-bold">10K Get 60K</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">Profit: 20K</span>
                    </button>

                    <button type="button" @click="applyPreset(15000, 95000, 8)"
                            class="p-2.5 rounded-lg border text-center transition"
                            :class="entryFee == 15000 && prizePool == 95000 ? 'border-primary-container bg-primary-container/20 text-on-surface font-bold' : 'border-border-subtle dark:border-[#333] hover:border-primary-container'">
                        <span class="block text-xs font-bold">15K Get 95K</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">Profit: 25K</span>
                    </button>

                    <button type="button" @click="applyPreset(20000, 130000, 8)"
                            class="p-2.5 rounded-lg border text-center transition"
                            :class="entryFee == 20000 && prizePool == 130000 ? 'border-primary-container bg-primary-container/20 text-on-surface font-bold' : 'border-border-subtle dark:border-[#333] hover:border-primary-container'">
                        <span class="block text-xs font-bold">20K Get 130K</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">Profit: 30K</span>
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Sesi Label & Jumlah Slot -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-1.5">Label Sesi</label>
                        <input type="text" name="session_label" x-model="sessionLabel" required placeholder="Contoh: Sesi 1 / Malam"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-semibold text-on-surface dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-1.5">Jumlah Slot Tim</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center justify-center p-2.5 rounded-lg border cursor-pointer"
                                   :class="maxSlots == 8 ? 'bg-primary-container text-on-surface font-bold border-primary-container' : 'border-border-subtle text-secondary'">
                                <input type="radio" name="max_slots" value="8" x-model="maxSlots" class="sr-only">
                                8 Slot (Default)
                            </label>
                            <label class="flex items-center justify-center p-2.5 rounded-lg border cursor-pointer"
                                   :class="maxSlots == 4 ? 'bg-primary-container text-on-surface font-bold border-primary-container' : 'border-border-subtle text-secondary'">
                                <input type="radio" name="max_slots" value="4" x-model="maxSlots" class="sr-only">
                                4 Slot (Mini)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Biaya Regis & Hadiah Juara 1 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-1.5">Biaya Pendaftaran / Tim (Rp)</label>
                        <input type="number" name="entry_fee" x-model="entryFee" required min="0" step="500"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-mono font-bold text-on-surface dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-1.5">Hadiah Juara 1 (Rp)</label>
                        <input type="number" name="prize_pool" x-model="prizePool" required min="0" step="500"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-mono font-bold text-emerald-600 outline-none">
                    </div>
                </div>

                <!-- Live Profit Summary Box -->
                <div class="p-4 rounded-xl bg-surface dark:bg-[#181818] border border-border-subtle dark:border-[#2a2a2a] space-y-2 text-xs">
                    <div class="flex justify-between items-center text-secondary dark:text-gray-400">
                        <span>Total Pemasukan Kotor (<span x-text="maxSlots"></span> tim):</span>
                        <span class="font-mono font-bold text-on-surface dark:text-white" x-text="'Rp ' + formatRupiah(totalGross)"></span>
                    </div>
                    <div class="flex justify-between items-center text-secondary dark:text-gray-400">
                        <span>Hadiah Juara 1:</span>
                        <span class="font-mono font-bold text-emerald-600" x-text="'- Rp ' + formatRupiah(prizePool)"></span>
                    </div>
                    <div class="pt-2 border-t border-border-subtle dark:border-[#2a2a2a] flex justify-between items-center">
                        <strong class="font-bold text-on-surface dark:text-white">Estimasi Keuntungan Admin:</strong>
                        <strong class="text-sm font-bold font-mono text-status-lunas" x-text="'Rp ' + formatRupiah(adminProfit)"></strong>
                    </div>
                </div>

                <!-- Notes / Rules -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-1.5">Catatan / Aturan (Opsional)</label>
                    <textarea name="notes" x-model="notes" rows="2" placeholder="Contoh: No lag, rematch jika disconnect menit 0-10..."
                              class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs text-on-surface dark:text-white outline-none resize-none"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-border-subtle dark:border-[#2a2a2a]">
                <a href="{{ route('tour-organizer.efootball.index') }}" class="px-4 py-2 border border-border-subtle rounded-lg text-xs font-semibold text-secondary">Batal</a>
                <button type="submit" class="px-5 py-2 bg-primary-container text-on-surface font-bold text-xs rounded-lg shadow-sm hover:brightness-95 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">sports_esports</span>
                    Buka Sesi Turnamen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
```

---

## 📋 File 3: `resources/views/tour-organizer/efootball/show.blade.php` (Manajemen Slot & Broadcast)

```blade
@extends('layouts.app')

@section('title', $tournament->name . ' (' . $tournament->session_label . ') — eFootball')
@section('header', 'eFootball Mobile')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    registerModalOpen: false,
    selectedSlot: 1,
    teamName: '',
    contactWa: '',
    copiedBroadcast: false,
    copiedWinnerWa: false,
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
                <a href="{{ route('tour-organizer.efootball.index') }}" class="hover:underline">Daftar Sesi</a>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
                <span>{{ $tournament->session_label }}</span>
            </div>
            <h1 class="text-2xl font-black text-on-surface dark:text-white tracking-tight flex items-center gap-2">
                {{ $tournament->name }}
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $tournament->status === 'completed' ? 'bg-gray-100 text-gray-700' : ($tournament->status === 'full' ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600') }}">
                    {{ $tournament->status === 'completed' ? 'SELESAI' : ($tournament->status === 'full' ? 'PENUH' : 'OPEN') }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
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
        <pre class="bg-white dark:bg-[#181818] p-3 rounded-lg border border-amber-200 text-xs font-mono whitespace-pre-wrap select-all">{{ session('winner_wa_message') }}</pre>
    </div>
    @endif

    <!-- Specs Mini Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white dark:bg-[#1e1e1e] p-4 rounded-xl border border-border-subtle dark:border-[#2a2a2a] shadow-xs text-xs">
        <div>
            <span class="text-[10px] text-secondary uppercase font-semibold block">Biaya Registrasi</span>
            <strong class="text-sm font-mono font-bold text-on-surface dark:text-white">Rp {{ number_format($tournament->entry_fee, 0, ',', '.') }}</strong>
        </div>
        <div>
            <span class="text-[10px] text-secondary uppercase font-semibold block">Hadiah Juara 1</span>
            <strong class="text-sm font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($tournament->prize_pool, 0, ',', '.') }}</strong>
        </div>
        <div>
            <span class="text-[10px] text-secondary uppercase font-semibold block">Laba Bersih Admin</span>
            <strong class="text-sm font-mono font-bold text-status-lunas">Rp {{ number_format($tournament->admin_profit, 0, ',', '.') }}</strong>
        </div>
        <div>
            <span class="text-[10px] text-secondary uppercase font-semibold block">Status Slot</span>
            <strong class="text-sm font-bold {{ $tournament->isFull() ? 'text-red-500' : 'text-on-surface dark:text-white' }}">
                {{ $tournament->filled_slots_count }} / {{ $tournament->max_slots }} Terisi
            </strong>
        </div>
    </div>

    <!-- Interactive Slot Grid (1 s/d max_slots) -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 shadow-xs">
        <h3 class="text-sm font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-4 flex items-center justify-between">
            <span>Daftar Slot Tim Peserta ({{ $tournament->max_slots }} Slot)</span>
            <span class="text-[11px] font-normal lowercase">klik slot kosong untuk mendaftarkan tim</span>
        </h3>

        @php $participantsMap = $tournament->participants->keyBy('slot_number'); @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            @for($slot = 1; $slot <= $tournament->max_slots; $slot++)
            @php $p = $participantsMap[$slot] ?? null; @endphp
            <div class="p-3.5 rounded-xl border transition-all flex items-center justify-between {{ $p ? ($p->is_winner ? 'border-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-border-subtle dark:border-[#333] bg-surface dark:bg-[#181818]') : 'border-dashed border-border-subtle hover:border-primary-container bg-white dark:bg-[#1e1e1e]' }}">
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
                        <!-- Winner Toggle -->
                        @if(!$p->is_winner && $tournament->status !== 'completed')
                        <form action="{{ route('tour-organizer.efootball.setWinner', [$tournament, $p]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-amber-500 hover:bg-amber-100 rounded-lg transition" title="Tandai Sebagai Juara 1">
                                <span class="material-symbols-outlined text-lg">emoji_events</span>
                            </button>
                        </form>
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
```
