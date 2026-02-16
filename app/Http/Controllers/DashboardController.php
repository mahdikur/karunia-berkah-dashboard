<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Stats
        $totalSalesMonth = Invoice::whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->where('status', '!=', 'overdue')
            ->sum('total_amount');

        $unpaidInvoices = Invoice::whereIn('status', ['unpaid', 'partial', 'overdue']);
        if ($user->isStaff()) {
            $unpaidInvoices = $unpaidInvoices->where('created_by', $user->id);
        }
        $totalUnpaid = $unpaidInvoices->sum('remaining_amount');
        $unpaidCount = $unpaidInvoices->count();

        $overdueInvoiceCount = Invoice::where('status', 'overdue')->count();
        $pendingPoCount = PurchaseOrder::where('status', 'pending_approval')->count();

        // Recent POs
        $recentPOsQuery = PurchaseOrder::with('client')->latest();
        if ($user->isStaff()) {
            $recentPOsQuery->where('created_by', $user->id);
        }
        $recentPOs = $recentPOsQuery->limit(5)->get();

        // Recent Invoices
        $recentInvoicesQuery = Invoice::with('client')->latest();
        if ($user->isStaff()) {
            $recentInvoicesQuery->where('created_by', $user->id);
        }
        $recentInvoices = $recentInvoicesQuery->limit(5)->get();

        // Chart data - last 6 months
        $chartLabels = [];
        $chartData = [];
        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartLabels[] = $bulan[$date->month - 1] . ' ' . $date->year;
            $chartData[] = Invoice::whereMonth('invoice_date', $date->month)
                ->whereYear('invoice_date', $date->year)
                ->sum('total_amount');
        }

        // Top clients
        $topClients = Client::withCount('invoices')
            ->withSum(['invoices as total_sales' => fn($q) => $q->where('status', '!=', 'overdue')], 'total_amount')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalSalesMonth', 'totalUnpaid', 'unpaidCount',
            'overdueInvoiceCount', 'pendingPoCount',
            'recentPOs', 'recentInvoices',
            'chartLabels', 'chartData', 'topClients'
        ));
    }
}
