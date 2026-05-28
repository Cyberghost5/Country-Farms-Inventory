<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $stats = match ($user->role) {
            'super_admin', 'general_manager' => $this->oversightStats(),
            'production_manager'             => $this->productionStats($user),
            'store_manager'                  => $this->storeStats($user),
            'distributor'                    => $this->distributorStats($user),
            default                          => [],
        };

        return view('dashboard', compact('user', 'stats'));
    }

    // ── Stats builders ──

    private function oversightStats(): array
    {
        $produced = ProductionBatch::verified()->sum('quantity');
        $dispatched = DispatchItem::whereHas('dispatch', function($q) {
            $q->whereIn('status', ['dispatched', 'received']);
        })->sum('quantity');
        $totalStock = max(0, $produced - $dispatched);

        return [
            'total_users'        => User::count(),
            'distributors'       => User::where('role', 'distributor')->count(),
            'production_managers'=> User::where('role', 'production_manager')->count(),
            'store_managers'     => User::where('role', 'store_manager')->count(),
            'general_managers'   => User::where('role', 'general_manager')->count(),
            'total_products'     => Product::count(),
            'active_products'    => Product::where('is_active', true)->count(),
            'total_revenue'      => Payment::where('status', 'approved')->sum('amount'),
            'total_outstanding'  => Invoice::whereIn('status', ['unpaid', 'partially_paid', 'pending_approval'])->sum('due_amount'),
            'total_stock'        => $totalStock,
        ];
    }

    private function productionStats(User $user): array
    {
        return [
            'total_uploads'        => ProductionBatch::where('uploaded_by', $user->id)->count(),
            'pending_verification' => ProductionBatch::where('uploaded_by', $user->id)->pending()->count(),
            'verified'             => ProductionBatch::where('uploaded_by', $user->id)->verified()->count(),
        ];
    }

    private function storeStats(User $user): array
    {
        $produced = ProductionBatch::verified()->sum('quantity');
        $dispatched = DispatchItem::whereHas('dispatch', function($q) {
            $q->whereIn('status', ['dispatched', 'received']);
        })->sum('quantity');

        return [
            'total_stock'          => max(0, $produced - $dispatched),
            'pending_verification' => ProductionBatch::pending()->count(),
            'dispatches'           => Dispatch::where('dispatched_by', $user->id)->count(),
        ];
    }

    private function distributorStats(User $user): array
    {
        $receivedQty = DispatchItem::whereHas('dispatch', function ($q) use ($user) {
            $q->where('distributor_id', $user->id)->where('status', 'received');
        })->sum('quantity');

        $unpaidInvoices = Invoice::where('distributor_id', $user->id)
            ->whereIn('status', ['unpaid', 'partially_paid', 'pending_approval'])
            ->get();

        return [
            'received_products'   => $receivedQty,
            'invoices_count'      => $unpaidInvoices->count(),
            'outstanding_balance' => $unpaidInvoices->sum('due_amount'),
        ];
    }
}
