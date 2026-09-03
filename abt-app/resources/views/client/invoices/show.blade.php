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
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full shadow-xs">
                    <span class="material-symbols-outlined text-sm">verified</span>
                    Lunas
                </span>
                @elseif($invoice->status === 'dp_paid')
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-full shadow-xs">
                    <span class="material-symbols-outlined text-sm">payments</span>
                    DP Terbayar
                </span>
                @elseif($invoice->status === 'canceled')
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-red-50 text-red-700 border border-red-200 px-3 py-1 rounded-full shadow-xs">
                    <span class="material-symbols-outlined text-sm">cancel</span>
                    Dibatalkan
                </span>
                @else
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full shadow-xs">
                    <span class="material-symbols-outlined text-sm">schedule</span>
                    {{ $invoice->payment_type === 'dp' ? 'Belum Bayar DP' : 'Belum Lunas' }}
                </span>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8 w-full flex-1 space-y-6">

        <!-- Visual Progress Stepper Tracker -->
        <div class="bg-white rounded-2xl border border-border-subtle p-5 sm:p-6 shadow-sm">
            <h2 class="text-xs font-bold uppercase tracking-wider text-secondary mb-4 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-base">timeline</span>
                Status Proyek & Pembayaran
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 relative">
                <!-- Step 1: Invoice Created -->
                <div class="flex flex-col items-center sm:items-start text-center sm:text-left">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs mb-2">
                        <span class="material-symbols-outlined text-sm">check</span>
                    </div>
                    <p class="text-xs font-bold text-on-surface">1. Invoice Dibuat</p>
                    <p class="text-[11px] text-secondary mt-0.5">{{ $invoice->created_at->translatedFormat('d M Y') }}</p>
                </div>

                <!-- Step 2: DP / Full Payment -->
                <div class="flex flex-col items-center sm:items-start text-center sm:text-left">
                    @if($invoice->status === 'dp_paid' || $invoice->status === 'paid')
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs mb-2">
                        <span class="material-symbols-outlined text-sm">check</span>
                    </div>
                    <p class="text-xs font-bold text-on-surface">2. {{ $invoice->payment_type === 'dp' ? 'DP Terbayar' : 'Pembayaran Lunas' }}</p>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-0.5">Terverifikasi</p>
                    @else
                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs mb-2 animate-pulse">
                        <span class="material-symbols-outlined text-sm">pending</span>
                    </div>
                    <p class="text-xs font-bold text-on-surface">2. {{ $invoice->payment_type === 'dp' ? 'Uang Muka (DP)' : 'Pembayaran Lunas' }}</p>
                    <p class="text-[11px] text-amber-600 font-semibold mt-0.5">Menunggu Transfer</p>
                    @endif
                </div>

                <!-- Step 3: Project Progress / File Tugas -->
                <div class="flex flex-col items-center sm:items-start text-center sm:text-left">
                    @if($invoice->task_file_path)
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs mb-2">
                        <span class="material-symbols-outlined text-sm">download_done</span>
                    </div>
                    <p class="text-xs font-bold text-on-surface">3. Hasil Pengerjaan</p>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-0.5">File Siap Diunduh</p>
                    @elseif($invoice->status === 'dp_paid' || $invoice->status === 'paid')
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs mb-2 animate-pulse">
                        <span class="material-symbols-outlined text-sm">engineering</span>
                    </div>
                    <p class="text-xs font-bold text-on-surface">3. Pengerjaan Proyek</p>
                    <p class="text-[11px] text-blue-600 font-semibold mt-0.5">Sedang Diproses</p>
                    @else
                    <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-xs mb-2">
                        3
                    </div>
                    <p class="text-xs font-bold text-gray-400">3. Pengerjaan Proyek</p>
                    <p class="text-[11px] text-secondary mt-0.5">Setelah Pembayaran</p>
                    @endif
                </div>

                <!-- Step 4: Completed -->
                <div class="flex flex-col items-center sm:items-start text-center sm:text-left">
                    @if($invoice->status === 'paid')
                    <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs mb-2 shadow-sm">
                        <span class="material-symbols-outlined text-sm">verified</span>
                    </div>
                    <p class="text-xs font-bold text-emerald-700">4. Selesai (Lunas)</p>
                    <p class="text-[11px] text-secondary mt-0.5">{{ $invoice->paid_at ? $invoice->paid_at->translatedFormat('d M Y') : 'Terima Kasih 🙏' }}</p>
                    @else
                    <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-xs mb-2">
                        4
                    </div>
                    <p class="text-xs font-bold text-gray-400">4. Pelunasan & Selesai</p>
                    <p class="text-[11px] text-secondary mt-0.5">Tahap Akhir</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Bar: Download Options & WhatsApp Confirmation -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-border-subtle shadow-xs">
            <div class="flex items-center gap-2">
                <a href="{{ route('client.invoices.export', [$invoice->access_token, 'pdf']) }}" 
                   class="px-4 py-2 bg-primary-container text-on-surface rounded-xl text-xs font-bold hover:brightness-95 transition flex items-center gap-1.5 shadow-xs">
                    <span class="material-symbols-outlined text-base">download</span>
                    Download PDF
                </a>
                <a href="{{ route('client.invoices.export', [$invoice->access_token, 'png']) }}" 
                   class="px-4 py-2 bg-white border border-border-subtle text-on-surface rounded-xl text-xs font-bold hover:bg-gray-50 transition flex items-center gap-1.5 shadow-xs">
                    <span class="material-symbols-outlined text-base">image</span>
                    Download Gambar (PNG)
                </a>
            </div>

            <div class="flex items-center gap-2">
                @if($invoice->task_file_path)
                <a href="{{ route('client.invoices.downloadTaskFile', $invoice->access_token) }}" 
                   class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-base">folder_zip</span>
                    Download Hasil Tugas
                </a>
                @endif

                <a href="{{ $invoice->getWhatsAppConfirmationUrl() }}" target="_blank" rel="noopener noreferrer" 
                   class="px-4 py-2 bg-[#25D366] text-white rounded-xl text-xs font-bold hover:brightness-95 transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Konfirmasi WhatsApp
                </a>
            </div>
        </div>

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

        <!-- Invoice Document (Preserved Aspect Ratio Container) -->
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
                    <div class="absolute top-0 right-10 w-20 h-5 sm:h-5.5 bg-primary-container rounded-b-lg shadow-xs flex items-center justify-center">
                        <span class="font-black text-[11px] text-on-surface tracking-widest leading-none">ABT</span>
                    </div>

                    <!-- Top Section (Header, Details, 1-Row Description & Pricing) -->
                    <div class="relative z-10">
                        <!-- Header with Logo & Invoice Info -->
                        <div class="flex justify-between items-start mb-5 pb-4 border-b border-border-subtle">
                            <div class="flex items-center gap-3.5">
                                @if($logoBase64)
                                <img src="{{ $logoBase64 }}" alt="Logo" class="w-12 h-12 sm:w-14 sm:h-14 object-contain rounded-xl border border-border-subtle p-1 bg-white shadow-xs shrink-0">
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
                                <h2 class="text-2xl sm:text-3xl font-bold tracking-widest text-secondary/20 uppercase mb-0.5 font-mono">INVOICE</h2>
                                <p class="font-bold text-on-surface text-xs sm:text-sm font-mono tracking-wider">{{ $invoice->invoice_number }}</p>
                                <p class="text-secondary text-[11px] sm:text-xs mt-0.5">{{ $invoice->created_at->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>

                        <!-- Client & Invoice Meta Info -->
                        <div class="flex justify-between items-start mb-5">
                            <div>
                                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-secondary">Ditujukan Kepada</p>
                                <h3 class="text-base sm:text-lg font-bold text-on-surface mt-0.5">{{ $invoice->client_name }}</h3>
                                <p class="text-xs sm:text-sm text-secondary">Proyek: <span class="font-medium text-on-surface">{{ $invoice->title }}</span></p>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold tracking-wide uppercase shadow-xs {{ $invoice->status === 'paid' ? 'bg-status-lunas/10 text-status-lunas border border-status-lunas/20' : ($invoice->status === 'dp_paid' ? 'bg-status-dp/10 text-status-dp border border-status-dp/20' : ($invoice->status === 'canceled' ? 'bg-status-cancel/10 text-status-cancel border border-status-cancel/20' : 'bg-status-pending/10 text-status-pending border border-status-pending/20')) }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $invoice->status === 'paid' ? 'bg-status-lunas' : ($invoice->status === 'dp_paid' ? 'bg-status-dp' : ($invoice->status === 'canceled' ? 'bg-status-cancel' : 'bg-status-pending')) }}"></span>
                                    {{ $invoice->status === 'paid' ? 'LUNAS' : ($invoice->status === 'dp_paid' ? 'DP TERBAYAR' : ($invoice->status === 'canceled' ? 'DIBATALKAN' : ($invoice->payment_type === 'dp' ? 'BELUM BAYAR DP' : 'BELUM DIBAYAR'))) }}
                                </span>
                            </div>
                        </div>

                        <!-- 3-Column Info Meta Bar -->
                        <div class="grid grid-cols-3 gap-2 bg-[#f9fafb] p-3 rounded-lg border border-border-subtle mb-5 text-center text-xs">
                            <div class="border-r border-border-subtle pr-2">
                                <span class="text-[10px] text-secondary font-semibold uppercase tracking-wider block mb-0.5">Kategori Jasa</span>
                                <span class="font-bold text-on-surface text-[11px] sm:text-xs">{{ $invoice->category->name }}</span>
                            </div>
                            <div class="border-r border-border-subtle px-2">
                                <span class="text-[10px] text-secondary font-semibold uppercase tracking-wider block mb-0.5">Deadline / Jatuh Tempo</span>
                                <span class="font-bold text-on-surface text-[11px] sm:text-xs">{{ $invoice->deadline ? $invoice->deadline->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}</span>
                            </div>
                            <div class="pl-2">
                                <span class="text-[10px] text-secondary font-semibold uppercase tracking-wider block mb-0.5">Metode Biaya</span>
                                <span class="font-bold text-on-surface text-[11px] sm:text-xs">{{ $invoice->payment_type === 'dp' ? 'Bertahap (Dengan DP)' : 'Pembayaran Penuh' }}</span>
                            </div>
                        </div>

                        <!-- Single Row Side-by-Side: Description (Left) & Pricing Breakdown (Right) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <!-- Left: Description Box -->
                            <div class="flex flex-col h-full">
                                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-secondary mb-1.5">Deskripsi Pekerjaan</p>
                                <div class="bg-white p-3.5 rounded-lg border border-border-subtle text-xs text-on-surface leading-relaxed whitespace-pre-line flex-1">
                                    {{ $invoice->description ?: 'Pengerjaan sesuai dengan kesepakatan dan kebutuhan proyek yang tertera.' }}
                                </div>
                            </div>

                            <!-- Right: Pricing Breakdown Table -->
                            <div class="flex flex-col h-full">
                                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-secondary mb-1.5">Rincian Pembayaran</p>
                                <div class="bg-white rounded-lg border border-border-subtle p-3.5 flex flex-col justify-between flex-1 space-y-2 text-xs">
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between py-1 text-secondary">
                                            <span>Total Biaya Proyek</span>
                                            <span class="font-mono font-bold text-on-surface">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                        </div>

                                        @if($invoice->payment_type === 'dp')
                                        <div class="flex justify-between py-1 text-secondary">
                                            <span>Uang Muka (DP) Wajib</span>
                                            <span class="font-mono font-bold text-status-dp">Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 text-secondary">
                                            <span>Sisa Pelunasan Nanti</span>
                                            <span class="font-mono font-semibold text-secondary">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Highlight Target Payment Box -->
                                    <div class="pt-2 border-t border-border-subtle flex justify-between items-center bg-gray-50 -mx-3.5 -mb-3.5 p-3 rounded-b-lg">
                                        <span class="font-bold text-on-surface text-xs sm:text-sm">
                                            @if($invoice->status === 'paid')
                                                Total Telah Lunas
                                            @elseif($invoice->status === 'dp_paid')
                                                Sisa Pelunasan
                                            @elseif($invoice->payment_type === 'dp')
                                                Tagihan DP Saat Ini
                                            @else
                                                Total Tagihan
                                            @endif
                                        </span>
                                        <span class="font-mono font-black text-sm sm:text-base {{ $invoice->status === 'paid' ? 'text-status-lunas' : 'text-on-surface' }}">
                                            @if($invoice->status === 'paid')
                                                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                            @elseif($invoice->status === 'dp_paid')
                                                Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}
                                            @elseif($invoice->payment_type === 'dp')
                                                Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}
                                            @else
                                                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Section: Payment Methods & Footer -->
                    <div class="relative z-10 pt-4 border-t border-border-subtle">
                        @if($invoice->status === 'paid')
                            <!-- Paid Stamp Banner -->
                            <div class="bg-status-lunas/10 border border-status-lunas/30 rounded-xl p-4 text-center">
                                <span class="material-symbols-outlined text-status-lunas text-3xl">verified</span>
                                <h4 class="font-bold text-status-lunas text-sm sm:text-base mt-1">TAGIHAN INI TELAH LUNAS</h4>
                                <p class="text-xs text-secondary mt-0.5">Terima kasih atas kepercayaan Anda menggunakan layanan {{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}.</p>
                            </div>
                        @elseif($invoice->status === 'canceled')
                            <!-- Canceled Stamp Banner -->
                            <div class="bg-status-cancel/10 border border-status-cancel/30 rounded-xl p-4 text-center">
                                <span class="material-symbols-outlined text-status-cancel text-3xl">cancel</span>
                                <h4 class="font-bold text-status-cancel text-sm sm:text-base mt-1">INVOICE INI TELAH DIBATALKAN</h4>
                                <p class="text-xs text-secondary mt-0.5">Invoice tidak berlaku lagi untuk pembayaran.</p>
                            </div>
                        @else
                            <!-- Payment Instructions Header -->
                            <div class="text-center mb-3">
                                <span class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-secondary block">Instruksi Pembayaran</span>
                                <div class="inline-flex items-center gap-2 mt-1">
                                    <span class="text-xs font-medium text-secondary">{{ $transferLabel }}</span>
                                    <span class="text-xs sm:text-sm font-extrabold font-mono text-on-surface bg-primary-container px-2 py-0.5 rounded shadow-xs">
                                        Rp {{ number_format($transferAmount, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <!-- 2-Column Payment Gateway Layout: QRIS (Left) & Bank Transfer (Right) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch">
                                <!-- QRIS Payment Box -->
                                <div class="bg-[#fafafa] rounded-xl border border-border-subtle p-3.5 flex flex-col items-center justify-between text-center">
                                    <div class="w-full flex items-center justify-between border-b border-border-subtle pb-2 mb-2">
                                        <span class="text-[11px] font-bold tracking-wider uppercase text-on-surface flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm text-primary">qr_code_2</span>
                                            QRIS Semua Pembayaran
                                        </span>
                                        <span class="text-[9px] font-semibold bg-primary-container px-1.5 py-0.5 rounded text-on-surface">Auto Scan</span>
                                    </div>

                                    @if($qrisBase64)
                                    <div class="p-1.5 bg-white rounded-lg border border-border-subtle shadow-xs max-w-[200px] w-full">
                                        <img src="{{ $qrisBase64 }}" alt="QRIS" class="w-full aspect-square object-contain rounded">
                                    </div>
                                    @endif

                                    <p class="text-[9.5px] text-secondary mt-2">
                                        Scan dengan GoPay, OVO, DANA, ShopeePay, BCA Mobile, Livin, dll.
                                    </p>
                                </div>

                                <!-- Bank Accounts Box -->
                                <div class="bg-[#fafafa] rounded-xl border border-border-subtle p-3.5 flex flex-col justify-between">
                                    <div class="border-b border-border-subtle pb-2 mb-2 flex justify-between items-center">
                                        <span class="text-[11px] font-bold tracking-wider uppercase text-on-surface flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm text-primary">account_balance</span>
                                            Transfer Bank & E-Wallet
                                        </span>
                                    </div>

                                    <div class="space-y-2">
                                        <!-- BCA -->
                                        @if(!empty($settings['bca_account']))
                                        <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-border-subtle text-xs">
                                            <div class="flex items-center gap-2">
                                                @if($bcaBase64)
                                                <img src="{{ $bcaBase64 }}" alt="BCA" class="h-4 object-contain">
                                                @else
                                                <span class="font-bold text-[10px]">BCA</span>
                                                @endif
                                            </div>
                                            <span class="font-mono font-bold text-on-surface">{{ $settings['bca_account'] }}</span>
                                        </div>
                                        @endif

                                        <!-- DANA -->
                                        @if(!empty($settings['dana_account']))
                                        <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-border-subtle text-xs">
                                            <div class="flex items-center gap-2">
                                                @if($danaBase64)
                                                <img src="{{ $danaBase64 }}" alt="DANA" class="h-4 object-contain">
                                                @else
                                                <span class="font-bold text-[10px]">DANA</span>
                                                @endif
                                            </div>
                                            <span class="font-mono font-bold text-on-surface">{{ $settings['dana_account'] }}</span>
                                        </div>
                                        @endif

                                        <!-- SeaBank -->
                                        @if(!empty($settings['seabank_account']))
                                        <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-border-subtle text-xs">
                                            <div class="flex items-center gap-2">
                                                @if($seaBase64)
                                                <img src="{{ $seaBase64 }}" alt="SeaBank" class="h-4 object-contain">
                                                @else
                                                <span class="font-bold text-[10px]">SeaBank</span>
                                                @endif
                                            </div>
                                            <span class="font-mono font-bold text-on-surface">{{ $settings['seabank_account'] }}</span>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="mt-2 pt-2 border-t border-border-subtle text-center">
                                        <p class="text-[10px] text-secondary font-medium">
                                            Semua rekening & e-wallet a.n. <strong class="text-on-surface font-bold">ALIEF BADRIT TAMAM</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Footer Note -->
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
