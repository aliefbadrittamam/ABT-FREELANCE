<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Category;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Calculate exact real cash inflow for a specific date or date range.
     * Cash in = (Full Payments received) + (DP payments received) + (Remaining Pelunasan received).
     */
    private function calculateCashInflow(?Carbon $date = null, ?Carbon $start = null, ?Carbon $end = null): float
    {
        // 1. Full payment invoices paid within period
        $fullQuery = Invoice::where('status', 'paid')->where('payment_type', 'full');
        if ($date) {
            $fullQuery->whereDate('paid_at', $date);
        } elseif ($start && $end) {
            $fullQuery->whereBetween('paid_at', [$start, $end]);
        }
        $fullInflow = (float)$fullQuery->sum('total_amount');

        // 2. DP payments received within period (from dp_paid or paid invoices)
        $dpQuery = Invoice::whereIn('status', ['dp_paid', 'paid'])->where('payment_type', 'dp');
        if ($date) {
            $dpQuery->where(function($q) use ($date) {
                $q->whereDate('dp_paid_at', $date)
                  ->orWhere(function($sub) use ($date) {
                      $sub->whereNull('dp_paid_at')->whereDate('paid_at', $date);
                  });
            });
        } elseif ($start && $end) {
            $dpQuery->where(function($q) use ($start, $end) {
                $q->whereBetween('dp_paid_at', [$start, $end])
                  ->orWhere(function($sub) use ($start, $end) {
                      $sub->whereNull('dp_paid_at')->whereBetween('paid_at', [$start, $end]);
                  });
            });
        }
        $dpInflow = (float)$dpQuery->sum('dp_amount');

        // 3. Pelunasan (remaining amount) received within period (status == 'paid')
        // Only the remaining amount (total_amount - dp_amount), NOT the full amount!
        $pelunasanQuery = Invoice::where('status', 'paid')->where('payment_type', 'dp');
        if ($date) {
            $pelunasanQuery->whereDate('paid_at', $date);
        } elseif ($start && $end) {
            $pelunasanQuery->whereBetween('paid_at', [$start, $end]);
        }
        
        $pelunasanInflow = (float)$pelunasanQuery->get()->sum(function ($inv) {
            return max(0, (float)$inv->total_amount - (float)$inv->dp_amount);
        });

        return $fullInflow + $dpInflow + $pelunasanInflow;
    }

    public function index()
    {
        // 1. Total Pendapatan Keseluruhan (Cash in to date)
        $totalPaidFull = Invoice::where('status', 'paid')->where('payment_type', 'full')->sum('total_amount');
        $totalDpCollected = Invoice::whereIn('status', ['dp_paid', 'paid'])->where('payment_type', 'dp')->sum('dp_amount');
        $totalPelunasanPaid = Invoice::where('status', 'paid')->where('payment_type', 'dp')->get()->sum(function ($inv) {
            return max(0, (float)$inv->total_amount - (float)$inv->dp_amount);
        });
        $totalRevenue = (float)$totalPaidFull + (float)$totalDpCollected + (float)$totalPelunasanPaid;

        // 2. Pendapatan Hari Ini (Uang masuk riil hari ini)
        $todayRevenue = $this->calculateCashInflow(date: Carbon::today());

        // 3. Pendapatan Bulan Ini (tgl 1 sampai akhir bulan berjalan)
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $thisMonthRevenue = $this->calculateCashInflow(start: $startOfMonth, end: $endOfMonth);
        
        $thisMonthInvoicesCount = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->count();
        $thisMonthPeriod = $startOfMonth->format('d') . ' - ' . $endOfMonth->translatedFormat('d F Y');

        // 4. Total DP yang Sudah Terbayar (dari invoice dp_paid maupun paid)
        $totalDpTerbayar = Invoice::whereIn('status', ['dp_paid', 'paid'])
            ->where('payment_type', 'dp')
            ->sum('dp_amount');

        // 5. Sisa Pelunasan yang Belum Terbayar (Piutang riil)
        $sisaPelunasan = Invoice::where('status', 'dp_paid')->get()->sum(function ($inv) {
            return max(0, (float)$inv->total_amount - (float)$inv->dp_amount);
        });

        // 6. Total Belum Bayar (Status unpaid total)
        $totalUnpaid = Invoice::where('status', 'unpaid')->sum('total_amount');

        // Total Outstanding Piutang (Belum DP + Sisa Pelunasan)
        $totalPiutang = (float)$totalUnpaid + (float)$sisaPelunasan;

        // Counts
        $totalInvoices = Invoice::where('status', '!=', 'canceled')->count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $dpPaidInvoices = Invoice::where('status', 'dp_paid')->count();
        $unpaidInvoices = Invoice::where('status', 'unpaid')->count();
        $canceledInvoices = Invoice::where('status', 'canceled')->count();

        // 7. Dataset Grafik Harian (7 Hari Terakhir)
        $dailyLabels = [];
        $dailyValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->translatedFormat('d M');
            $dailyValues[] = $this->calculateCashInflow(date: $date);
        }

        // 8. Dataset Grafik Mingguan (6 Minggu Terakhir)
        $weeklyLabels = [];
        $weeklyValues = [];
        for ($w = 5; $w >= 0; $w--) {
            $startWeek = Carbon::now()->subWeeks($w)->startOfWeek();
            $endWeek = Carbon::now()->subWeeks($w)->endOfWeek();
            $weeklyLabels[] = $startWeek->format('d/m') . '-' . $endWeek->format('d/m');
            $weeklyValues[] = $this->calculateCashInflow(start: $startWeek, end: $endWeek);
        }

        // 9. Dataset Grafik Bulanan (Tahun Berjalan)
        $monthsName = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $monthlyLabels = [];
        $monthlyValues = [];
        $currentYear = Carbon::now()->year;
        for ($m = 1; $m <= 12; $m++) {
            $monthlyLabels[] = $monthsName[$m - 1];
            $startMonth = Carbon::createFromDate($currentYear, $m, 1)->startOfMonth();
            $endMonth = Carbon::createFromDate($currentYear, $m, 1)->endOfMonth();
            $monthlyValues[] = $this->calculateCashInflow(start: $startMonth, end: $endMonth);
        }

        // Breakdown per Kategori
        $categoryBreakdown = Category::withCount(['invoices' => function ($q) {
            $q->where('status', '!=', 'canceled');
        }])->withSum(['invoices as revenue' => function ($q) {
            $q->where('status', 'paid');
        }], 'total_amount')->get();

        // 5 Invoice Terbaru untuk Aktivitas Cepat
        $recentInvoices = Invoice::with('category')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalRevenue', 'todayRevenue', 'thisMonthRevenue', 'thisMonthPeriod', 'thisMonthInvoicesCount',
            'totalDpTerbayar', 'sisaPelunasan', 'totalPiutang',
            'totalInvoices', 'paidInvoices', 'dpPaidInvoices', 'unpaidInvoices', 'canceledInvoices',
            'dailyLabels', 'dailyValues', 'weeklyLabels', 'weeklyValues', 'monthlyLabels', 'monthlyValues',
            'categoryBreakdown', 'recentInvoices'
        ));
    }
}
