@extends('layouts.app')

@section('title', 'Dashboard — ABT-FREELANCE')
@section('header', 'Dashboard')

@section('content')
<!-- Header Greeting -->
<div class="mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl sm:text-[30px] font-black text-on-surface dark:text-white tracking-tight leading-tight">Dashboard Penghasilan</h1>
        <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5">Ringkasan performa finansial dan pesanan aktif Anda.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('invoices.create') }}" class="px-4 py-2 bg-primary-container text-on-surface text-xs font-bold rounded-lg shadow-sm hover:brightness-95 transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base">add</span>
            Buat Invoice Baru
        </a>
    </div>
</div>

<!-- Summary Cards (5 Strategic Metrics) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-5 mb-6">
    <!-- Card 1: Pendapatan Hari Ini -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border-2 border-primary-container relative overflow-hidden shadow-sm transition-colors duration-200 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Hari Ini</span>
                <span class="w-7 h-7 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary dark:text-primary-container">
                    <span class="material-symbols-outlined text-base">today</span>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-on-surface dark:text-white tracking-tight">
                Rp {{ number_format($todayRevenue, 0, ',', '.') }}
            </h2>
        </div>
        <p class="text-[11px] text-secondary dark:text-gray-400 mt-2">Uang masuk hari ini</p>
    </div>

    <!-- Card 2: Pendapatan Bulan Ini (tgl 1 s/d akhir bulan) -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-sm transition-colors duration-200 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Bulan Ini</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-500/15 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined text-base">calendar_month</span>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}
            </h2>
        </div>
        <div class="mt-2 text-[11px] text-secondary dark:text-gray-400 flex flex-col">
            <span class="font-medium text-on-surface dark:text-gray-300">{{ $thisMonthPeriod }}</span>
            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $thisMonthInvoicesCount }} invoice lunas</span>
        </div>
    </div>

    <!-- Card 3: Total Pendapatan -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-sm transition-colors duration-200 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Total Pendapatan</span>
                <span class="w-7 h-7 rounded-lg bg-status-lunas/15 flex items-center justify-center text-status-lunas">
                    <span class="material-symbols-outlined text-base">account_balance_wallet</span>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-on-surface dark:text-white tracking-tight">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </h2>
        </div>
        <p class="text-[11px] text-status-lunas font-semibold mt-2 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            {{ $paidInvoices }} invoice lunas
        </p>
    </div>

    <!-- Card 4: DP Terbayar -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-sm transition-colors duration-200 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">DP Terbayar</span>
                <span class="w-7 h-7 rounded-lg bg-status-dp/15 flex items-center justify-center text-status-dp">
                    <span class="material-symbols-outlined text-base">payments</span>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-on-surface dark:text-white tracking-tight">
                Rp {{ number_format($totalDpTerbayar, 0, ',', '.') }}
            </h2>
        </div>
        <p class="text-[11px] text-status-dp font-semibold mt-2">
            {{ $dpPaidInvoices }} order pengerjaan
        </p>
    </div>

    <!-- Card 5: Sisa Pelunasan -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-sm transition-colors duration-200 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Sisa Pelunasan</span>
                <span class="w-7 h-7 rounded-lg bg-status-pending/15 flex items-center justify-center text-status-pending">
                    <span class="material-symbols-outlined text-base">pending_actions</span>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-status-pending tracking-tight">
                Rp {{ number_format($sisaPelunasan, 0, ',', '.') }}
            </h2>
        </div>
        <p class="text-[11px] text-secondary dark:text-gray-400 mt-2">
            Piutang: <strong class="text-on-surface dark:text-white">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</strong>
        </p>
    </div>
</div>

