<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DispatchItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductionBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OversightReportsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isOversight(), 403);

        // 1. KPI Stats
        $produced = (int) ProductionBatch::verified()->sum('quantity');
        $dispatched = (int) DispatchItem::sum('quantity');
        $totalStock = max(0, $produced - $dispatched);

        $activeProductsCount = Product::where('is_active', true)->count();
        $totalRevenue        = Payment::sum('amount');
        $totalOutstanding    = Invoice::whereIn('status', ['unpaid', 'partially_paid', 'pending_approval'])->sum('due_amount');

        $stats = [
            'total_stock'       => $totalStock,
            'active_products'   => $activeProductsCount,
            'total_revenue'     => $totalRevenue,
            'total_outstanding' => $totalOutstanding,
        ];

        // 2. Chart data: Stock Levels by Product
        $productStockData = Product::where('is_active', true)
            ->get()
            ->map(function ($product) {
                return [
                    'name'  => $product->name,
                    'stock' => $product->verifiedStock(),
                ];
            });

        // 3. Chart data: Sales by Category
        $categorySalesData = DB::table('dispatch_items')
            ->join('products', 'dispatch_items.product_id', '=', 'products.id')
            ->select('products.category', DB::raw('SUM(dispatch_items.subtotal) as total'))
            ->groupBy('products.category')
            ->get();

        // 4. Audit Logs (deletions log)
        $auditLogs = AuditLog::orderBy('created_at', 'desc')->paginate(15);

        return view('admin.reports.index', compact('user', 'stats', 'productStockData', 'categorySalesData', 'auditLogs'));
    }
}
