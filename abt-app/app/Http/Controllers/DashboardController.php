<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Category;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Pendapatan Keseluruhan (Paid invoices + DP amounts from dp_paid)
        $totalPaidFull = Invoice::where('status', 'paid')->sum('total_amount');
        $totalDpCollected = Invoice::where('status', 'dp_paid')->sum('dp_amount');
        $totalRevenue = (float)$totalPaidFull + (float)$totalDpCollected;

        // 2. Pendapatan Hari Ini
        $todayPaidFull = Invoice::where('status', 'paid')
            ->whereDate('paid_at', Carbon::today())
            ->sum('total_amount');
        $todayDpCollected = Invoice::where('status', 'dp_paid')
            ->whereDate('created_at', Carbon::today())
            ->sum('dp_amount');
        $todayRevenue = (float)$todayPaidFull + (float)$todayDpCollected;

        // 3. Pendapatan Bulan Ini (tgl 1 sampai akhir bulan berjalan)
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $monthPaidFull = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');
        $monthDpCollected = Invoice::where('status', 'dp_paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('dp_amount');
        $thisMonthRevenue = (float)$monthPaidFull + (float)$monthDpCollected;
        $thisMonthInvoicesCount = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->count();
        $thisMonthPeriod = $startOfMonth->format('d') . ' - ' . $endOfMonth->translatedFormat('d F Y');

        // 4. Total DP yang Sudah Terbayar (dari invoice dp_paid maupun paid)
        $totalDpTerbayar = Invoice::whereIn('status', ['dp_paid', 'paid'])
            ->where('payment_type', 'dp')
            ->sum('dp_amount');

        // 4. Sisa Pelunasan yang Belum Terbayar (Piutang riil)
        $sisaPelunasan = Invoice::where('status', 'dp_paid')->get()->sum(function ($inv) {
            return (float)$inv->total_amount - (float)$inv->dp_amount;
        });

        // 5. Total Belum Bayar (Status unpaid total)
        $totalUnpaid = Invoice::where('status', 'unpaid')->sum('total_amount');

        // Total Outstanding Piutang (Belum DP + Sisa Pelunasan)
        $totalPiutang = (float)$totalUnpaid + (float)$sisaPelunasan;

        // Counts
        $totalInvoices = Invoice::where('status', '!=', 'canceled')->count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $dpPaidInvoices = Invoice::where('status', 'dp_paid')->count();
        $unpaidInvoices = Invoice::where('status', 'unpaid')->count();
        $canceledInvoices = Invoice::where('status', 'canceled')->count();

        // 6. Dataset Grafik Harian (7 Hari Terakhir)
        $dailyLabels = [];
        $dailyValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->translatedFormat('d M');
            $paidSum = Invoice::where('status', 'paid')
                ->whereDate('paid_at', $date)
                ->sum('total_amount');
            $dpSum = Invoice::where('status', 'dp_paid')
                ->whereDate('created_at', $date)
                ->sum('dp_amount');
            $dailyValues[] = (float)$paidSum + (float)$dpSum;
        }

        // 7. Dataset Grafik Mingguan (6 Minggu Terakhir)
        $weeklyLabels = [];
        $weeklyValues = [];
        for ($w = 5; $w >= 0; $w--) {
            $startWeek = Carbon::now()->subWeeks($w)->startOfWeek();
            $endWeek = Carbon::now()->subWeeks($w)->endOfWeek();
            $weeklyLabels[] = $startWeek->format('d/m') . '-' . $endWeek->format('d/m');
            $paidSum = Invoice::where('status', 'paid')
                ->whereBetween('paid_at', [$startWeek, $endWeek])
                ->sum('total_amount');
            $dpSum = Invoice::where('status', 'dp_paid')
                ->whereBetween('created_at', [$startWeek, $endWeek])
                ->sum('dp_amount');
            $weeklyValues[] = (float)$paidSum + (float)$dpSum;
        }

        // 8. Dataset Grafik Bulanan (Tahun Berjalan)
        $monthsName = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $monthlyLabels = [];
        $monthlyValues = [];
        $currentYear = Carbon::now()->year;
        for ($m = 1; $m <= 12; $m++) {
            $monthlyLabels[] = $monthsName[$m - 1];
            $paidSum = Invoice::where('status', 'paid')
                ->whereYear('paid_at', $currentYear)
                ->whereMonth('paid_at', $m)
                ->sum('total_amount');
            $dpSum = Invoice::where('status', 'dp_paid')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $m)
                ->sum('dp_amount');
            $monthlyValues[] = (float)$paidSum + (float)$dpSum;
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
