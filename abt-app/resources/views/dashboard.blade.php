@extends('layouts.app')

@section('title', 'Dashboard — ABT-FREELANCE')
@section('header', 'Dashboard')

@section('content')
<header class="mb-6 sm:mb-8">
    <h1 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">Dashboard Penghasilan</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-1">Ringkasan performa finansial Anda.</p>
</header>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-6">
    <!-- Total Pendapatan -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 sm:p-6 border border-border-subtle dark:border-[#2a2a2a] border-l-4 border-l-primary-container relative overflow-hidden group shadow-sm transition-colors duration-200">
        <div class="absolute -right-6 sm:-right-10 -top-6 sm:-top-10 opacity-5 dark:opacity-10 group-hover:opacity-10 transition-opacity duration-300">
            <span class="material-symbols-outlined text-[80px] sm:text-[120px] dark:text-white">account_balance_wallet</span>
        </div>
        <div class="relative z-10">
            <p class="text-[11px] sm:text-xs font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1.5">Total Pendapatan</p>
            <h2 class="text-2xl sm:text-[36px] lg:text-[40px] font-bold text-on-surface dark:text-white leading-tight sm:leading-[48px] tracking-tight truncate">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </h2>
            <div class="flex items-center gap-1.5 mt-3 text-status-lunas text-xs font-semibold">
                <span class="material-symbols-outlined text-sm">trending_up</span>
                <span>Dari {{ $paidInvoices }} invoice lunas</span>
            </div>
        </div>
    </div>

    <!-- Piutang -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 sm:p-6 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden group hover:border-on-surface-variant transition-colors duration-300 shadow-sm">
        <div class="absolute -right-6 sm:-right-10 -top-6 sm:-top-10 opacity-5 dark:opacity-10 group-hover:opacity-10 transition-opacity duration-300">
            <span class="material-symbols-outlined text-[80px] sm:text-[120px] dark:text-white">pending_actions</span>
        </div>
        <div class="relative z-10">
            <p class="text-[11px] sm:text-xs font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1.5">Piutang / Belum Lunas</p>
            <h2 class="text-2xl sm:text-[36px] lg:text-[40px] font-bold text-on-surface dark:text-white leading-tight sm:leading-[48px] tracking-tight truncate">
                Rp {{ number_format($totalPiutang, 0, ',', '.') }}
            </h2>
            <div class="flex items-center gap-1.5 mt-3 text-status-pending text-xs font-semibold">
                <span class="material-symbols-outlined text-sm">warning</span>
                <span>Menunggu {{ $totalInvoices - $paidInvoices }} invoice</span>
            </div>
        </div>
    </div>
</div>

<!-- Chart + Category Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Chart Section -->
    <div class="lg:col-span-2 bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 flex flex-col shadow-sm transition-colors duration-200 h-[320px] sm:h-[400px]">
        <div class="flex justify-between items-center mb-4 sm:mb-6">
            <h3 class="text-base sm:text-lg font-semibold text-on-surface dark:text-white">Pendapatan per Bulan</h3>
            <div class="px-2.5 py-1 bg-surface-variant dark:bg-[#2a2a2a] rounded-md text-xs font-semibold text-on-surface-variant dark:text-gray-300">
                {{ date('Y') }}
            </div>
        </div>
        <div class="flex-1 relative w-full overflow-hidden">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="lg:col-span-1 bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] flex flex-col shadow-sm transition-colors duration-200 max-h-[400px]">
        <div class="p-5 sm:p-6 border-b border-border-subtle dark:border-[#2a2a2a]">
            <h3 class="text-base sm:text-lg font-semibold text-on-surface dark:text-white">Pendapatan per Kategori</h3>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            @forelse($categoryBreakdown as $cat)
            <div class="flex items-center justify-between p-3 hover:bg-surface-variant/50 dark:hover:bg-[#252525] rounded-lg transition-colors group">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-md bg-secondary-container dark:bg-[#2a2a2a] flex items-center justify-center text-on-surface dark:text-white group-hover:bg-primary-container group-hover:text-on-surface transition-colors shrink-0">
                        <span class="material-symbols-outlined text-sm">work</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-on-surface dark:text-white">{{ $cat->name }}</p>
                        <p class="text-[11px] text-on-surface-variant dark:text-gray-400">{{ $cat->invoices_count ?? 0 }} Proyek</p>
                    </div>
                </div>
                <span class="text-xs font-semibold text-on-surface dark:text-white">Rp {{ number_format($cat->revenue ?? 0, 0, ',', '.') }}</span>
            </div>
            @empty
            <div class="p-6 text-center text-on-surface-variant dark:text-gray-400 text-sm">
                Belum ada data kategori
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    const revenueData = @json($monthlyRevenue);
    const labels = revenueData.length ? revenueData.map(d => months[d.month - 1]) : ['Jan','Feb','Mar','Apr','Mei','Jun'];
    const values = revenueData.length ? revenueData.map(d => d.total) : [0,0,0,0,0,0];

    const isDark = document.documentElement.classList.contains('dark');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(232, 255, 0, 0.5)');
    gradient.addColorStop(1, 'rgba(232, 255, 0, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan',
                data: values,
                borderColor: '#bed100',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: isDark ? '#ffffff' : '#1a1c1c',
                pointBorderColor: '#e8ff00',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
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
                    grid: { color: isDark ? '#2a2a2a' : '#e2e2e2', borderDash: [5,5] },
                    ticks: {
                        color: '#888',
                        font: { family: 'Inter', size: 11 },
                        callback: v => v === 0 ? '0' : 'Rp ' + (v/1000000) + 'M'
                    },
                    beginAtZero: true
                }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });
});
</script>
@endsection
