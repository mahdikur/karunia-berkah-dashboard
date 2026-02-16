<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('report.index');
    }

    public function monthly(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        // Revenue
        $revenue = Invoice::whereMonth('invoice_date', $month)
            ->whereYear('invoice_date', $year)
            ->sum('total_amount');

        // COGS
        $poIds = Invoice::whereMonth('invoice_date', $month)
            ->whereYear('invoice_date', $year)
            ->pluck('purchase_order_id');

        $cogs = PurchaseOrderItem::whereIn('purchase_order_id', $poIds)
            ->whereNotNull('purchase_price')
            ->selectRaw('SUM(purchase_price * quantity) as total')
            ->value('total') ?? 0;

        $grossProfit = $revenue - $cogs;

        // Expenses
        $expenses = Expense::whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        $netProfit = $grossProfit - $expenses;
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue * 100) : 0;

        // Per client breakdown
        $clientBreakdown = Client::withSum(
            ['invoices as total_revenue' => fn($q) => $q->whereMonth('invoice_date', $month)->whereYear('invoice_date', $year)],
            'total_amount'
        )->having('total_revenue', '>', 0)->orderByDesc('total_revenue')->get();

        return view('report.monthly', compact(
            'month', 'year', 'revenue', 'cogs', 'grossProfit',
            'expenses', 'netProfit', 'profitMargin', 'clientBreakdown'
        ));
    }

    public function profitLoss(Request $request)
    {
        $year = $request->year ?? now()->year;
        $monthlyData = [];
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        for ($m = 1; $m <= 12; $m++) {
            $revenue = Invoice::whereMonth('invoice_date', $m)
                ->whereYear('invoice_date', $year)
                ->sum('total_amount');

            $poIds = Invoice::whereMonth('invoice_date', $m)
                ->whereYear('invoice_date', $year)
                ->pluck('purchase_order_id');

            $cogs = PurchaseOrderItem::whereIn('purchase_order_id', $poIds)
                ->whereNotNull('purchase_price')
                ->selectRaw('SUM(purchase_price * quantity) as total')
                ->value('total') ?? 0;

            $expenses = Expense::whereMonth('expense_date', $m)
                ->whereYear('expense_date', $year)
                ->sum('amount');

            $monthlyData[] = [
                'month' => $bulan[$m - 1],
                'revenue' => $revenue,
                'cogs' => $cogs,
                'gross_profit' => $revenue - $cogs,
                'expenses' => $expenses,
                'net_profit' => $revenue - $cogs - $expenses,
            ];
        }

        return view('report.profit-loss', compact('year', 'monthlyData'));
    }

    public function clientReport(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        $selectedClient = null;
        $reportData = null;

        if ($request->client_id) {
            $selectedClient = Client::findOrFail($request->client_id);
            $dateFrom = $request->date_from ?? now()->startOfYear()->format('Y-m-d');
            $dateTo = $request->date_to ?? now()->format('Y-m-d');

            $invoices = Invoice::where('client_id', $selectedClient->id)
                ->whereBetween('invoice_date', [$dateFrom, $dateTo])
                ->with('payments')
                ->latest()
                ->get();

            $reportData = [
                'total_po' => PurchaseOrder::where('client_id', $selectedClient->id)->whereBetween('po_date', [$dateFrom, $dateTo])->count(),
                'total_invoice' => $invoices->count(),
                'total_sales' => $invoices->sum('total_amount'),
                'total_paid' => $invoices->sum('paid_amount'),
                'total_outstanding' => $invoices->sum('remaining_amount'),
                'overdue_amount' => $invoices->where('status', 'overdue')->sum('remaining_amount'),
                'invoices' => $invoices,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ];
        }

        return view('report.client', compact('clients', 'selectedClient', 'reportData'));
    }
}
