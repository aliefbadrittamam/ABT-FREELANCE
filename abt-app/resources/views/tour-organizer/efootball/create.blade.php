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