<!-- Dynamic Filterable Chart + Category Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8" x-data="{ currentRange: 'daily' }">
    <!-- Chart Section -->
    <div class="lg:col-span-2 bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 flex flex-col shadow-sm transition-colors duration-200 min-h-[380px]">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-on-surface dark:text-white">Tren Pendapatan</h3>
                <p class="text-xs text-secondary dark:text-gray-400">Pantau pergerakan omset berkala</p>
            </div>

            <!-- Time Range Selectors -->
            <div class="flex bg-surface-container dark:bg-[#252525] p-1 rounded-lg border border-border-subtle dark:border-[#333] text-xs font-semibold">
                <button type="button" @click="currentRange = 'daily'; window.changeChartRange('daily')" 
                        :class="currentRange === 'daily' ? 'bg-primary-container text-on-surface shadow-sm font-bold' : 'text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-md transition-all">
                    Harian (7 Hari)
                </button>
                <button type="button" @click="currentRange = 'weekly'; window.changeChartRange('weekly')" 
                        :class="currentRange === 'weekly' ? 'bg-primary-container text-on-surface shadow-sm font-bold' : 'text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-md transition-all">
                    Mingguan
                </button>
                <button type="button" @click="currentRange = 'monthly'; window.changeChartRange('monthly')" 
                        :class="currentRange === 'monthly' ? 'bg-primary-container text-on-surface shadow-sm font-bold' : 'text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-md transition-all">
                    Bulanan
                </button>
            </div>
        </div>
        <div class="flex-1 relative w-full h-[260px]">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="lg:col-span-1 bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] flex flex-col shadow-sm transition-colors duration-200">
        <div class="p-5 sm:p-6 border-b border-border-subtle dark:border-[#2a2a2a]">
            <h3 class="text-base sm:text-lg font-bold text-on-surface dark:text-white">Pendapatan per Kategori</h3>
            <p class="text-xs text-secondary dark:text-gray-400">Lini bisnis & layanan aktif</p>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            @forelse($categoryBreakdown as $cat)
            <div class="flex items-center justify-between p-3 hover:bg-surface-variant/50 dark:hover:bg-[#252525] rounded-lg transition-colors group">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-md bg-secondary-container dark:bg-[#2a2a2a] flex items-center justify-center text-on-surface dark:text-white group-hover:bg-primary-container group-hover:text-on-surface transition-colors shrink-0">
                        <span class="material-symbols-outlined text-sm">work</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-on-surface dark:text-white">{{ $cat->name }}</p>
                        <p class="text-[11px] text-secondary dark:text-gray-400">{{ $cat->invoices_count ?? 0 }} Proyek</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-on-surface dark:text-white">Rp {{ number_format($cat->revenue ?? 0, 0, ',', '.') }}</span>
            </div>
            @empty
            <div class="p-6 text-center text-on-surface-variant dark:text-gray-400 text-sm">
                Belum ada data kategori
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Invoices Table (Quick Access) -->
<div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] overflow-hidden shadow-sm transition-colors duration-200">
    <div class="p-5 sm:p-6 border-b border-border-subtle dark:border-[#2a2a2a] flex justify-between items-center">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-on-surface dark:text-white">Invoice Terbaru</h3>
            <p class="text-xs text-secondary dark:text-gray-400">5 pesanan dan tagihan terkini</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-primary dark:text-primary-container hover:underline flex items-center gap-1">
            Lihat Semua Invoice
            <span class="material-symbols-outlined text-base">arrow_forward</span>
        </a>
    </div>

    <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse min-w-[600px]">
            <thead>
                <tr class="border-b border-border-subtle dark:border-[#2a2a2a] bg-surface-container-low dark:bg-[#181818] text-secondary dark:text-gray-400 text-[11px] uppercase tracking-wider font-semibold">
                    <th class="py-3 px-6">Nomor Invoice</th>
                    <th class="py-3 px-6">Klien & Proyek</th>
                    <th class="py-3 px-6">Kategori</th>
                    <th class="py-3 px-6 text-right">Total</th>
                    <th class="py-3 px-6 text-center">Status</th>
                    <th class="py-3 px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle dark:divide-[#2a2a2a] text-xs sm:text-sm">
                @forelse($recentInvoices as $inv)
                <tr class="hover:bg-surface-variant/30 dark:hover:bg-[#252525] transition-colors">
                    <td class="py-3.5 px-6 font-mono font-bold text-on-surface dark:text-gray-300">
                        {{ $inv->invoice_number }}
                    </td>
                    <td class="py-3.5 px-6">
                        <p class="font-bold text-on-surface dark:text-white">{{ $inv->client_name }}</p>
                        <p class="text-xs text-secondary dark:text-gray-400">{{ Str::limit($inv->title, 40) }}</p>
                    </td>
                    <td class="py-3.5 px-6 text-secondary dark:text-gray-400">
                        {{ $inv->category->name ?? '-' }}
                    </td>
                    <td class="py-3.5 px-6 text-right font-bold text-on-surface dark:text-white">
                        Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="py-3.5 px-6 text-center">
                        @if($inv->status === 'paid')
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-status-lunas/10 text-status-lunas px-2.5 py-0.5 rounded-full">Lunas</span>
                        @elseif($inv->status === 'dp_paid')
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-status-dp/10 text-status-dp px-2.5 py-0.5 rounded-full">DP Terbayar</span>
                        @elseif($inv->status === 'canceled')
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-gray-200 dark:bg-[#333] text-gray-700 dark:text-gray-300 px-2.5 py-0.5 rounded-full">Dibatalkan</span>
                        @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-status-pending/10 text-status-pending px-2.5 py-0.5 rounded-full">Belum Bayar</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6 text-right">
                        <a href="{{ route('invoices.show', $inv) }}" class="inline-flex items-center gap-1 text-xs font-bold text-primary dark:text-primary-container hover:underline">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-secondary dark:text-gray-400 text-xs">
                        Belum ada invoice. Buat invoice pertama Anda!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dailyData = { labels: @json($dailyLabels), values: @json($dailyValues) };
    const weeklyData = { labels: @json($weeklyLabels), values: @json($weeklyValues) };
    const monthlyData = { labels: @json($monthlyLabels), values: @json($monthlyValues) };

    const ctx = document.getElementById('revenueChart').getContext('2d');
    const isDark = document.documentElement.classList.contains('dark');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(232, 255, 0, 0.45)');
    gradient.addColorStop(1, 'rgba(232, 255, 0, 0.0)');

    const chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dailyData.values,
                borderColor: '#bed100',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: isDark ? '#ffffff' : '#1a1c1c',
                pointBorderColor: '#e8ff00',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1c1c',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#888', font: { family: 'Inter', size: 11, weight: 600 } }
                },
                y: {
                    grid: { color: isDark ? '#2a2a2a' : '#e2e2e2', borderDash: [4,4] },
                    ticks: {
                        color: '#888',
                        font: { family: 'Inter', size: 11 },
                        callback: function(v) {
                            if (v === 0) return '0';
                            if (v >= 1000000) return 'Rp ' + (v/1000000) + 'M';
                            return 'Rp ' + (v/1000) + 'K';
                        }
                    },
                    beginAtZero: true
                }
            }
        }
    });

    window.changeChartRange = function(range) {
        let data = dailyData;
        if (range === 'weekly') data = weeklyData;
        if (range === 'monthly') data = monthlyData;

        chartInstance.data.labels = data.labels;
        chartInstance.data.datasets[0].data = data.values;
        chartInstance.update();
    };
});
</script>
@endsection
