@extends('layouts.app')

@section('title', 'Edit Invoice — ABT-FREELANCE')
@section('header', 'Invoice')

@section('content')
<div class="max-w-3xl" x-data="{
    paymentType: '{{ old('payment_type', $invoice->payment_type) }}',
    setToday() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        this.deadlineVal = `${year}-${month}-${day}T${hours}:${minutes}`;
    },
    deadlineVal: '{{ old('deadline', $invoice->deadline->format('Y-m-d\TH:i')) }}'
}">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mb-6">
        <a href="{{ route('invoices.index') }}" class="hover:text-on-surface dark:hover:text-white transition">Invoice</a>
        <span class="material-symbols-outlined text-base sm:text-lg">chevron_right</span>
        <a href="{{ route('invoices.show', $invoice) }}" class="hover:text-on-surface dark:hover:text-white transition">{{ $invoice->invoice_number }}</a>
        <span class="material-symbols-outlined text-base sm:text-lg">chevron_right</span>
        <span class="text-on-surface dark:text-white font-medium">Edit</span>
    </div>

    <!-- Status Stepper Card -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-4 sm:p-6 mb-6 shadow-sm transition-colors duration-200">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <!-- Stepper -->
            <div class="flex items-center space-x-1.5 sm:space-x-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0 scrollbar-none">
                @php
                    $steps = $invoice->payment_type === 'dp' 
                        ? ['unpaid' => 'Belum Bayar', 'dp_paid' => 'DP Terbayar', 'paid' => 'Lunas']
                        : ['unpaid' => 'Belum Bayar', 'paid' => 'Lunas'];
                    $stepKeys = array_keys($steps);
                    $currentIndex = array_search($invoice->status, $stepKeys);
                @endphp
                @foreach($steps as $key => $label)
                    @php $index = array_search($key, $stepKeys); @endphp
                    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                        <div class="flex items-center gap-1 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full text-[11px] sm:text-xs font-semibold
                            {{ $index < $currentIndex ? 'bg-status-lunas/10 text-status-lunas' : ($index === $currentIndex ? 'bg-primary-container text-on-surface' : 'bg-surface-container dark:bg-[#2a2a2a] text-on-surface-variant dark:text-gray-400') }}">
                            @if($index < $currentIndex)
                                <span class="material-symbols-outlined text-xs sm:text-sm">check_circle</span>
                            @endif
                            {{ $label }}
                        </div>
                        @if(!$loop->last)
                            <span class="material-symbols-outlined text-on-surface-variant/30 dark:text-gray-600 text-sm sm:text-base">arrow_forward</span>
                        @endif
                    </div>
                @endforeach
            </div>
            <a href="{{ route('invoices.show', $invoice) }}" class="text-xs sm:text-sm text-primary dark:text-primary-container hover:text-on-surface dark:hover:text-white font-medium flex items-center gap-1 transition">
                <span class="material-symbols-outlined text-base sm:text-lg">visibility</span>
                Lihat Invoice
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-8 shadow-sm transition-colors duration-200">
        <form action="{{ route('invoices.update', $invoice) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4 sm:space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Judul Invoice</label>
                        <input type="text" name="title" value="{{ old('title', $invoice->title) }}" required
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Nama Klien</label>
                        <input type="text" name="client_name" value="{{ old('client_name', $invoice->client_name) }}" required
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Kategori Jasa</label>
                        <select name="category_id" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary outline-none">
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $invoice->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
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
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Deskripsi Pekerjaan</label>
                    <textarea name="description" rows="3" required
                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none resize-none">{{ old('description', $invoice->description) }}</textarea>
                </div>

                <!-- Payment Type -->
                <div>
                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-2">Jenis Pembayaran</label>
                    <div class="flex bg-surface-container dark:bg-[#181818] border border-transparent dark:border-[#2a2a2a] rounded-lg p-1 gap-1 max-w-md">
                        <button type="button" @click="paymentType = 'dp'" :class="paymentType === 'dp' ? 'bg-primary-container text-on-surface font-semibold shadow-sm' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white'" class="flex-1 py-2 rounded-md text-xs sm:text-sm transition-all">Dengan DP</button>
                        <button type="button" @click="paymentType = 'full'" :class="paymentType === 'full' ? 'bg-primary-container text-on-surface font-semibold shadow-sm' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white'" class="flex-1 py-2 rounded-md text-xs sm:text-sm transition-all">Full Payment</button>
                    </div>
                    <input type="hidden" name="payment_type" :value="paymentType">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div x-show="paymentType === 'dp'" x-transition>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Jumlah DP</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 font-medium">Rp</span>
                            <input type="number" name="dp_amount" value="{{ old('dp_amount', $invoice->dp_amount) }}" min="0"
                                class="w-full pl-10 sm:pl-12 pr-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Total Biaya</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 font-medium">Rp</span>
                            <input type="number" name="total_amount" value="{{ old('total_amount', $invoice->total_amount) }}" required min="0"
                                class="w-full pl-10 sm:pl-12 pr-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary outline-none">
                        </div>
                    </div>
                </div>

                <!-- Status Update -->
                <div class="p-4 bg-surface dark:bg-[#181818] rounded-lg border border-border-subtle dark:border-[#2a2a2a]">
                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-2">Update Status Pembayaran</label>
                    <select name="status" class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary outline-none">
                        <option value="unpaid" {{ old('status', $invoice->status) === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="dp_paid" {{ old('status', $invoice->status) === 'dp_paid' ? 'selected' : '' }} x-show="paymentType === 'dp'">DP Terbayar</option>
                        <option value="paid" {{ old('status', $invoice->status) === 'paid' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2.5 sm:gap-3 pt-4 border-t border-border-subtle dark:border-[#2a2a2a]">
                    <a href="{{ route('invoices.show', $invoice) }}" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-transparent dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface-variant dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#333] transition">Batal</a>
                    <button type="submit" class="bg-primary-container text-on-surface font-semibold px-6 sm:px-8 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm hover:brightness-95 transition shadow-sm">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
