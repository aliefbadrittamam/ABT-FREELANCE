@extends('layouts.app')

@section('title', $invoice->invoice_number . ' — ABT-FREELANCE')
@section('header', 'Invoice Detail')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div class="max-w-4xl mx-auto" x-data="{
    exportingImage: false,
    exportAsImage() {
        this.exportingImage = true;
        const element = document.getElementById('invoice-document');
        
        html2canvas(element, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            scrollX: 0,
            scrollY: 0,
            logging: false,
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = '{{ $invoice->invoice_number }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            this.exportingImage = false;
        }).catch(err => {
            console.error(err);
            alert('Gagal mengunduh gambar. Silakan coba lagi.');
            this.exportingImage = false;
        });
    }
}">
    <!-- Top Actions & Status Stepper -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4 bg-white dark:bg-[#1e1e1e] p-4 sm:p-5 rounded-xl border border-border-subtle dark:border-[#2a2a2a] shadow-sm">
        <!-- Stepper -->
        <div class="flex items-center space-x-2 overflow-x-auto w-full lg:w-auto pb-2 lg:pb-0 scrollbar-none">
            @php
                $isPaid = $invoice->status === 'paid';
                $isDpPaid = $invoice->status === 'dp_paid';
                $isUnpaid = $invoice->status === 'unpaid';
                $isCanceled = $invoice->status === 'canceled';
            @endphp

            @if($isCanceled)
            <!-- Canceled State -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-bold">
                <span class="material-symbols-outlined text-sm">cancel</span>
                Invoice Dibatalkan
            </div>
            @else
            <!-- Step 1: Belum Bayar -->
            <div class="flex items-center shrink-0">
                <div class="h-6 w-6 rounded-full {{ !$isUnpaid ? 'bg-status-lunas text-white' : 'bg-primary-container border-2 border-on-surface text-on-surface' }} flex items-center justify-center">
                    @if(!$isUnpaid)
                    <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
                    @else
                    <span class="w-2 h-2 rounded-full bg-on-surface"></span>
                    @endif
                </div>
                <span class="ml-1.5 sm:ml-2 text-xs sm:text-sm {{ !$isUnpaid ? 'text-secondary dark:text-gray-400 line-through' : 'font-semibold text-on-surface dark:text-white' }}">
                    {{ $invoice->payment_type === 'dp' ? 'Belum DP' : 'Belum Bayar' }}
                </span>
            </div>

            <div class="w-6 sm:w-8 h-[2px] shrink-0 {{ !$isUnpaid ? 'bg-status-lunas' : 'bg-border-subtle dark:bg-[#333]' }}"></div>

            <!-- Step 2: DP Terbayar (if DP type) -->
            @if($invoice->payment_type === 'dp')
            <div class="flex items-center shrink-0">
                <div class="h-6 w-6 rounded-full {{ $isPaid ? 'bg-status-lunas text-white' : ($isDpPaid ? 'bg-primary-container border-2 border-on-surface' : 'border-2 border-border-subtle dark:border-[#444]') }} flex items-center justify-center">
                    @if($isPaid)
                    <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
                    @elseif($isDpPaid)
                    <span class="w-2 h-2 rounded-full bg-on-surface"></span>
                    @endif
                </div>
                <span class="ml-1.5 sm:ml-2 text-xs sm:text-sm {{ $isPaid ? 'text-secondary dark:text-gray-400 line-through' : ($isDpPaid ? 'font-semibold text-on-surface dark:text-white' : 'text-secondary dark:text-gray-500') }}">DP Terbayar</span>
            </div>
            <div class="w-6 sm:w-8 h-[2px] shrink-0 {{ $isPaid ? 'bg-status-lunas' : 'bg-border-subtle dark:bg-[#333]' }}"></div>
            @endif

            <!-- Step 3: Lunas -->
            <div class="flex items-center shrink-0 {{ !$isPaid ? 'opacity-60' : '' }}">
                <div class="h-6 w-6 rounded-full {{ $isPaid ? 'bg-status-lunas text-white' : 'border-2 border-border-subtle dark:border-[#444]' }} flex items-center justify-center">
                    @if($isPaid)
                    <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
                    @endif
                </div>
                <span class="ml-1.5 sm:ml-2 text-xs sm:text-sm {{ $isPaid ? 'font-semibold text-on-surface dark:text-white' : 'text-secondary dark:text-gray-400' }}">Lunas</span>
            </div>
            @endif
        </div>

        <!-- Action Buttons (Edit, Cancel, Delete, PNG, PDF) -->
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto pt-2 lg:pt-0 border-t lg:border-t-0 border-border-subtle dark:border-[#2a2a2a]">
            <a href="{{ route('invoices.edit', $invoice) }}" 
               class="px-3.5 py-2 border border-border-subtle dark:border-[#333] bg-transparent text-on-surface dark:text-gray-300 font-semibold text-xs sm:text-sm rounded-lg hover:bg-surface-variant dark:hover:bg-[#252525] transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">edit</span>
                Edit
            </a>

            @if($invoice->status !== 'canceled' && $invoice->status !== 'paid')
            <form action="{{ route('invoices.cancel', $invoice) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan invoice ini?')" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 border border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-400 font-semibold text-xs sm:text-sm rounded-lg hover:bg-yellow-100 transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">block</span>
                    Batalkan
                </button>
            </form>
            @endif

            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus invoice {{ $invoice->invoice_number }} secara permanen? Data yang dihapus tidak bisa dikembalikan.')" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="p-2 border border-red-200 dark:border-red-900/40 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Hapus Permanen">
                    <span class="material-symbols-outlined text-base">delete</span>
                </button>
            </form>
            
            <a href="{{ route('invoices.export', [$invoice, 'png']) }}" 
               class="px-3.5 py-2 border-2 border-primary-container bg-transparent text-on-surface dark:text-gray-200 font-semibold text-xs sm:text-sm rounded-lg hover:bg-primary-container/10 transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">image</span>
                Export Gambar (PNG)
            </a>

            <a href="{{ route('invoices.export', [$invoice, 'pdf']) }}" 
               class="px-4 py-2 bg-primary-container text-on-surface font-semibold text-xs sm:text-sm rounded-lg hover:brightness-95 transition-colors shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">download</span>
                Export PDF
            </a>
        </div>
    </div>

    <!-- Task File Archive Card -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-4 sm:p-5 mb-6 shadow-sm transition-colors duration-200">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-container/20 dark:bg-primary/20 text-primary dark:text-primary-container flex items-center justify-center shrink-0 border border-primary/20">
                    <span class="material-symbols-outlined text-xl">folder_zip</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-on-surface dark:text-white flex items-center gap-2">
                        Arsip File Tugas
                        @if($invoice->task_file_path)
                        <span class="text-[10px] bg-status-lunas/15 text-status-lunas font-semibold px-2 py-0.5 rounded-full">Tersimpan</span>
                        @else
                        <span class="text-[10px] bg-surface-container dark:bg-[#252525] text-secondary dark:text-gray-400 font-semibold px-2 py-0.5 rounded-full">Belum Diunggah</span>
                        @endif
                    </h3>
                    <p class="text-xs text-secondary dark:text-gray-400 mt-0.5">
                        @if($invoice->task_file_name)
                        File: <span class="font-medium text-on-surface dark:text-white">{{ $invoice->task_file_name }}</span>
                        @else
                        Simpan file hasil pengerjaan (ZIP, RAR, DOCX, PDF, dll) untuk arsip project ini.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Upload & Download Actions -->
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                @if($invoice->task_file_path)
                <a href="{{ route('invoices.downloadTaskFile', $invoice) }}" 
                   class="px-3.5 py-1.5 bg-primary-container text-on-surface font-semibold text-xs rounded-lg hover:brightness-95 transition flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-base">download</span>
                    Download File
                </a>

                <form action="{{ route('invoices.deleteTaskFile', $invoice) }}" method="POST" onsubmit="return confirm('Hapus file arsip tugas ini?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 text-secondary dark:text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Hapus File">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </form>
                @endif

                <form action="{{ route('invoices.uploadTaskFile', $invoice) }}" method="POST" enctype="multipart/form-data" class="inline">
                    @csrf
                    <input type="file" name="task_file" id="task_file_input" class="hidden" onchange="this.form.submit()">
                    <button type="button" onclick="document.getElementById('task_file_input').click()" 
                            class="px-3.5 py-1.5 border border-border-subtle dark:border-[#333] text-on-surface dark:text-gray-300 font-semibold text-xs rounded-lg hover:bg-surface-variant dark:hover:bg-[#252525] transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">upload_file</span>
                        {{ $invoice->task_file_path ? 'Ganti File' : 'Upload File Tugas' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Invoice Document (Base64 Encoded Images for Perfect Clean Export) -->
    @php
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

        if ($invoice->payment_type === 'dp') {
            if ($invoice->status === 'unpaid') {
                $transferLabel = 'Transfer Pembayaran DP:';
                $transferAmount = $invoice->dp_amount;
            } elseif ($invoice->status === 'dp_paid') {
                $transferLabel = 'Transfer Sisa Pelunasan:';
                $transferAmount = $invoice->remaining_amount;
            } else {
                $transferLabel = 'Status Tagihan:';
                $transferAmount = 0;
            }
        } else {
            $transferLabel = 'Transfer Pembayaran Lunas:';
            $transferAmount = $invoice->total_amount;
        }
    @endphp

    <style>
        .invoice-neon-grid {
            background-color: #ffffff;
            background-image: 
                linear-gradient(to right, rgba(232, 255, 0, 0.08) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(232, 255, 0, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .neon-corner-tl { position: absolute; top: -1px; left: -1px; width: 14px; height: 14px; border-top: 2px solid rgba(232, 255, 0, 0.6); border-left: 2px solid rgba(232, 255, 0, 0.6); }
        .neon-corner-tr { position: absolute; top: -1px; right: -1px; width: 14px; height: 14px; border-top: 2px solid rgba(232, 255, 0, 0.6); border-right: 2px solid rgba(232, 255, 0, 0.6); }
        .neon-corner-bl { position: absolute; bottom: -1px; left: -1px; width: 14px; height: 14px; border-bottom: 2px solid rgba(232, 255, 0, 0.6); border-left: 2px solid rgba(232, 255, 0, 0.6); }
        .neon-corner-br { position: absolute; bottom: -1px; right: -1px; width: 14px; height: 14px; border-bottom: 2px solid rgba(232, 255, 0, 0.6); border-right: 2px solid rgba(232, 255, 0, 0.6); }
    </style>

    <div class="flex justify-center w-full">
        <div id="invoice-document" class="invoice-neon-grid text-on-surface border border-border-subtle shadow-[0_10px_30px_-5px_rgba(0,0,0,0.08)] w-full max-w-[760px] p-6 sm:p-8 flex flex-col justify-between relative rounded-xl box-border overflow-hidden">
            <!-- Neon Corner Accents -->
            <div class="neon-corner-tl"></div>
            <div class="neon-corner-tr"></div>
            <div class="neon-corner-bl"></div>
            <div class="neon-corner-br"></div>

            <!-- Decorative accent bar -->
            <div class="absolute top-0 left-0 w-full h-2 bg-on-surface"></div>
            
            <!-- Enlarged Top Right Neon Tab with "ABT" text -->
            <div class="absolute top-0 right-10 w-20 h-5 sm:h-5.5 bg-primary-container rounded-b-lg shadow-sm flex items-center justify-center">
                <span class="font-black text-[11px] text-on-surface tracking-widest leading-none">ABT</span>
            </div>

            <!-- Top Section (Header, Details, 1-Row Description & Pricing) -->
            <div class="relative z-10">
                <!-- Header with Logo & Invoice Info -->
                <div class="flex justify-between items-start mb-5 pb-4 border-b border-border-subtle">
                    <div class="flex items-center gap-3.5">
                        @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo" class="w-12 h-12 sm:w-14 sm:h-14 object-contain rounded-xl border border-border-subtle p-1 bg-white shadow-sm shrink-0">
                        @endif
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-on-surface leading-tight">
                                {{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}
                            </h1>
                            <p class="text-secondary text-[10px] sm:text-xs uppercase tracking-wider font-semibold mt-0.5">
                                {{ $invoice->category->tagline ?: 'Invoice & Jasa Professional' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-widest text-secondary/20 uppercase mb-0.5">INVOICE</h2>
                        <p class="font-bold text-on-surface text-xs sm:text-sm font-mono tracking-wider">{{ $invoice->invoice_number }}</p>
                        <p class="text-secondary text-[11px] sm:text-xs mt-0.5">{{ $invoice->created_at->translatedFormat('d F Y') }}</p>
                    </div>
                </div>

                <!-- Client Info & Status Badge Row -->
                <div class="grid grid-cols-2 gap-4 sm:gap-6 mb-4 pb-4 border-b border-border-subtle">
                    <div>
                        <h3 class="text-[10px] sm:text-xs font-semibold text-secondary uppercase tracking-widest mb-1">Ditujukan Kepada</h3>
                        <p class="text-base sm:text-lg font-bold text-on-surface leading-snug">{{ $invoice->client_name }}</p>
                        <p class="text-xs text-secondary mt-0.5">Proyek: <span class="font-semibold text-on-surface">{{ $invoice->title }}</span></p>
                    </div>
                    <div class="flex justify-end items-start">
                        @if($invoice->status === 'paid')
                        <div class="inline-flex items-center px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-status-lunas/10 text-status-lunas border border-status-lunas/20">
                            <span class="w-2 h-2 rounded-full bg-status-lunas mr-1.5 sm:mr-2 animate-pulse"></span>
                            <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider">Lunas</span>
                        </div>
                        @elseif($invoice->status === 'dp_paid')
                        <div class="inline-flex items-center px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-status-dp/10 text-status-dp border border-status-dp/20">
                            <span class="w-2 h-2 rounded-full bg-status-dp mr-1.5 sm:mr-2 animate-pulse"></span>
                            <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider">DP Terbayar</span>
                        </div>
                        @elseif($invoice->status === 'canceled')
                        <div class="inline-flex items-center px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-gray-200 dark:bg-[#333] text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">
                            <span class="w-2 h-2 rounded-full bg-gray-500 mr-1.5 sm:mr-2"></span>
                            <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider">Dibatalkan</span>
                        </div>
                        @else
                        <div class="inline-flex items-center px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-status-pending/10 text-status-pending border border-status-pending/20">
                            <span class="w-2 h-2 rounded-full bg-status-pending mr-1.5 sm:mr-2 animate-pulse"></span>
                            <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider">{{ $invoice->payment_type === 'dp' ? 'Belum Bayar DP' : 'Belum Bayar' }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Details Section -->
                <div class="mb-4 pb-4 border-b border-border-subtle grid grid-cols-3 gap-3 sm:gap-6">
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold text-secondary uppercase mb-0.5 sm:mb-1">Kategori Jasa</p>
                        <p class="text-xs sm:text-sm text-on-surface font-semibold">{{ $invoice->category->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold text-secondary uppercase mb-0.5 sm:mb-1">Deadline / Jatuh Tempo</p>
                        <p class="text-xs sm:text-sm text-on-surface font-semibold">{{ $invoice->deadline->format('d M Y, H:i') }} WIB</p>
                    </div>
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold text-secondary uppercase mb-0.5 sm:mb-1">Metode Biaya</p>
                        <p class="text-xs sm:text-sm text-on-surface font-semibold">
                            {{ $invoice->payment_type === 'dp' ? 'Bertahap (Dengan DP)' : 'Bayar Lunas Langsung' }}
                        </p>
                    </div>
                </div>

                <!-- 1 Row: Description (Left) & Pricing Summary (Right) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 items-stretch">
                    <!-- Left Column: Deskripsi Pekerjaan -->
                    <div class="flex flex-col">
                        <p class="text-[10px] sm:text-xs font-semibold text-secondary uppercase mb-1">Deskripsi Pekerjaan</p>
                        <div class="p-3 bg-surface rounded-lg border border-border-subtle text-xs sm:text-sm text-on-surface whitespace-pre-line leading-relaxed flex-1">
                            {{ $invoice->description }}
                        </div>
                    </div>

                    <!-- Right Column: Rincian Biaya (Compact) -->
                    <div class="flex flex-col">
                        <p class="text-[10px] sm:text-xs font-semibold text-secondary uppercase mb-1">Rincian Pembayaran</p>
                        <div class="space-y-1.5 bg-surface/80 p-3 rounded-lg border border-border-subtle flex-1 flex flex-col justify-between">
                            <div class="space-y-1">
                                <div class="flex justify-between items-center text-xs text-secondary">
                                    <span>Total Biaya Proyek</span>
                                    <span class="font-semibold text-on-surface">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                </div>

                                @if($invoice->payment_type === 'dp')
                                    @if($invoice->status === 'unpaid')
                                    <div class="flex justify-between items-center text-xs text-secondary">
                                        <span>Uang Muka (DP) Wajib</span>
                                        <span class="font-bold text-on-surface">Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-[11px] text-secondary">
                                        <span>Sisa Pelunasan Nanti</span>
                                        <span>Rp {{ number_format($invoice->total_amount - $invoice->dp_amount, 0, ',', '.') }}</span>
                                    </div>
                                    @elseif($invoice->status === 'dp_paid')
                                    <div class="flex justify-between items-center text-xs text-secondary">
                                        <span>DP Terbayar</span>
                                        <span class="text-status-lunas font-semibold">- Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</span>
                                    </div>
                                    @else
                                    <div class="flex justify-between items-center text-xs text-secondary">
                                        <span>DP Terbayar</span>
                                        <span class="text-status-lunas font-semibold">Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs text-secondary">
                                        <span>Pelunasan Terbayar</span>
                                        <span class="text-status-lunas font-semibold">Rp {{ number_format($invoice->total_amount - $invoice->dp_amount, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                @endif
                            </div>

                            <div>
                                <div class="h-px bg-border-subtle w-full my-1"></div>
                                <div class="flex justify-between items-center pt-0.5">
                                    @if($invoice->payment_type === 'dp')
                                        @if($invoice->status === 'unpaid')
                                        <span class="text-xs font-bold text-on-surface">Tagihan DP Saat Ini</span>
                                        <span class="text-sm sm:text-base font-bold text-on-surface tracking-tight">Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</span>
                                        @elseif($invoice->status === 'dp_paid')
                                        <span class="text-xs font-bold text-on-surface">Sisa Pelunasan</span>
                                        <span class="text-sm sm:text-base font-bold text-on-surface tracking-tight">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</span>
                                        @elseif($invoice->status === 'canceled')
                                        <span class="text-xs font-bold text-gray-500">Tagihan Dibatalkan</span>
                                        <span class="text-sm sm:text-base font-bold text-gray-500 tracking-tight">Rp 0</span>
                                        @else
                                        <span class="text-xs font-bold text-status-lunas">Sisa Tagihan</span>
                                        <span class="text-sm sm:text-base font-bold text-status-lunas tracking-tight">Rp 0 (LUNAS)</span>
                                        @endif
                                    @else
                                        <span class="text-xs font-bold text-on-surface">{{ $invoice->status === 'paid' ? 'Total Terbayar' : ($invoice->status === 'canceled' ? 'Tagihan Dibatalkan' : 'Total Tagihan') }}</span>
                                        <span class="text-sm sm:text-base font-bold text-on-surface tracking-tight">
                                            {{ $invoice->status === 'paid' ? 'Rp ' . number_format($invoice->total_amount, 0, ',', '.') . ' (LUNAS)' : ($invoice->status === 'canceled' ? 'Rp 0' : 'Rp ' . number_format($invoice->total_amount, 0, ',', '.')) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section (QRIS Expanded + Bank Accounts Compact) -->
            <div class="relative z-10">
                <div class="flex flex-col p-4 border border-border-subtle bg-surface border-dashed rounded-xl space-y-3">
                    <!-- Header Transfer Amount -->
                    <div class="text-center">
                        <p class="text-[10px] sm:text-xs font-semibold text-secondary uppercase tracking-widest mb-1">Instruksi Pembayaran</p>
                        @if($invoice->status === 'paid')
                        <div class="inline-flex items-center gap-1.5 bg-status-lunas/15 text-status-lunas px-4 py-1 rounded-full text-xs sm:text-sm font-bold">
                            <span class="material-symbols-outlined text-base">verified</span>
                            Invoice Ini Telah Dibayar Lunas
                        </div>
                        @elseif($invoice->status === 'canceled')
                        <div class="inline-flex items-center gap-1.5 bg-gray-200 dark:bg-[#333] text-gray-700 dark:text-gray-300 px-4 py-1 rounded-full text-xs sm:text-sm font-bold">
                            <span class="material-symbols-outlined text-base">block</span>
                            Invoice Ini Telah Dibatalkan
                        </div>
                        @else
                        <div class="inline-flex items-center justify-center gap-2 bg-white px-3.5 py-1.5 rounded-lg border border-border-subtle shadow-sm">
                            <span class="text-xs sm:text-sm text-on-surface font-medium">{{ $transferLabel }}</span> 
                            <span class="bg-primary-container text-on-surface px-2.5 py-0.5 rounded font-extrabold text-sm sm:text-base tracking-tight">
                                Rp {{ number_format($transferAmount, 0, ',', '.') }}
                            </span>
                        </div>
                        @endif
                    </div>

                    @if($invoice->status !== 'paid' && $invoice->status !== 'canceled')
                        <!-- QRIS Section (MAXIMIZED & PROMINENT - STRETCHED WIDTH) -->
                        @if($qrisBase64)
                        <div class="text-center bg-white p-3 sm:p-4 rounded-xl border border-border-subtle shadow-sm w-full max-w-md mx-auto">
                            <p class="text-xs font-bold text-on-surface uppercase tracking-wider mb-2">QRIS</p>
                            
                            <!-- Large Stretched Image Box -->
                            <div class="p-1.5 bg-white rounded-lg border border-border-subtle/80 shadow-inner w-full flex items-center justify-center">
                                <img src="{{ $qrisBase64 }}" alt="QRIS" class="w-full max-w-[340px] sm:max-w-[400px] h-auto object-contain mx-auto rounded">
                            </div>

                            <p class="text-[10px] text-secondary font-medium mt-1.5">
                                Scan via BCA Mobile, Livin, BRImo, DANA, GoPay, OVO, ShopeePay, dll
                            </p>
                        </div>
                        @endif

                        <!-- Compact Clean Structured Bank Accounts Row -->
                        <div class="bg-white p-3 sm:p-3.5 rounded-xl border border-border-subtle shadow-sm max-w-xl mx-auto w-full">
                            <p class="text-[10px] font-bold text-secondary uppercase tracking-wider mb-2 text-center">Pilihan Transfer Bank & E-Wallet</p>
                            
                            <div class="grid grid-cols-3 gap-2.5 mb-2">
                                <!-- BCA -->
                                <div class="p-2.5 rounded-lg border border-border-subtle bg-surface flex flex-col items-center justify-between text-center">
                                    <div class="h-7 flex items-center justify-center mb-1">
                                        @if($bcaBase64)
                                        <img src="{{ $bcaBase64 }}" alt="BCA" class="h-6 max-w-[75px] object-contain">
                                        @else
                                        <span class="px-2 py-0.5 bg-[#005EAA] text-white text-[9px] font-black rounded">BCA</span>
                                        @endif
                                    </div>
                                    <div class="w-full bg-white dark:bg-[#252525] py-1 px-1.5 rounded border border-border-subtle flex items-center justify-center">
                                        <span class="font-mono font-bold text-xs text-on-surface tracking-wider">1921252558</span>
                                    </div>
                                </div>

                                <!-- DANA -->
                                <div class="p-2.5 rounded-lg border border-border-subtle bg-surface flex flex-col items-center justify-between text-center">
                                    <div class="h-7 flex items-center justify-center mb-1">
                                        @if($danaBase64)
                                        <img src="{{ $danaBase64 }}" alt="DANA" class="h-6 max-w-[75px] object-contain">
                                        @else
                                        <span class="px-2 py-0.5 bg-[#118EEA] text-white text-[9px] font-black rounded">DANA</span>
                                        @endif
                                    </div>
                                    <div class="w-full bg-white dark:bg-[#252525] py-1 px-1.5 rounded border border-border-subtle flex items-center justify-center">
                                        <span class="font-mono font-bold text-xs text-on-surface tracking-wider">082333362651</span>
                                    </div>
                                </div>

                                <!-- SeaBank -->
                                <div class="p-2.5 rounded-lg border border-border-subtle bg-surface flex flex-col items-center justify-between text-center">
                                    <div class="h-7 flex items-center justify-center mb-1">
                                        @if($seaBase64)
                                        <img src="{{ $seaBase64 }}" alt="SeaBank" class="h-6 max-w-[75px] object-contain">
                                        @else
                                        <span class="px-2 py-0.5 bg-[#FF5722] text-white text-[9px] font-black rounded">SeaBank</span>
                                        @endif
                                    </div>
                                    <div class="w-full bg-white dark:bg-[#252525] py-1 px-1.5 rounded border border-border-subtle flex items-center justify-center">
                                        <span class="font-mono font-bold text-xs text-on-surface tracking-wider">901099053997</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Single Account Name Badge -->
                            <div class="pt-1.5 border-t border-border-subtle text-center">
                                <p class="text-[11px] text-secondary font-medium">
                                    Semua rekening & e-wallet a.n. <strong class="text-on-surface font-bold">ALIEF BADRIT TAMAM</strong>
                                </p>
                                <p class="text-[9.5px] text-secondary mt-0.5">
                                    📌 Mohon konfirmasi bukti transfer setelah pembayaran. Terima kasih 🙏
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="mt-4 pt-2.5 border-t border-border-subtle text-center text-xs text-secondary font-medium">
                    Official Invoice by <strong>{{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
