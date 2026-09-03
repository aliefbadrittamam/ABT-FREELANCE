<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        @page page-landscape {
            size: A4 landscape;
            margin: 8mm;
        }

        @page page-portrait {
            size: A4 portrait;
            margin: 8mm;
        }

        body { 
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif; 
            color: #1a1c1c; 
            background: #ffffff;
            width: 100%;
        }

        /* Page 1 (Landscape) */
        .page-landscape {
            page: page-landscape;
            break-after: page;
            border: 1px solid #E4E4E7;
            padding: 24px 30px;
            position: relative;
            background: #ffffff;
            min-height: 188mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Page 2 (Portrait) */
        .page-portrait {
            page: page-portrait;
            border: 1px solid #E4E4E7;
            padding: 26px 30px;
            position: relative;
            background: #ffffff;
            min-height: 275mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .accent-top {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: #1a1c1c;
        }
        .accent-neon {
            position: absolute;
            top: 0;
            right: 40px;
            width: 50px;
            height: 14px;
            background: #E8FF00;
        }

        .header { 
            display: table; 
            width: 100%; 
            margin-bottom: 18px; 
            margin-top: 4px;
            padding-bottom: 14px; 
            border-bottom: 1px solid #E4E4E7; 
        }
        .header-left { display: table-cell; vertical-align: middle; width: 65%; }
        .header-right { display: table-cell; vertical-align: top; width: 35%; text-align: right; }

        .logo-box {
            display: table-cell;
            vertical-align: middle;
            width: 52px;
            padding-right: 12px;
        }
        .logo-img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }

        .brand-text-box {
            display: table-cell;
            vertical-align: middle;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: #1a1c1c;
            letter-spacing: -0.02em;
        }
        .brand-sub {
            font-size: 8.5px;
            font-weight: 600;
            color: #5d5e60;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 2px;
        }

        .invoice-watermark {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.1em;
            color: #E4E4E7;
            text-transform: uppercase;
        }
        .invoice-no {
            font-family: monospace;
            font-size: 11.5px;
            font-weight: 700;
            color: #1a1c1c;
            margin-top: 2px;
        }
        .invoice-date {
            font-size: 9.5px;
            color: #5d5e60;
            margin-top: 2px;
        }

        .client-section {
            display: table;
            width: 100%;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #E4E4E7;
        }
        .client-left { display: table-cell; vertical-align: top; width: 70%; }
        .client-right { display: table-cell; vertical-align: top; width: 30%; text-align: right; }

        .section-label {
            font-size: 8px;
            font-weight: 700;
            color: #5d5e60;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 2px;
        }
        .client-name {
            font-size: 15px;
            font-weight: 700;
            color: #1a1c1c;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-paid { background: #DCFCE7; color: #15803D; }
        .status-dp { background: #DBEAFE; color: #1D4ED8; }
        .status-unpaid { background: #FEF3C7; color: #B45309; }

        .details-grid {
            display: table;
            width: 100%;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #E4E4E7;
        }
        .detail-col { display: table-cell; width: 33.33%; }
        .detail-val { font-size: 11px; font-weight: 600; color: #1a1c1c; margin-top: 2px; }

        /* 1 Row 2 Columns for Description & Pricing */
        .desc-pricing-table {
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }
        .desc-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 8px;
        }
        .pricing-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 8px;
        }

        .desc-box {
            background: #F9F9F9;
            border: 1px solid #E4E4E7;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 80px;
        }
        .desc-text {
            font-size: 10px;
            color: #1a1c1c;
            white-space: pre-line;
            line-height: 1.45;
        }

        .pricing-box {
            background: #F9F9F9;
            border: 1px solid #E4E4E7;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 80px;
        }
        .pricing-row { display: table; width: 100%; margin-bottom: 3px; font-size: 10px; }
        .pricing-left { display: table-cell; text-align: left; color: #5d5e60; }
        .pricing-right { display: table-cell; text-align: right; font-weight: 600; color: #1a1c1c; }
        .pricing-divider { border-top: 1px solid #E4E4E7; padding-top: 5px; margin-top: 5px; }
        .total-label { font-size: 11px; font-weight: 800; color: #1a1c1c; }
        .total-val { font-size: 14px; font-weight: 800; color: #1a1c1c; }

        .notice-box {
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-radius: 6px;
            padding: 10px 14px;
            margin-top: 10px;
            text-align: center;
        }
        .notice-title { font-weight: 700; color: #92400E; font-size: 10.5px; margin-bottom: 2px; }
        .notice-sub { font-size: 9.5px; color: #B45309; }

        /* Page 2 Specific Styling (Portrait) */
        .payment-card {
            background: #F9F9F9;
            border: 1px dashed #E4E4E7;
            border-radius: 8px;
            padding: 18px;
            margin: 16px 0;
            text-align: center;
        }
        .neon-pill { 
            background: #E8FF00; 
            color: #1a1c1c; 
            padding: 3px 10px; 
            border-radius: 4px; 
            font-weight: 800; 
            font-size: 13px; 
            display: inline-block;
            margin-left: 6px;
        }

        .qris-wrapper {
            background: #ffffff;
            border: 1px solid #E4E4E7;
            border-radius: 8px;
            padding: 16px;
            max-width: 320px;
            margin: 14px auto;
            text-align: center;
        }
        .qris-img {
            max-width: 280px;
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .bank-grid-table {
            display: table;
            width: 100%;
            background: #ffffff;
            border: 1px solid #E4E4E7;
            border-radius: 6px;
            padding: 12px;
            margin: 16px auto 10px auto;
            max-width: 500px;
        }
        .bank-col {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 6px 10px;
            border-right: 1px solid #F0F0F0;
        }
        .bank-col:last-child { border-right: none; }

        .bank-logo-img { 
            height: 22px; 
            max-width: 75px; 
            margin: 0 auto 4px auto; 
            display: block; 
        }
        .bank-number { 
            font-family: monospace; 
            font-weight: 700; 
            font-size: 11px; 
            color: #1a1c1c; 
            margin-top: 4px; 
            background: #F9F9F9;
            padding: 2px 4px;
            border-radius: 3px;
        }

        .footer {
            padding-top: 12px;
            border-top: 1px solid #E4E4E7;
            text-align: center;
            font-size: 9px;
            color: #5d5e60;
        }
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

    <!-- ================= PAGE 1: DETAIL PROYEK (A4 LANDSCAPE) ================= -->
    <div class="page-landscape">
        <div>
            <div class="accent-top"></div>
            <div class="accent-neon"></div>

            <!-- Header with Logo -->
            <div class="header">
                <div class="header-left">
                    <div style="display:table;">
                        @if($logoBase64)
                        <div class="logo-box">
                            <img src="{{ $logoBase64 }}" alt="Logo" class="logo-img">
                        </div>
                        @endif
                        <div class="brand-text-box">
                            <div class="brand-title">{{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}</div>
                            <div class="brand-sub">{{ $invoice->category->tagline ?: 'Invoice Detail Document' }}</div>
                        </div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="invoice-watermark">INVOICE</div>
                    <div class="invoice-no">{{ $invoice->invoice_number }}</div>
                    <div class="invoice-date">{{ $invoice->created_at->translatedFormat('d F Y') }}</div>
                </div>
            </div>

            <!-- Client & Status -->
            <div class="client-section">
                <div class="client-left">
                    <div class="section-label">Ditujukan Kepada</div>
                    <div class="client-name">{{ $invoice->client_name }}</div>
                    <div style="font-size: 10px; color: #5d5e60; margin-top: 2px;">Proyek: <strong>{{ $invoice->title }}</strong></div>
                </div>
                <div class="client-right">
                    @if($invoice->status === 'paid')
                    <div class="status-badge status-paid">LUNAS</div>
                    @elseif($invoice->status === 'dp_paid')
                    <div class="status-badge status-dp">DP TERBAYAR</div>
                    @else
                    <div class="status-badge status-unpaid">BELUM BAYAR</div>
                    @endif
                </div>
            </div>

            <!-- Details Grid -->
            <div class="details-grid">
                <div class="detail-col">
                    <div class="section-label">Kategori Jasa</div>
                    <div class="detail-val">{{ $invoice->category->name ?? '-' }}</div>
                </div>
                <div class="detail-col">
                    <div class="section-label">Deadline / Jatuh Tempo</div>
                    <div class="detail-val">{{ $invoice->deadline->format('d M Y, H:i') }} WIB</div>
                </div>
                <div class="detail-col">
                    <div class="section-label">Metode Biaya</div>
                    <div class="detail-val">{{ $invoice->payment_type === 'dp' ? 'Bertahap (Dengan DP)' : 'Bayar Lunas Langsung' }}</div>
                </div>
            </div>

            <!-- 1 Row 2 Columns: Description (Left) & Pricing (Right) -->
            <div class="desc-pricing-table">
                <div class="desc-col">
                    <div class="section-label">Deskripsi Pekerjaan</div>
                    <div class="desc-box">
                        <div class="desc-text">{{ $invoice->description }}</div>
                    </div>
                </div>
                <div class="pricing-col">
                    <div class="section-label">Rincian Pembayaran</div>
                    <div class="pricing-box">
                        <div class="pricing-row">
                            <div class="pricing-left">Total Biaya Proyek</div>
                            <div class="pricing-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                        </div>
                        @if($invoice->payment_type === 'dp')
                            @if($invoice->status === 'unpaid')
                            <div class="pricing-row">
                                <div class="pricing-left">Uang Muka (DP) Wajib</div>
                                <div class="pricing-right">Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="pricing-row">
                                <div class="pricing-left">Sisa Pelunasan Nanti</div>
                                <div class="pricing-right">Rp {{ number_format($invoice->total_amount - $invoice->dp_amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="pricing-row pricing-divider">
                                <div class="pricing-left total-label">Tagihan DP Saat Ini</div>
                                <div class="pricing-right total-val">Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</div>
                            </div>
                            @elseif($invoice->status === 'dp_paid')
                            <div class="pricing-row">
                                <div class="pricing-left">DP Dibayar</div>
                                <div class="pricing-right" style="color:#15803D;">- Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="pricing-row pricing-divider">
                                <div class="pricing-left total-label">Sisa Tagihan Pelunasan</div>
                                <div class="pricing-right total-val">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</div>
                            </div>
                            @else
                            <div class="pricing-row">
                                <div class="pricing-left">DP Dibayar</div>
                                <div class="pricing-right" style="color:#15803D;">Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="pricing-row">
                                <div class="pricing-left">Pelunasan Terbayar</div>
                                <div class="pricing-right" style="color:#15803D;">Rp {{ number_format($invoice->total_amount - $invoice->dp_amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="pricing-row pricing-divider">
                                <div class="pricing-left total-label" style="color:#15803D;">Sisa Tagihan</div>
                                <div class="pricing-right total-val" style="color:#15803D;">Rp 0 (LUNAS)</div>
                            </div>
                            @endif
                        @else
                        <div class="pricing-row pricing-divider">
                            <div class="pricing-left total-label">{{ $invoice->status === 'paid' ? 'Total Terbayar' : 'Total Tagihan' }}</div>
                            <div class="pricing-right total-val">
                                {{ $invoice->status === 'paid' ? 'Rp ' . number_format($invoice->total_amount, 0, ',', '.') . ' (LUNAS)' : 'Rp ' . number_format($invoice->total_amount, 0, ',', '.') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Page 1 Notice -->
            <div class="notice-box">
                <div class="notice-title">Petunjuk Pembayaran & QRIS</div>
                <div class="notice-sub">Silakan lihat halaman selanjutnya (Halaman 2) untuk scan QRIS dan informasi rekening transfer bank & e-wallet.</div>
            </div>
        </div>

        <!-- Footer Page 1 -->
        <div class="footer">
            Halaman 1/2 &bull; Official Invoice by <strong>{{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}</strong>
        </div>
    </div>


    <!-- ================= PAGE 2: INFORMASI PEMBAYARAN & QRIS (A4 PORTRAIT) ================= -->
    <div class="page-portrait">
        <div>
            <div class="accent-top"></div>
            <div class="accent-neon"></div>

            <!-- Header Page 2 -->
            <div class="header">
                <div class="header-left">
                    <div style="display:table;">
                        @if($logoBase64)
                        <div class="logo-box">
                            <img src="{{ $logoBase64 }}" alt="Logo" class="logo-img">
                        </div>
                        @endif
                        <div class="brand-text-box">
                            <div class="brand-title">{{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}</div>
                            <div class="brand-sub">Instruksi Pembayaran &bull; {{ $invoice->client_name }}</div>
                        </div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="invoice-watermark">PAYMENT</div>
                    <div class="invoice-no">{{ $invoice->invoice_number }}</div>
                    <div class="invoice-date">{{ $invoice->created_at->translatedFormat('d F Y') }}</div>
                </div>
            </div>

            <!-- Payment Card Section -->
            <div class="payment-card">
                <div class="section-label" style="margin-bottom:6px;">Instruksi Pembayaran</div>
                @if($invoice->status === 'paid')
                <div style="font-size:12px; font-weight:700; color:#15803D;">
                    &check; Invoice Ini Telah Dibayar Lunas
                </div>
                @else
                <div style="font-size:12px; font-weight:700; color:#1a1c1c;">
                    {{ $transferLabel }} <span class="neon-pill">Rp {{ number_format($transferAmount, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>

            @if($invoice->status !== 'paid')
                <!-- Large QRIS Section (Page 2 Hero) -->
                @if($qrisBase64)
                <div class="qris-wrapper">
                    <div style="font-size:11px; font-weight:800; color:#1a1c1c; text-transform:uppercase; margin-bottom:8px; letter-spacing:0.05em;">QRIS</div>
                    <img src="{{ $qrisBase64 }}" alt="QRIS" class="qris-img">
                    <div style="font-size:9px; color:#5d5e60; margin-top:8px;">Scan via BCA Mobile, Livin, BRImo, DANA, GoPay, OVO, ShopeePay, dll</div>
                </div>
                @endif

                <!-- Bank Accounts Grid Table -->
                <div class="bank-grid-table">
                    <div class="bank-col">
                        @if($bcaBase64)
                        <img src="{{ $bcaBase64 }}" alt="BCA" class="bank-logo-img">
                        @else
                        <span style="font-size:9px; font-weight:bold; color:#005EAA;">BCA</span>
                        @endif
                        <div class="bank-number">1921252558</div>
                    </div>
                    <div class="bank-col">
                        @if($danaBase64)
                        <img src="{{ $danaBase64 }}" alt="DANA" class="bank-logo-img">
                        @else
                        <span style="font-size:9px; font-weight:bold; color:#118EEA;">DANA</span>
                        @endif
                        <div class="bank-number">082333362651</div>
                    </div>
                    <div class="bank-col">
                        @if($seaBase64)
                        <img src="{{ $seaBase64 }}" alt="SeaBank" class="bank-logo-img">
                        @else
                        <span style="font-size:9px; font-weight:bold; color:#FF5722;">SeaBank</span>
                        @endif
                        <div class="bank-number">901099053997</div>
                    </div>
                </div>

                <div style="text-align:center; font-size:10px; color:#1a1c1c; margin-top:10px;">
                    Semua rekening & e-wallet a.n. <strong style="color:#000;">ALIEF BADRIT TAMAM</strong>
                </div>
                <div style="text-align:center; font-size:9px; color:#5d5e60; margin-top:4px;">
                    📌 Mohon konfirmasi bukti transfer setelah pembayaran. Terima kasih 🙏
                </div>
            @endif
        </div>

        <!-- Footer Page 2 -->
        <div class="footer">
            Halaman 2/2 &bull; Official Invoice by <strong>{{ $invoice->category->brand_name ?: 'ABT-FREELANCE' }}</strong>
        </div>
    </div>
</body>
</html>
