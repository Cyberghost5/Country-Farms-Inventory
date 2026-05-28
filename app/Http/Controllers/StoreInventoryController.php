<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\User;
use App\Notifications\BatchVerifiedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class StoreInventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isStoreManager(), 403);

        $pendingBatches = ProductionBatch::pending()
            ->with(['product', 'uploader'])
            ->orderBy('created_at', 'asc')
            ->get();

        $products = Product::with(['batches' => function ($q) {
                $q->verified();
            }])
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(25);

        // Aggregate stats
        $totalStock = ProductionBatch::verified()->sum('quantity');
        $pendingCount = $pendingBatches->count();

        $stats = [
            'total_stock'          => $totalStock,
            'pending_verification' => $pendingCount,
            'dispatches'           => \App\Models\Dispatch::where('dispatched_by', $user->id)->count(),
        ];

        return view('store.inventory.index', compact('user', 'pendingBatches', 'products', 'stats'));
    }

    public function verify(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user->isStoreManager(), 403);

        $batch = ProductionBatch::pending()->findOrFail($id);

        $batch->update([
            'is_verified' => true,
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        // Notify Super Admins and General Managers
        $recipients = User::whereIn('role', [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_GENERAL_MANAGER
        ])->where('is_active', true)->get();

        Notification::send($recipients, new BatchVerifiedNotification($batch));

        return back()->with('success', 'Batch #' . $batch->batch_number . ' has been verified and added to stock.');
    }
}
