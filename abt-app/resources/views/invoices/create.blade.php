@extends('layouts.app')

@section('title', 'Buat Invoice — ABT-FREELANCE')
@section('header', 'Invoice')

@section('content')
<header class="mb-6 sm:mb-8">
    <h1 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">Buat Invoice Baru</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5 sm:mt-1">Isi detail invoice untuk klien Anda.</p>
</header>

<div class="max-w-2xl" x-data="{
    paymentType: '{{ old('payment_type', 'full') }}',
    setToday() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        this.deadlineVal = `${year}-${month}-${day}T${hours}:${minutes}`;
    },
    deadlineVal: '{{ old('deadline', date('Y-m-d\TH:i')) }}'
}">
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-8 shadow-sm transition-colors duration-200">
        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf
            <div class="space-y-4 sm:space-y-6">
                <!-- Client & Category -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Judul Invoice</label>
                        <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Invoice Website Toko"
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Nama Klien</label>
                        <input type="text" name="client_name" value="{{ old('client_name') }}" required
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        @error('client_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Kategori Jasa</label>
                        <select name="category_id" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary outline-none">
                            <option value="">Pilih kategori...</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider">Jatuh Tempo & Jam</label>
                            <button type="button" @click="setToday()" class="text-[11px] text-primary dark:text-primary-container font-bold hover:underline flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-xs">today</span>
                                Hari Ini
                            </button>
                        </div>
                        <input type="datetime-local" name="deadline" x-model="deadlineVal" required
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        @error('deadline') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Deskripsi Pekerjaan</label>
                    <textarea name="description" rows="3" required placeholder="Detail pekerjaan..."
                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none resize-none">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Payment Type Segmented Control -->
                <div>
                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-2">Jenis Pembayaran</label>
                    <div class="flex bg-surface-container dark:bg-[#181818] border border-transparent dark:border-[#2a2a2a] rounded-lg p-1 gap-1 max-w-md">
                        <button type="button" @click="paymentType = 'dp'"
                            :class="paymentType === 'dp' ? 'bg-primary-container text-on-surface font-semibold shadow-sm' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white'"
                            class="flex-1 py-2 sm:py-2.5 rounded-md text-xs sm:text-sm transition-all duration-200">
                            Dengan DP
                        </button>
                        <button type="button" @click="paymentType = 'full'"
                            :class="paymentType === 'full' ? 'bg-primary-container text-on-surface font-semibold shadow-sm' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white'"
                            class="flex-1 py-2 sm:py-2.5 rounded-md text-xs sm:text-sm transition-all duration-200">
                            Bayar Lunas Langsung
                        </button>
                    </div>
                    <input type="hidden" name="payment_type" :value="paymentType">
                </div>

                <!-- Pricing -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div x-show="paymentType === 'dp'" x-transition>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Jumlah DP</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 font-medium">Rp</span>
                            <input type="number" name="dp_amount" value="{{ old('dp_amount') }}" min="0"
                                class="w-full pl-10 sm:pl-12 pr-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        </div>
                        @error('dp_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Total Biaya</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 font-medium">Rp</span>
                            <input type="number" name="total_amount" value="{{ old('total_amount') }}" required min="0"
                                class="w-full pl-10 sm:pl-12 pr-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                        </div>
                        @error('total_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2.5 sm:gap-3 pt-4 border-t border-border-subtle dark:border-[#2a2a2a]">
                    <a href="{{ route('invoices.index') }}" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-transparent dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface-variant dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#333] transition">Batal</a>
                    <button type="submit" class="bg-primary-container text-on-surface font-semibold px-6 sm:px-8 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm hover:brightness-95 transition shadow-sm">Buat Invoice</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
