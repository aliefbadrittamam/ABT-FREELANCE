@extends('layouts.app')

@section('title', 'Buat Invoice — ABT-FREELANCE')
@section('header', 'Invoice')

@section('content')
@php
    $categoriesData = [];
    foreach ($categories as $c) {
        $categoriesData[$c->id] = [
            'name' => $c->name,
            'prefix' => $c->prefix,
            'brand_name' => $c->brand_name ?: 'ABT-FREELANCE',
            'tagline' => $c->tagline ?: 'Invoice & Jasa Professional',
        ];
    }

    $logoPath = storage_path('app/public/assets/logo.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

    $paymentSetting = \App\Models\PaymentSetting::getSettings();
    $qrisPath = $paymentSetting->qris_image_path ? storage_path('app/public/' . $paymentSetting->qris_image_path) : null;
    $hasQris = $qrisPath && file_exists($qrisPath);
    $qrisBase64 = $hasQris ? 'data:image/png;base64,' . base64_encode(file_get_contents($qrisPath)) : null;

    $bcaPath = storage_path('app/public/assets/banks/bca.png');
    $bcaBase64 = file_exists($bcaPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bcaPath)) : null;

    $danaPath = storage_path('app/public/assets/banks/dana.png');
    $danaBase64 = file_exists($danaPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($danaPath)) : null;

    $seaPath = storage_path('app/public/assets/banks/seabank.png');
    $seaBase64 = file_exists($seaPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($seaPath)) : null;
@endphp

<div class="mb-6 sm:mb-8">
    <h1 class="text-2xl sm:text-[30px] font-black text-on-surface dark:text-white tracking-tight leading-tight">Buat Invoice Baru</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5">Isi formulir dan pantau live preview invoice di sebelah kanan.</p>
</div>

