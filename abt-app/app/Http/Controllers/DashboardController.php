<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');

        $totalPiutang = Invoice::where('status', '!=', 'paid')->get()->sum(function ($inv) {
            if ($inv->status === 'dp_paid') {
                return (float)$inv->total_amount - (float)$inv->dp_amount;
            }
            return (float)$inv->total_amount;
        });

        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::where('status', 'paid')->count();

        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->selectRaw('MONTH(paid_at) as month, YEAR(paid_at) as year, SUM(total_amount) as total')
            ->groupByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->orderByRaw('YEAR(paid_at), MONTH(paid_at)')
            ->get();

        $categoryBreakdown = Category::withCount('invoices')
            ->withSum(['invoices as revenue' => function ($q) {
                $q->where('status', 'paid');
            }], 'total_amount')->get();

        $monthlyOrders = Invoice::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as total')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        return view('dashboard', compact(
            'totalRevenue', 'totalPiutang', 'totalInvoices', 'paidInvoices',
            'monthlyRevenue', 'categoryBreakdown', 'monthlyOrders'
        ));
    }
}
