@extends('layouts.app')

@section('title', 'Dashboard Penghasilan Turnamen — Tour Organizer')
@section('header', 'Tour Organizer')
@section('favicon', asset('assets/logo-abt-efootball-tur.jpg'))
@section('header_logo', asset('assets/logo-abt-efootball-tur.jpg'))

@section('content')
<!-- Header Greeting -->
<div class="mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <div class="flex items-center gap-2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mb-1">
            <span class="text-on-surface dark:text-white font-medium">Tour Organizer</span>
            <span class="material-symbols-outlined text-base">chevron_right</span>
            <span class="text-primary dark:text-primary-container font-bold">Dashboard Penghasilan</span>
        </div>
        <h1 class="text-2xl sm:text-[30px] font-black text-on-surface dark:text-white tracking-tight leading-tight">Dashboard Penghasilan Turnamen</h1>
        <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5">Ringkasan performa finansial dan laba bersih dari seluruh sesi turnamen eFootball.</p>
    </div>
    <div class="flex items-center gap-2.5">
        <a href="{{ url('/turnamen/efootball/live') }}" target="_blank" 
           class="px-3.5 py-2 bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#333] text-on-surface dark:text-gray-200 text-xs font-bold rounded-lg hover:bg-surface-variant dark:hover:bg-[#252525] transition-all flex items-center gap-1.5 shadow-xs">
            <span class="material-symbols-outlined text-base text-primary dark:text-primary-container">open_in_new</span>
            Live Slot Publik
        </a>
        <a href="{{ route('tour-organizer.efootball.create') }}" 
           class="px-4 py-2 bg-primary-container text-on-surface text-xs font-bold rounded-lg shadow-sm hover:brightness-95 transition-all flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base">add</span>
            Buka Sesi Baru
        </a>
    </div>
</div>