<div x-data="invoiceForm()" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

    <!-- Left Column: Form Input (5 cols on Desktop) -->
    <div class="lg:col-span-5 bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 shadow-sm transition-colors duration-200">
        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf
            <div class="space-y-4 sm:space-y-5">
                <!-- Title & Client Name -->
                <div>
                    <label class="block text-[11px] font-bold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Judul Invoice</label>
                    <input type="text" name="title" x-model="title" required placeholder="Contoh: Pembuatan Website Toko"
                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary focus:border-primary outline-none font-medium">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Nama Klien</label>
                    <input type="text" name="client_name" x-model="clientName" required placeholder="Contoh: Budi Santoso"
                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary focus:border-primary outline-none font-medium">
                    @error('client_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Category & Deadline -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Kategori Jasa</label>
                        <select name="category_id" x-model="categoryId" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary outline-none font-medium">
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[11px] font-bold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider">Jatuh Tempo</label>
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

                <!-- Description -->
                <div>
                    <label class="block text-[11px] font-bold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Deskripsi Pekerjaan</label>
                    <textarea name="description" x-model="description" rows="3" required placeholder="Jelaskan rincian pengerjaan tugas atau proyek..."
                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none resize-none"></textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Payment Type Selection -->
                <div>
                    <label class="block text-[11px] font-bold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-2">Jenis Pembayaran</label>
                    <div class="flex bg-surface-container dark:bg-[#181818] border border-transparent dark:border-[#2a2a2a] rounded-lg p-1 gap-1">
                        <button type="button" @click="onPaymentTypeChange('full')"
                            :class="paymentType === 'full' ? 'bg-primary-container text-on-surface font-bold shadow-sm' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white'"
                            class="flex-1 py-2 rounded-md text-xs sm:text-sm transition-all duration-200">
                            Bayar Lunas Langsung
                        </button>
                        <button type="button" @click="onPaymentTypeChange('dp')"
                            :class="paymentType === 'dp' ? 'bg-primary-container text-on-surface font-bold shadow-sm' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white'"
                            class="flex-1 py-2 rounded-md text-xs sm:text-sm transition-all duration-200">
                            Dengan DP (Bertahap)
                        </button>
                    </div>
                    <input type="hidden" name="payment_type" :value="paymentType">
                </div>

                <!-- Total Amount -->
                <div>
                    <label class="block text-[11px] font-bold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider mb-1.5">Total Biaya Proyek</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 font-bold">Rp</span>
                        <input type="number" name="total_amount" x-model="totalAmount" @input="onTotalChange()" required min="0" placeholder="0"
                            class="w-full pl-10 sm:pl-12 pr-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none font-bold">
                    </div>
                    @error('total_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- DP Section (With Smart Percentages & Custom Input) -->
                <div x-show="paymentType === 'dp'" x-transition class="p-4 bg-surface dark:bg-[#181818] rounded-xl border border-border-subtle dark:border-[#2a2a2a] space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-[11px] font-bold text-on-surface dark:text-white uppercase tracking-wider">Uang Muka (DP)</label>
                        <!-- Quick percentage presets -->
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] text-secondary mr-1">Preset:</span>
                            <button type="button" @click="setDpPercent(30)" class="px-2 py-0.5 rounded bg-white dark:bg-[#252525] border border-border-subtle text-[10px] font-bold hover:border-primary">30%</button>
                            <button type="button" @click="setDpPercent(40)" class="px-2 py-0.5 rounded bg-white dark:bg-[#252525] border border-border-subtle text-[10px] font-bold hover:border-primary">40%</button>
                            <button type="button" @click="setDpPercent(50)" class="px-2 py-0.5 rounded bg-primary-container text-on-surface text-[10px] font-bold shadow-sm">50%</button>
                        </div>
                    </div>

                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 font-bold">Rp</span>
                        <input type="number" name="dp_amount" x-model="dpAmount" @input="isCustomDp = true" min="0" placeholder="0"
                            class="w-full pl-10 sm:pl-12 pr-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none font-bold">
                    </div>
                    @error('dp_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                    <!-- Calculated Remaining info -->
                    <div class="flex justify-between items-center text-xs text-secondary dark:text-gray-400 pt-1">
                        <span>Sisa Pelunasan Nanti:</span>
                        <span class="font-bold text-on-surface dark:text-white" x-text="'Rp ' + formatRupiah(remainingAmount)"></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-subtle dark:border-[#2a2a2a]">
                    <a href="{{ route('invoices.index') }}" class="px-5 py-2.5 bg-transparent dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface-variant dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#333] transition font-semibold">Batal</a>
                    <button type="submit" class="bg-primary-container text-on-surface font-bold px-7 py-2.5 rounded-lg text-xs sm:text-sm hover:brightness-95 transition shadow-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">receipt_long</span>
                        Buat Invoice
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Right Column: Dynamic Live Invoice Preview (7 cols on Desktop, Sticky) -->
    <div class="lg:col-span-7 lg:sticky lg:top-24">
        <div class="flex items-center justify-between mb-3 px-1">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-status-lunas animate-pulse"></span>
                <span class="text-xs font-bold text-on-surface dark:text-white uppercase tracking-wider">Live Preview Invoice</span>
            </div>
            <span class="text-[11px] text-secondary dark:text-gray-400">Otomatis terupdate real-time</span>
        </div>

        <!-- Live Document -->
        <div class="bg-white text-on-surface border border-border-subtle shadow-lg p-6 sm:p-8 flex flex-col justify-between relative rounded-xl box-border overflow-hidden">
            <!-- Decorative subtle accent bar -->
            <div class="absolute top-0 left-0 w-full h-1.5 bg-on-surface"></div>
            <div class="absolute top-0 right-8 w-16 h-4 bg-primary-container rounded-b-md shadow-sm flex items-center justify-center">
                <span class="font-black text-[10px] text-on-surface tracking-wider leading-none">ABT</span>
            </div>

            <!-- Top Section -->
            <div>
                <!-- Header with Logo & Brand Info -->
                <div class="flex justify-between items-start mb-5 pb-4 border-b border-border-subtle">
                    <div class="flex items-center gap-3">
                        @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo" class="w-12 h-12 object-contain rounded-xl border border-border-subtle p-1 bg-white shadow-sm shrink-0">
                        @endif
                        <div>
                            <h1 class="text-xl font-bold tracking-tight text-on-surface leading-tight" x-text="currentCategory.brand_name"></h1>
                            <p class="text-secondary text-[10px] uppercase tracking-wider font-semibold mt-0.5" x-text="currentCategory.tagline"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <h2 class="text-2xl font-bold tracking-widest text-secondary/20 uppercase mb-0.5">INVOICE</h2>
                        <p class="font-bold text-on-surface text-xs font-mono tracking-wider" x-text="'INV-' + currentCategory.prefix + '-PREVIEW'"></p>
                        <p class="text-secondary text-[11px]">{{ date('d F Y') }}</p>
                    </div>
                </div>

                <!-- Client Info & Status Badge -->
                <div class="grid grid-cols-2 gap-4 mb-4 pb-4 border-b border-border-subtle">
                    <div>
                        <h3 class="text-[10px] font-semibold text-secondary uppercase tracking-widest mb-0.5">Ditujukan Kepada</h3>
                        <p class="text-base font-bold text-on-surface leading-snug" x-text="clientName || 'Nama Klien'"></p>
                        <p class="text-xs text-secondary mt-0.5">Proyek: <span class="font-semibold text-on-surface" x-text="title || 'Judul Proyek / Tugas'"></span></p>
                    </div>
                    <div class="flex justify-end items-start">
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-status-pending/10 text-status-pending border border-status-pending/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-status-pending mr-1.5 animate-pulse"></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider" x-text="paymentType === 'dp' ? 'Belum Bayar DP' : 'Belum Bayar'"></span>
                        </div>
                    </div>
                </div>

                <!-- Details Section -->
                <div class="mb-4 pb-4 border-b border-border-subtle grid grid-cols-3 gap-3">
                    <div>
                        <p class="text-[10px] font-semibold text-secondary uppercase mb-0.5">Kategori Jasa</p>
                        <p class="text-xs text-on-surface font-semibold" x-text="currentCategory.name"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-secondary uppercase mb-0.5">Deadline / Jatuh Tempo</p>
                        <p class="text-xs text-on-surface font-semibold" x-text="formatDate(deadlineVal)"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-secondary uppercase mb-0.5">Metode Biaya</p>
                        <p class="text-xs text-on-surface font-semibold" x-text="paymentType === 'dp' ? 'Bertahap (Dengan DP)' : 'Bayar Lunas Langsung'"></p>
                    </div>
                </div>

                <!-- 1 Row: Description (Left) & Pricing Summary (Right) -->
                <div class="grid grid-cols-2 gap-4 mb-4 items-stretch">
                    <!-- Left: Description -->
                    <div class="flex flex-col">
                        <p class="text-[10px] font-semibold text-secondary uppercase mb-1">Deskripsi Pekerjaan</p>
                        <div class="p-3 bg-surface rounded-lg border border-border-subtle text-xs text-on-surface whitespace-pre-line leading-relaxed flex-1" x-text="description || 'Rincian deskripsi pengerjaan proyek akan tampil di sini...'">
                        </div>
                    </div>

                    <!-- Right: Pricing Summary -->
                    <div class="flex flex-col">
                        <p class="text-[10px] font-semibold text-secondary uppercase mb-1">Rincian Pembayaran</p>
                        <div class="space-y-1.5 bg-surface/80 p-3 rounded-lg border border-border-subtle flex-1 flex flex-col justify-between">
                            <div class="space-y-1">
                                <div class="flex justify-between items-center text-xs text-secondary">
                                    <span>Total Biaya Proyek</span>
                                    <span class="font-semibold text-on-surface" x-text="'Rp ' + formatRupiah(totalAmount)"></span>
                                </div>

                                <template x-if="paymentType === 'dp'">
                                    <div class="space-y-1">
                                        <div class="flex justify-between items-center text-xs text-secondary">
                                            <span>Uang Muka (DP) Wajib</span>
                                            <span class="font-bold text-on-surface" x-text="'Rp ' + formatRupiah(dpAmount)"></span>
                                        </div>
                                        <div class="flex justify-between items-center text-[11px] text-secondary">
                                            <span>Sisa Pelunasan Nanti</span>
                                            <span x-text="'Rp ' + formatRupiah(remainingAmount)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div>
                                <div class="h-px bg-border-subtle w-full my-1"></div>
                                <div class="flex justify-between items-center pt-0.5">
                                    <span class="text-xs font-bold text-on-surface" x-text="paymentType === 'dp' ? 'Tagihan DP Saat Ini' : 'Total Tagihan'"></span>
                                    <span class="text-sm font-bold text-on-surface tracking-tight" x-text="paymentType === 'dp' ? 'Rp ' + formatRupiah(dpAmount) : 'Rp ' + formatRupiah(totalAmount)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section (QRIS & Bank Options Preview) -->
            <div>
                <div class="flex flex-col p-3.5 border border-border-subtle bg-surface border-dashed rounded-xl space-y-3">
                    <div class="text-center">
                        <p class="text-[10px] font-semibold text-secondary uppercase tracking-widest mb-0.5">Instruksi Pembayaran</p>
                        <div class="inline-flex items-center justify-center gap-2 bg-white px-3 py-1 rounded-lg border border-border-subtle shadow-sm">
                            <span class="text-xs text-on-surface font-medium" x-text="paymentType === 'dp' ? 'Transfer Pembayaran DP:' : 'Transfer Pembayaran Lunas:'"></span> 
                            <span class="bg-primary-container text-on-surface px-2.5 py-0.5 rounded font-extrabold text-xs sm:text-sm tracking-tight" x-text="paymentType === 'dp' ? 'Rp ' + formatRupiah(dpAmount) : 'Rp ' + formatRupiah(totalAmount)"></span>
                        </div>
                    </div>

                    @if($hasQris)
                    <!-- QRIS Thumbnail Preview -->
                    <div class="text-center bg-white p-2.5 rounded-xl border border-border-subtle shadow-sm max-w-xs mx-auto w-full">
                        <p class="text-[10px] font-bold text-on-surface uppercase tracking-wider mb-1">QRIS</p>
                        <img src="{{ $qrisBase64 }}" alt="QRIS" class="w-full max-w-[180px] h-auto object-contain mx-auto rounded">
                    </div>
                    @endif

                    <!-- Bank Row Preview -->
                    <div class="bg-white p-2.5 rounded-xl border border-border-subtle shadow-sm max-w-md mx-auto w-full">
                        <div class="grid grid-cols-3 gap-2">
                            <div class="p-1.5 rounded-lg border border-border-subtle bg-surface text-center">
                                <div class="h-4 flex items-center justify-center mb-0.5">
                                    @if($bcaBase64) <img src="{{ $bcaBase64 }}" class="h-3.5 max-w-[50px] object-contain"> @else <span class="text-[8px] font-bold text-[#005EAA]">BCA</span> @endif
                                </div>
                                <span class="font-mono font-bold text-[10px] text-on-surface">1921252558</span>
                            </div>
                            <div class="p-1.5 rounded-lg border border-border-subtle bg-surface text-center">
                                <div class="h-4 flex items-center justify-center mb-0.5">
                                    @if($danaBase64) <img src="{{ $danaBase64 }}" class="h-3.5 max-w-[50px] object-contain"> @else <span class="text-[8px] font-bold text-[#118EEA]">DANA</span> @endif
                                </div>
                                <span class="font-mono font-bold text-[10px] text-on-surface">082333362651</span>
                            </div>
                            <div class="p-1.5 rounded-lg border border-border-subtle bg-surface text-center">
                                <div class="h-4 flex items-center justify-center mb-0.5">
                                    @if($seaBase64) <img src="{{ $seaBase64 }}" class="h-3.5 max-w-[50px] object-contain"> @else <span class="text-[8px] font-bold text-[#FF5722]">SeaBank</span> @endif
                                </div>
                                <span class="font-mono font-bold text-[10px] text-on-surface">901099053997</span>
                            </div>
                        </div>
                        <p class="text-[9.5px] text-secondary text-center mt-1.5">Semua rekening a.n. <strong>ALIEF BADRIT TAMAM</strong></p>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-t border-border-subtle text-center text-[10px] text-secondary font-medium">
                    Official Invoice by <strong x-text="currentCategory.brand_name"></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function invoiceForm() {
    return {
        title: @json(old('title', '')),
        clientName: @json(old('client_name', '')),
        categoryId: @json(old('category_id', $categories->first()->id ?? '')),
        categories: @json($categoriesData),
        deadlineVal: @json(old('deadline', date('Y-m-d\TH:i'))),
        description: @json(old('description', '')),
        paymentType: @json(old('payment_type', 'full')),
        totalAmount: Number(@json(old('total_amount', 0))),
        dpAmount: Number(@json(old('dp_amount', 0))),
        isCustomDp: false,

        init() {
            if (this.paymentType === 'dp' && (!this.dpAmount || this.dpAmount === 0)) {
                this.setDpPercent(50);
            }
        },

        setToday() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            this.deadlineVal = `${year}-${month}-${day}T${hours}:${minutes}`;
        },

        setDpPercent(percent) {
            this.isCustomDp = false;
            if (this.totalAmount > 0) {
                this.dpAmount = Math.round(this.totalAmount * (percent / 100));
            }
        },

        onTotalChange() {
            if (this.paymentType === 'dp' && !this.isCustomDp) {
                this.setDpPercent(50);
            }
        },

        onPaymentTypeChange(type) {
            this.paymentType = type;
            if (type === 'dp' && (!this.dpAmount || this.dpAmount === 0)) {
                this.setDpPercent(50);
            }
        },

        formatRupiah(num) {
            if (!num || isNaN(num)) return '0';
            return new Intl.NumberFormat('id-ID').format(num);
        },

        formatDate(dateString) {
            if (!dateString) return '{{ date('d M Y, H:i') }} WIB';
            const d = new Date(dateString);
            if (isNaN(d.getTime())) return dateString;
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
            const day = String(d.getDate()).padStart(2, '0');
            const month = months[d.getMonth()];
            const year = d.getFullYear();
            const hours = String(d.getHours()).padStart(2, '0');
            const mins = String(d.getMinutes()).padStart(2, '0');
            return `${day} ${month} ${year}, ${hours}:${mins} WIB`;
        },

        get currentCategory() {
            return this.categories[this.categoryId] || {
                name: 'Pilih Kategori',
                prefix: 'JOKI',
                brand_name: 'ABT-FREELANCE',
                tagline: 'Invoice & Jasa Professional'
            };
        },

        get remainingAmount() {
            if (this.paymentType === 'full') return 0;
            const remaining = (Number(this.totalAmount) || 0) - (Number(this.dpAmount) || 0);
            return remaining > 0 ? remaining : 0;
        }
    };
}
</script>
@endsection
