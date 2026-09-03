<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'surface': '#f9f9f9',
                        'surface-variant': '#e2e2e2',
                        'on-surface': '#1a1c1c',
                        'secondary': '#5d5e60',
                        'border-subtle': '#E4E4E7',
                        'primary-container': '#e8ff00',
                        'status-lunas': '#22C55E',
                        'status-dp': '#3B82F6',
                        'status-pending': '#F59E0B',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { margin: 0; padding: 20px; background: #ffffff; font-family: 'Inter', sans-serif; display: flex; justify-content: center; }
        #invoice-document { width: 760px; max-width: 760px; background: #ffffff; box-sizing: border-box; }
    </style>
</head>
<body>
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

    <div id="invoice-document" class="bg-white text-on-surface border border-border-subtle p-8 flex flex-col justify-between relative rounded-xl shadow-none">
        <!-- Decorative accent bar -->
        <div class="absolute top-0 left-0 w-full h-2 bg-on-surface"></div>
        <div class="absolute top-0 right-10 w-16 h-3.5 bg-primary-container rounded-b-md"></div>

        <!-- Top Section -->
        <div>
            <!-- Header with Logo & Invoice Info -->
            <div class="flex justify-between items-start mb-6 mt-1 pb-5 border-b border-border-subtle">
                <div class="flex items-center gap-3.5">
                    @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo" class="w-14 h-14 object-contain rounded-xl border border-border-subtle p-1 bg-white shadow-sm shrink-0">
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-on-surface leading-tight">
                            {{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}
                        </h1>
                        <p class="text-secondary text-xs uppercase tracking-wider font-semibold mt-0.5">
                            {{ $invoice->category->tagline ?: 'Invoice & Jasa Professional' }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <h2 class="text-3xl font-bold tracking-widest text-secondary/20 uppercase mb-0.5">INVOICE</h2>
                    <p class="font-bold text-on-surface text-sm font-mono tracking-wider">{{ $invoice->invoice_number }}</p>
                    <p class="text-secondary text-xs mt-0.5">{{ $invoice->created_at->translatedFormat('d F Y') }}</p>
                </div>
            </div>

            <!-- Client Info & Status Badge Row -->
            <div class="grid grid-cols-2 gap-6 mb-5 pb-5 border-b border-border-subtle">
                <div>
                    <h3 class="text-xs font-semibold text-secondary uppercase tracking-widest mb-1">Ditujukan Kepada</h3>
                    <p class="text-lg font-bold text-on-surface leading-snug">{{ $invoice->client_name }}</p>
                    <p class="text-xs text-secondary mt-0.5">Proyek: <span class="font-semibold text-on-surface">{{ $invoice->title }}</span></p>
                </div>
                <div class="flex justify-end items-start">
                    @if($invoice->status === 'paid')
                    <div class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-status-lunas/10 text-status-lunas border border-status-lunas/20">
                        <span class="w-2 h-2 rounded-full bg-status-lunas mr-2"></span>
                        <span class="text-xs font-bold uppercase tracking-wider">Lunas</span>
                    </div>
                    @elseif($invoice->status === 'dp_paid')
                    <div class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-status-dp/10 text-status-dp border border-status-dp/20">
                        <span class="w-2 h-2 rounded-full bg-status-dp mr-2"></span>
                        <span class="text-xs font-bold uppercase tracking-wider">DP Terbayar</span>
                    </div>
                    @else
                    <div class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-status-pending/10 text-status-pending border border-status-pending/20">
                        <span class="w-2 h-2 rounded-full bg-status-pending mr-2"></span>
                        <span class="text-xs font-bold uppercase tracking-wider">{{ $invoice->payment_type === 'dp' ? 'Belum Bayar DP' : 'Belum Bayar' }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Details Section -->
            <div class="mb-5 pb-5 border-b border-border-subtle grid grid-cols-3 gap-6">
                <div>
                    <p class="text-xs font-semibold text-secondary uppercase mb-1">Kategori Jasa</p>
                    <p class="text-sm text-on-surface font-semibold">{{ $invoice->category->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-secondary uppercase mb-1">Deadline / Jatuh Tempo</p>
                    <p class="text-sm text-on-surface font-semibold">{{ $invoice->deadline->format('d M Y, H:i') }} WIB</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-secondary uppercase mb-1">Metode Biaya</p>
                    <p class="text-sm text-on-surface font-semibold">
                        {{ $invoice->payment_type === 'dp' ? 'Bertahap (Dengan DP)' : 'Bayar Lunas Langsung' }}
                    </p>
                </div>
            </div>

            <!-- 1 Row: Description (Left) & Pricing Summary (Right) -->
            <div class="grid grid-cols-2 gap-4 mb-5 items-stretch">
                <!-- Left Column: Deskripsi Pekerjaan -->
                <div class="flex flex-col">
                    <p class="text-xs font-semibold text-secondary uppercase mb-1">Deskripsi Pekerjaan</p>
                    <div class="p-3.5 bg-surface rounded-lg border border-border-subtle text-xs text-on-surface whitespace-pre-line leading-relaxed flex-1">
                        {{ $invoice->description }}
                    </div>
                </div>

                <!-- Right Column: Rincian Biaya (Compact) -->
                <div class="flex flex-col">
                    <p class="text-xs font-semibold text-secondary uppercase mb-1">Rincian Pembayaran</p>
                    <div class="space-y-1.5 bg-surface/80 p-3.5 rounded-lg border border-border-subtle flex-1 flex flex-col justify-between">
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
                                    <span class="text-base font-bold text-on-surface tracking-tight">Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</span>
                                    @elseif($invoice->status === 'dp_paid')
                                    <span class="text-xs font-bold text-on-surface">Sisa Pelunasan</span>
                                    <span class="text-base font-bold text-on-surface tracking-tight">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</span>
                                    @else
                                    <span class="text-xs font-bold text-status-lunas">Sisa Tagihan</span>
                                    <span class="text-base font-bold text-status-lunas tracking-tight">Rp 0 (LUNAS)</span>
                                    @endif
                                @else
                                    <span class="text-xs font-bold text-on-surface">{{ $invoice->status === 'paid' ? 'Total Terbayar' : 'Total Tagihan' }}</span>
                                    <span class="text-base font-bold text-on-surface tracking-tight">
                                        {{ $invoice->status === 'paid' ? 'Rp ' . number_format($invoice->total_amount, 0, ',', '.') . ' (LUNAS)' : 'Rp ' . number_format($invoice->total_amount, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section (QRIS Expanded + Bank Accounts Compact) -->
        <div>
            <div class="flex flex-col p-4 border border-border-subtle bg-surface border-dashed rounded-xl space-y-3.5">
                <!-- Header Transfer Amount -->
                <div class="text-center">
                    <p class="text-xs font-semibold text-secondary uppercase tracking-widest mb-1">Instruksi Pembayaran</p>
                    @if($invoice->status === 'paid')
                    <div class="inline-flex items-center gap-1.5 bg-status-lunas/15 text-status-lunas px-4 py-1.5 rounded-full text-sm font-bold">
                        Invoice Ini Telah Dibayar Lunas
                    </div>
                    @else
                    <div class="inline-flex items-center justify-center gap-2 bg-white px-4 py-1.5 rounded-lg border border-border-subtle shadow-sm">
                        <span class="text-xs text-on-surface font-medium">{{ $transferLabel }}</span> 
                        <span class="bg-primary-container text-on-surface px-2.5 py-0.5 rounded font-extrabold text-sm tracking-tight">
                            Rp {{ number_format($transferAmount, 0, ',', '.') }}
                        </span>
                    </div>
                    @endif
                </div>

                @if($invoice->status !== 'paid')
                    <!-- QRIS Section (Centered & Maximized) -->
                    @if($qrisBase64)
                    <div class="text-center bg-white p-4 rounded-xl border border-border-subtle shadow-sm w-full max-w-md mx-auto">
                        <p class="text-xs font-bold text-on-surface uppercase tracking-wider mb-2">QRIS</p>
                        
                        <div class="p-2 bg-white rounded-lg border border-border-subtle inline-block w-full max-w-[340px]">
                            <img src="{{ $qrisBase64 }}" alt="QRIS" class="w-full h-auto object-contain mx-auto rounded">
                        </div>

                        <p class="text-[11px] text-secondary font-medium mt-1.5">
                            Scan via BCA Mobile, Livin, BRImo, DANA, GoPay, OVO, ShopeePay, dll
                        </p>
                    </div>
                    @endif

                    <!-- Compact Clean Structured Bank Accounts Row -->
                    <div class="bg-white p-3.5 rounded-xl border border-border-subtle shadow-sm max-w-xl mx-auto w-full">
                        <p class="text-[10px] font-bold text-secondary uppercase tracking-wider mb-2 text-center">Pilihan Transfer Bank & E-Wallet</p>
                        
                        <div class="grid grid-cols-3 gap-3 mb-2">
                            <!-- BCA -->
                            <div class="p-2.5 rounded-lg border border-border-subtle bg-surface flex flex-col items-center justify-between text-center">
                                <div class="h-6 flex items-center justify-center mb-1">
                                    @if($bcaBase64)
                                    <img src="{{ $bcaBase64 }}" alt="BCA" class="h-5 max-w-[70px] object-contain">
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
                                <div class="h-6 flex items-center justify-center mb-1">
                                    @if($danaBase64)
                                    <img src="{{ $danaBase64 }}" alt="DANA" class="h-5 max-w-[70px] object-contain">
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
                                <div class="h-6 flex items-center justify-center mb-1">
                                    @if($seaBase64)
                                    <img src="{{ $seaBase64 }}" alt="SeaBank" class="h-5 max-w-[70px] object-contain">
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
                        <div class="pt-2 border-t border-border-subtle text-center">
                            <p class="text-xs text-secondary font-medium">
                                Semua rekening & e-wallet a.n. <strong class="text-on-surface font-bold">ALIEF BADRIT TAMAM</strong>
                            </p>
                            <p class="text-[10px] text-secondary mt-0.5">
                                📌 Mohon konfirmasi bukti transfer setelah pembayaran. Terima kasih 🙏
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="mt-4 pt-3 border-t border-border-subtle text-center text-xs text-secondary font-medium">
                Official Invoice by <strong>{{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}</strong>
            </div>
        </div>
    </div>
</body>
</html>