<!-- 4 Summary Cards (Turnamen Financial Metrics) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-6">
    <!-- Card 1: Profit Hari Ini -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border-2 border-primary-container relative overflow-hidden shadow-xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Profit Hari Ini</span>
                <span class="w-7 h-7 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary dark:text-primary-container">
                    <span class="material-symbols-outlined text-base">today</span>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-on-surface dark:text-white tracking-tight">
                Rp {{ number_format($todayProfit, 0, ',', '.') }}
            </h2>
        </div>
        <p class="text-[11px] text-secondary dark:text-gray-400 mt-2">Laba bersih sesi hari ini</p>
    </div>

    <!-- Card 2: Profit Bulan Ini -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Profit Bulan Ini</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-500/15 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined text-base">calendar_month</span>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                Rp {{ number_format($thisMonthProfit, 0, ',', '.') }}
            </h2>
        </div>
        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-2">
            {{ $thisMonthCompletedCount }} sesi selesai bulan ini
        </p>
    </div>

    <!-- Card 3: Total Profit Akumulasi -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Total Laba Bersih</span>
                <span class="w-7 h-7 rounded-lg bg-status-lunas/15 flex items-center justify-center text-status-lunas">
                    <span class="material-symbols-outlined text-base">account_balance_wallet</span>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-on-surface dark:text-white tracking-tight">
                Rp {{ number_format($totalProfitAccumulated, 0, ',', '.') }}
            </h2>
        </div>
        <p class="text-[11px] text-status-lunas font-semibold mt-2 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">verified</span>
            Dari {{ $totalCompleted }} turnamen selesai
        </p>
    </div>

    <!-- Card 4: Sesi Berjalan -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl p-5 border border-border-subtle dark:border-[#2a2a2a] relative overflow-hidden shadow-xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Sesi Aktif Saat Ini</span>
                <span class="w-7 h-7 rounded-lg bg-blue-500/15 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <span class="material-symbols-outlined text-base">sports_esports</span>
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 tracking-tight">
                {{ $activeSessionsCount }} Sesi Buka
            </h2>
        </div>
        <p class="text-[11px] text-secondary dark:text-gray-400 mt-2">Dapat berjalan paralel</p>
    </div>
</div>

<!-- Chart & Breakdown Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8" x-data="{ currentRange: 'daily' }">
    <!-- Chart Box -->
    <div class="lg:col-span-2 bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 flex flex-col shadow-xs min-h-[380px]">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
            <div>
                <h3 class="text-base font-bold text-on-surface dark:text-white">Tren Laba Bersih Turnamen</h3>
                <p class="text-xs text-secondary dark:text-gray-400">Pergerakan profit bersih admin dari turnamen</p>
            </div>
            
            <div class="flex bg-surface-container-low dark:bg-[#252525] p-1 rounded-lg border border-border-subtle dark:border-[#333]">
                <button @click="switchChart('daily')" 
                        :class="currentRange === 'daily' ? 'bg-primary-container text-on-surface font-bold shadow-xs' : 'text-secondary dark:text-gray-400 hover:text-on-surface'"
                        class="px-3 py-1 text-xs rounded-md transition-all">
                    Harian (7 Hari)
                </button>
                <button @click="switchChart('weekly')" 
                        :class="currentRange === 'weekly' ? 'bg-primary-container text-on-surface font-bold shadow-xs' : 'text-secondary dark:text-gray-400 hover:text-on-surface'"
                        class="px-3 py-1 text-xs rounded-md transition-all">
                    Mingguan (6 Minggu)
                </button>
            </div>
        </div>

        <div class="flex-1 w-full relative min-h-[260px]">
            <canvas id="tourRevenueChart"></canvas>
        </div>
    </div>

    <!-- Breakdown Skema Turnamen -->
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] flex flex-col shadow-xs">
        <div class="p-5 border-b border-border-subtle dark:border-[#2a2a2a]">
            <h3 class="text-base font-bold text-on-surface dark:text-white">Performa Skema Biaya</h3>
            <p class="text-xs text-secondary dark:text-gray-400">Pemasukan berdasarkan skema regis</p>
        </div>

        <div class="flex-1 p-4 space-y-2.5 overflow-y-auto">
            @forelse($presetBreakdown as $preset)
            <div class="flex items-center justify-between p-3 rounded-lg bg-surface dark:bg-[#181818] border border-border-subtle dark:border-[#2a2a2a]">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-primary-container/20 text-on-surface dark:text-primary-container flex items-center justify-center text-xs font-black">
                        {{ $preset->entry_fee >= 1000 ? ($preset->entry_fee / 1000) . 'K' : $preset->entry_fee }}
                    </span>
                    <div>
                        <strong class="text-xs text-on-surface dark:text-white block">Regis Rp {{ number_format($preset->entry_fee, 0, ',', '.') }}</strong>
                        <span class="text-[10px] text-secondary">{{ $preset->count }} sesi diadakan</span>
                    </div>
                </div>
                <strong class="text-xs font-mono text-status-lunas">+ Rp {{ number_format($preset->total_profit, 0, ',', '.') }}</strong>
            </div>
            @empty
            <div class="p-8 text-center text-secondary text-xs">
                Belum ada turnamen yang diselesaikan.
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Hall of Fame Juara 1 Terakhir -->
<div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 shadow-xs">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="text-base font-bold text-on-surface dark:text-white flex items-center gap-2">
                <span>🏆</span>
                Pemenang Turnamen Terakhir (Hall of Fame)
            </h3>
            <p class="text-xs text-secondary">Daftar tim juara 1 pada sesi yang telah selesai</p>
        </div>
        <a href="{{ route('tour-organizer.efootball') }}" class="text-xs font-bold text-primary dark:text-primary-container hover:underline">
            Kelola Semua Sesi &rarr;
        </a>
    </div>

    @if($recentWinners->isEmpty())
    <p class="text-xs text-secondary text-center py-6">Belum ada pemenang yang tercatat.</p>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        @foreach($recentWinners as $win)
        <div class="p-3.5 rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50/40 dark:bg-amber-950/20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">👑</span>
                <div>
                    <strong class="text-xs sm:text-sm font-bold text-on-surface dark:text-white block">{{ $win->winner->team_name ?? '-' }}</strong>
                    <span class="text-[10px] text-secondary">{{ $win->name }} ({{ $win->session_label }})</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-emerald-600 font-bold block">Hadiah Rp {{ number_format($win->prize_pool, 0, ',', '.') }}</span>
                <span class="text-[9.5px] text-secondary">{{ $win->completed_at ? $win->completed_at->format('d/m/Y') : '-' }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dailyLabels = @json($dailyLabels);
        const dailyValues = @json($dailyValues);
        const weeklyLabels = @json($weeklyLabels);
        const weeklyValues = @json($weeklyValues);

        const ctx = document.getElementById('tourRevenueChart').getContext('2d');
        let chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Profit Bersih (Rp)',
                    data: dailyValues,
                    borderColor: '#E8FF00',
                    backgroundColor: 'rgba(232, 255, 0, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#111111',
                    pointBorderColor: '#E8FF00',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Profit: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value/1000000) + 'jt';
                                if (value >= 1000) return 'Rp ' + (value/1000) + 'k';
                                return 'Rp ' + value;
                            }
                        }
                    }
                }
            }
        });

        window.switchChart = function(range) {
            if (range === 'daily') {
                chart.data.labels = dailyLabels;
                chart.data.datasets[0].data = dailyValues;
            } else if (range === 'weekly') {
                chart.data.labels = weeklyLabels;
                chart.data.datasets[0].data = weeklyValues;
            }
            chart.update();
        }
    });
</script>
@endsection
