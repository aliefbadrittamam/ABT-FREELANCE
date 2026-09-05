@extends('layouts.app')

@section('title', 'Buka Turnamen Bagan Baru — Custom Cup')
@section('header', 'Tour Organizer')
@section('favicon', asset('assets/logo-abt-efootball-tur.jpg'))
@section('header_logo', asset('assets/logo-abt-efootball-tur.jpg'))

@section('content')
<div class="max-w-2xl mx-auto" x-data="{
    name: 'Turnamen eFootball Championship',
    sessionLabel: 'Custom Cup Sesi 1',
    maxSlots: 16,
    entryFee: 10000,
    prizePool: 120000,
    notes: '',
    applyPreset(slots, fee, prize, customName) {
        this.maxSlots = slots;
        this.entryFee = fee;
        this.prizePool = prize;
        if (customName) this.name = customName;
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
        <a href="{{ route('tour-organizer.custom-bracket.index') }}" class="text-xs text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white flex items-center gap-1 mb-2">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Kembali ke Daftar Custom Cup
        </a>
        <h1 class="text-2xl font-black text-on-surface dark:text-white tracking-tight">Buka Turnamen Custom Cup Berbagan</h1>
        <p class="text-xs text-secondary dark:text-gray-400 mt-0.5">Pilih kapasitas pohon bagan 8, 16, 32, atau 64 tim peserta.</p>
    </header>

    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-6 shadow-xs">
        <form action="{{ route('tour-organizer.custom-bracket.store') }}" method="POST">
            @csrf

            <!-- Preset Buttons -->
            <div class="mb-6">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-2">
                    ⚡ Preset Cepat Turnamen Skala Besar
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button type="button" @click="applyPreset(8, 10000, 60000, 'Cup 8 Tim (QF-SF-Final)')"
                            class="p-2.5 rounded-lg border text-center transition"
                            :class="maxSlots == 8 ? 'border-primary-container bg-primary-container/20 font-bold' : 'border-border-subtle dark:border-[#333]'">
                        <span class="block text-xs font-bold">8 Tim (QF)</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">Profit: 20K</span>
                    </button>

                    <button type="button" @click="applyPreset(16, 10000, 120000, 'Cup 16 Tim (16B-QF-SF-Final)')"
                            class="p-2.5 rounded-lg border text-center transition"
                            :class="maxSlots == 16 ? 'border-primary-container bg-primary-container/20 font-bold' : 'border-border-subtle dark:border-[#333]'">
                        <span class="block text-xs font-bold">16 Tim (16B)</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">Profit: 40K</span>
                    </button>

                    <button type="button" @click="applyPreset(32, 10000, 240000, 'Cup 32 Tim (32B-16B-QF-Final)')"
                            class="p-2.5 rounded-lg border text-center transition"
                            :class="maxSlots == 32 ? 'border-primary-container bg-primary-container/20 font-bold' : 'border-border-subtle dark:border-[#333]'">
                        <span class="block text-xs font-bold">32 Tim (32B)</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">Profit: 80K</span>
                    </button>

                    <button type="button" @click="applyPreset(64, 10000, 500000, 'Cup 64 Tim (Major Cup)')"
                            class="p-2.5 rounded-lg border text-center transition"
                            :class="maxSlots == 64 ? 'border-primary-container bg-primary-container/20 font-bold' : 'border-border-subtle dark:border-[#333]'">
                        <span class="block text-xs font-bold">64 Tim (Major)</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">Profit: 140K</span>
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Nama Turnamen & Label Sesi -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-1.5">Nama Turnamen</label>
                    <input type="text" name="name" x-model="name" required placeholder="Contoh: eFootball Independence Cup 2026"
                           class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-bold text-on-surface dark:text-white outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-1.5">Label Sesi / Edisi</label>
                        <input type="text" name="session_label" x-model="sessionLabel" required placeholder="Contoh: Sesi 1 / Season 2"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-semibold text-on-surface dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-1.5">Kapasitas Slot Bagan</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <label class="flex items-center justify-center py-2 px-1 rounded-lg border cursor-pointer text-center"
                                   :class="maxSlots == 8 ? 'bg-primary-container text-on-surface font-black border-primary-container shadow-xs' : 'border-border-subtle dark:border-[#333] text-secondary'">
                                <input type="radio" name="max_slots" value="8" x-model="maxSlots" class="sr-only">
                                <span class="text-xs">8 Tim</span>
                            </label>
                            <label class="flex items-center justify-center py-2 px-1 rounded-lg border cursor-pointer text-center"
                                   :class="maxSlots == 16 ? 'bg-primary-container text-on-surface font-black border-primary-container shadow-xs' : 'border-border-subtle dark:border-[#333] text-secondary'">
                                <input type="radio" name="max_slots" value="16" x-model="maxSlots" class="sr-only">
                                <span class="text-xs">16 Tim</span>
                            </label>
                            <label class="flex items-center justify-center py-2 px-1 rounded-lg border cursor-pointer text-center"
                                   :class="maxSlots == 32 ? 'bg-primary-container text-on-surface font-black border-primary-container shadow-xs' : 'border-border-subtle dark:border-[#333] text-secondary'">
                                <input type="radio" name="max_slots" value="32" x-model="maxSlots" class="sr-only">
                                <span class="text-xs">32 Tim</span>
                            </label>
                            <label class="flex items-center justify-center py-2 px-1 rounded-lg border cursor-pointer text-center"
                                   :class="maxSlots == 64 ? 'bg-primary-container text-on-surface font-black border-primary-container shadow-xs' : 'border-border-subtle dark:border-[#333] text-secondary'">
                                <input type="radio" name="max_slots" value="64" x-model="maxSlots" class="sr-only">
                                <span class="text-xs">64 Tim</span>
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
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-400 mb-1.5">Peraturan Khusus Bagan (Opsional)</label>
                    <textarea name="notes" x-model="notes" rows="2" placeholder="Contoh: Sistem gugur tunggal, match BO1, Final BO3..."
                              class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs text-on-surface dark:text-white outline-none resize-none"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-border-subtle dark:border-[#2a2a2a]">
                <a href="{{ route('tour-organizer.custom-bracket.index') }}" class="px-4 py-2 border border-border-subtle rounded-lg text-xs font-semibold text-secondary">Batal</a>
                <button type="submit" class="px-5 py-2 bg-primary-container text-on-surface font-bold text-xs rounded-lg shadow-sm hover:brightness-95 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">account_tree</span>
                    Buka Turnamen Bagan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
