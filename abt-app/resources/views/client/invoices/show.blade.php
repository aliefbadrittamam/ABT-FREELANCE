<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }} — {{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#000000',
                        'primary-container': '#E8FF00',
                        'on-surface': '#111111',
                        'secondary': '#666666',
                        'border-subtle': '#e5e7eb',
                        'surface': '#f9fafb',
                        'status-lunas': '#059669',
                        'status-dp': '#2563eb',
                        'status-pending': '#d97706',
                        'status-cancel': '#dc2626',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['Space Grotesk', 'monospace'],
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#f8fafc] text-on-surface font-sans antialiased min-h-screen flex flex-col justify-between" x-data="{ copied: false }">

    <!-- Top Public Navbar -->
    <header class="bg-white border-b border-border-subtle sticky top-0 z-30 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" class="w-8 h-8 rounded-lg object-contain border border-border-subtle p-0.5 bg-white shadow-xs">
                @endif
                <div>
                    <span class="text-sm font-extrabold text-on-surface tracking-tight">{{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}</span>
                    <span class="hidden sm:inline-block text-[10px] text-secondary font-semibold uppercase tracking-wider ml-1.5 px-2 py-0.5 bg-gray-100 rounded-full">Customer Portal</span>
                </div>
            </div>

            <!-- Status Badge in Navbar -->
            <div class="flex items-center gap-2.5">
                @if($invoice->status === 'paid')
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-status-lunas/10 text-status-lunas border border-status-lunas/20 px-3 py-1 rounded-full shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-status-lunas animate-pulse"></span>
                    LUNAS
                </span>
                @elseif($invoice->status === 'dp_paid')
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-status-dp/10 text-status-dp border border-status-dp/20 px-3 py-1 rounded-full shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-status-dp animate-pulse"></span>
                    DP TERBAYAR
                </span>
                @elseif($invoice->status === 'canceled')
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-gray-200 text-gray-700 border border-gray-300 px-3 py-1 rounded-full shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                    DIBATALKAN
                </span>
                @else
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-status-pending/10 text-status-pending border border-status-pending/20 px-3 py-1 rounded-full shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-status-pending animate-pulse"></span>
                    {{ $invoice->payment_type === 'dp' ? 'BELUM BAYAR DP' : 'BELUM BAYAR' }}
                </span>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8 w-full flex-1 space-y-6">

        <!-- Top Actions & Status Stepper (Exact mirror of admin view) -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white p-4 sm:p-5 rounded-xl border border-border-subtle shadow-sm">
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
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100 text-red-700 text-xs font-bold">
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
                    <span class="ml-1.5 sm:ml-2 text-xs sm:text-sm {{ !$isUnpaid ? 'text-secondary line-through' : 'font-semibold text-on-surface' }}">
                        {{ $invoice->payment_type === 'dp' ? 'Belum DP' : 'Belum Bayar' }}
                    </span>
                </div>

                <div class="w-6 sm:w-8 h-[2px] shrink-0 {{ !$isUnpaid ? 'bg-status-lunas' : 'bg-border-subtle' }}"></div>

                <!-- Step 2: DP Terbayar (if DP type) -->
                @if($invoice->payment_type === 'dp')
                <div class="flex items-center shrink-0">
                    <div class="h-6 w-6 rounded-full {{ $isPaid ? 'bg-status-lunas text-white' : ($isDpPaid ? 'bg-primary-container border-2 border-on-surface' : 'border-2 border-border-subtle') }} flex items-center justify-center">
                        @if($isPaid)
                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
                        @elseif($isDpPaid)
                        <span class="w-2 h-2 rounded-full bg-on-surface"></span>
                        @endif
                    </div>
                    <span class="ml-1.5 sm:ml-2 text-xs sm:text-sm {{ $isPaid ? 'text-secondary line-through' : ($isDpPaid ? 'font-semibold text-on-surface' : 'text-secondary') }}">DP Terbayar</span>
                </div>
                <div class="w-6 sm:w-8 h-[2px] shrink-0 {{ $isPaid ? 'bg-status-lunas' : 'bg-border-subtle' }}"></div>
                @endif

                <!-- Step 3: Lunas -->
                <div class="flex items-center shrink-0 {{ !$isPaid ? 'opacity-60' : '' }}">
                    <div class="h-6 w-6 rounded-full {{ $isPaid ? 'bg-status-lunas text-white' : 'border-2 border-border-subtle' }} flex items-center justify-center">
                        @if($isPaid)
                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
                        @endif
                    </div>
                    <span class="ml-1.5 sm:ml-2 text-xs sm:text-sm {{ $isPaid ? 'font-semibold text-on-surface' : 'text-secondary' }}">Lunas</span>
                </div>
                @endif
            </div>

            <!-- Action Buttons for Customer -->
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto pt-2 lg:pt-0 border-t lg:border-t-0 border-border-subtle">
                <a href="{{ route('client.invoices.export', [$invoice->access_token, 'png']) }}" 
                   class="px-3.5 py-2 border-2 border-primary-container bg-transparent text-on-surface font-semibold text-xs sm:text-sm rounded-lg hover:bg-primary-container/10 transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">image</span>
                    Export Gambar (PNG)
                </a>

                <a href="{{ route('client.invoices.export', [$invoice->access_token, 'pdf']) }}" 
                   class="px-4 py-2 bg-primary-container text-on-surface font-semibold text-xs sm:text-sm rounded-lg hover:brightness-95 transition-colors shadow-sm flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">download</span>
                    Export PDF
                </a>

                <a href="{{ $invoice->getWhatsAppConfirmationUrl() }}" target="_blank" rel="noopener noreferrer" 
                   class="px-4 py-2 bg-[#25D366] text-white font-semibold text-xs sm:text-sm rounded-lg hover:brightness-95 transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Konfirmasi WhatsApp
                </a>
            </div>
        </div>

        <!-- Task File Archive Card (If exists) -->
        @if($invoice->task_file_path)
        <div class="bg-white rounded-xl border border-border-subtle p-4 sm:p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200">
                        <span class="material-symbols-outlined text-xl">folder_zip</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-on-surface flex items-center gap-2">
                            Hasil File Tugas & Pengerjaan
                            <span class="text-[10px] bg-status-lunas/15 text-status-lunas font-semibold px-2 py-0.5 rounded-full">Siap Diunduh</span>
                        </h3>
                        <p class="text-xs text-secondary mt-0.5">
                            File: <span class="font-medium text-on-surface">{{ $invoice->task_file_name }}</span>
                        </p>
                    </div>
                </div>

                <a href="{{ route('client.invoices.downloadTaskFile', $invoice->access_token) }}" 
                   class="px-4 py-2 bg-emerald-600 text-white font-semibold text-xs rounded-lg hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-base">download</span>
                    Download File Tugas
                </a>
            </div>
        </div>
        @endif

        @php
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

        <!-- Invoice Document (EXACT 100% IDENTICAL STRUCTURE) -->
        <div class="overflow-x-auto w-full flex justify-center pb-6">
            <div class="w-full max-w-[760px] min-w-[620px] sm:min-w-0">
                <div id="invoice-document" class="invoice-neon-grid text-on-surface border border-border-subtle shadow-[0_10px_30px_-5px_rgba(0,0,0,0.08)] w-full p-6 sm:p-8 flex flex-col justify-between relative rounded-xl box-border overflow-hidden bg-white">
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
                                <div class="inline-flex items-center px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-gray-200 text-gray-700 border border-gray-300">
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
                                <p class="text-xs sm:text-sm text-on-surface font-semibold">{{ $invoice->deadline ? $invoice->deadline->format('d M Y, H:i') . ' WIB' : '-' }}</p>
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
                                <div class="inline-flex items-center gap-1.5 bg-gray-200 text-gray-700 px-4 py-1 rounded-full text-xs sm:text-sm font-bold">
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
                                            <div class="w-full bg-white py-1 px-1.5 rounded border border-border-subtle flex items-center justify-center">
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
                                            <div class="w-full bg-white py-1 px-1.5 rounded border border-border-subtle flex items-center justify-center">
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
                                            <div class="w-full bg-white py-1 px-1.5 rounded border border-border-subtle flex items-center justify-center">
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
    </main>

    <!-- Public Footer -->
    <footer class="bg-white border-t border-border-subtle py-4 text-center text-xs text-secondary">
        <p>&copy; {{ date('Y') }} {{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}. All rights reserved.</p>
    </footer>

</body>
</html>
