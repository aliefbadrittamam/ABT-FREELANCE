@extends('layouts.app')

@section('title', 'Pengaturan Pembayaran — ABT-FREELANCE')
@section('header', 'Pembayaran')

@section('content')
<header class="mb-6 sm:mb-8">
    <h1 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">Pengaturan Pembayaran</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5 sm:mt-1">Kelola gambar QRIS dan detail rekening/e-wallet untuk pembayaran invoice.</p>
</header>

<div class="max-w-4xl">
    <form action="{{ route('payment.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <!-- Left: QRIS Upload -->
            <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 flex flex-col justify-between shadow-sm transition-colors duration-200">
                <div>
                    <h3 class="text-xs font-bold text-on-surface dark:text-white uppercase tracking-wider mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary dark:text-primary-container text-base">qr_code_2</span>
                        1. Gambar QRIS (Statis)
                    </h3>
                    <p class="text-xs text-secondary dark:text-gray-400 mb-4">Gambar QRIS utama yang akan ditampilkan di setiap invoice.</p>

                    @if($hasQris)
                    <div class="mb-4 text-center p-4 bg-surface dark:bg-[#181818] rounded-xl border border-border-subtle dark:border-[#2a2a2a]">
                        <img src="{{ asset('storage/' . $setting->qris_image_path) }}?v={{ time() }}" alt="QRIS" class="max-w-[170px] mx-auto rounded-lg border border-border-subtle dark:border-[#333] bg-white p-2">
                        <div class="flex items-center justify-center gap-1 mt-2 text-status-lunas text-xs font-semibold">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            QRIS Aktif
                        </div>
                    </div>
                    @endif

                    <div class="border-2 border-dashed border-border-subtle dark:border-[#333] rounded-xl p-5 sm:p-6 text-center hover:border-primary-container transition-colors cursor-pointer bg-surface dark:bg-[#181818]" onclick="document.getElementById('qris_input').click()">
                        <span class="material-symbols-outlined text-2xl sm:text-3xl text-on-surface-variant/30 dark:text-gray-600 mb-1">add_photo_alternate</span>
                        <p class="text-xs text-on-surface dark:text-gray-300 font-medium">{{ $hasQris ? 'Klik untuk ganti gambar QRIS' : 'Klik untuk upload gambar QRIS' }}</p>
                        <p class="text-[10px] sm:text-[11px] text-secondary dark:text-gray-500 mt-0.5">PNG, JPG atau WEBP (Maks 5MB)</p>
                        <input type="file" name="qris_image" id="qris_input" accept="image/*" class="hidden" onchange="document.getElementById('file_chosen').innerText = this.files[0].name">
                        <p id="file_chosen" class="text-xs text-primary dark:text-primary-container font-semibold mt-2"></p>
                    </div>
                    @error('qris_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Right: Dynamic Bank Accounts & Notes -->
            <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 space-y-4 shadow-sm transition-colors duration-200">
                <div>
                    <h3 class="text-xs font-bold text-on-surface dark:text-white uppercase tracking-wider mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary dark:text-primary-container text-base">account_balance</span>
                        2. Informasi Rekening & E-Wallet
                    </h3>
                    <p class="text-xs text-secondary dark:text-gray-400 mb-3">Teks ini akan otomatis ditampilkan di invoice (bisa kamu edit nomor rekening / e-wallet kapan saja).</p>

                    <textarea name="bank_info" rows="10" required
                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs font-mono text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none resize-none leading-relaxed">{{ old('bank_info', $setting->bank_info) }}</textarea>
                    @error('bank_info') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 border-t border-border-subtle dark:border-[#2a2a2a] flex justify-end">
                    <button type="submit" class="w-full sm:w-auto bg-primary-container text-on-surface font-semibold px-6 py-2.5 rounded-lg text-xs sm:text-sm hover:brightness-95 transition-all flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-base sm:text-lg">save</span>
                        Simpan Pengaturan Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
