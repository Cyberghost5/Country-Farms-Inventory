<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\BatchDeletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OversightInventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isOversight(), 403);

        $search    = $request->input('search', '');
        $status    = $request->input('status', 'all');
        $productId = $request->input('product_id', '');

        $batches = ProductionBatch::with(['product', 'uploader', 'verifier'])
            ->when($search, fn($q) => $q->where('batch_number', 'like', "%{$search}%"))
            ->when($status !== 'all', function ($q) use ($status) {
                if ($status === 'verified') {
                    return $q->verified();
                } elseif ($status === 'pending') {
                    return $q->pending();
                }
            })
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $filterProducts = Product::orderBy('name')->get();

        $stockProducts = Product::with(['batches' => function ($q) {
                $q->verified();
            }])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Calculate card statistics
        $produced = ProductionBatch::verified()->sum('quantity');
        $dispatched = \App\Models\DispatchItem::whereHas('dispatch', function($q) {
            $q->whereIn('status', ['dispatched', 'received']);
        })->sum('quantity');
        $totalVerifiedStock = max(0, $produced - $dispatched);
        $totalPendingBatches = ProductionBatch::pending()->count();
        $totalVerifiedBatches = ProductionBatch::verified()->count();

        $stats = [
            'total_stock'      => $totalVerifiedStock,
            'pending_batches'  => $totalPendingBatches,
            'verified_batches' => $totalVerifiedBatches,
        ];

        return view('admin.inventory.index', compact(
            'user',
            'batches',
            'filterProducts',
            'stockProducts',
            'stats',
            'search',
            'status',
            'productId'
        ));
    }

    public function downloadReport($id)
    {
        $user = Auth::user();
        abort_unless($user->isOversight(), 403);

        $batch = ProductionBatch::with(['product', 'uploader', 'verifier'])->findOrFail($id);

        $filename = 'batch_report_' . $batch->batch_number . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($batch) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper Excel encoding
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['COUNTRY FARMS MANUFACTURING & DISTRIBUTION SYSTEM']);
            fputcsv($file, ['INVENTORY BATCH SPECS SHEET REPORT']);
            fputcsv($file, []);

            fputcsv($file, ['Batch Details:']);
            fputcsv($file, ['Batch Number', $batch->batch_number]);
            fputcsv($file, ['Product Name', $batch->product->name]);
            fputcsv($file, ['SKU/Code', $batch->product->sku]);
            fputcsv($file, ['Quantity Produced', $batch->quantity]);
            fputcsv($file, ['Production Date', $batch->production_date->format('Y-m-d')]);
            fputcsv($file, ['Expiry Date', $batch->expiry_date->format('Y-m-d')]);
            fputcsv($file, ['Status', $batch->is_verified ? 'Verified' : 'Pending']);
            fputcsv($file, ['Uploaded By', $batch->uploader->name]);
            fputcsv($file, ['Uploaded At', $batch->created_at->toDateTimeString()]);

            if ($batch->is_verified) {
                fputcsv($file, ['Verified By', $batch->verifier->name]);
                fputcsv($file, ['Verified At', $batch->verified_at->toDateTimeString()]);
            }

            fputcsv($file, ['Remarks', $batch->remarks ?: 'None']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user->isSuperAdmin(), 403); // Only Super Admin can delete/clear inventory

        $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        $batch = ProductionBatch::with('product')->findOrFail($id);

        try {
            DB::transaction(function () use ($batch, $user, $request) {
                // Serialise batch details
                $details = [
                    'batch_number'    => $batch->batch_number,
                    'product_name'    => $batch->product->name,
                    'sku'             => $batch->product->sku,
                    'quantity'        => $batch->quantity,
                    'production_date' => $batch->production_date->format('Y-m-d'),
                    'expiry_date'     => $batch->expiry_date->format('Y-m-d'),
                    'remarks'         => $batch->remarks,
                    'uploader_name'   => $batch->uploader->name,
                    'is_verified'     => $batch->is_verified,
                ];

                if ($batch->is_verified) {
                    $details['verifier_name'] = $batch->verifier->name;
                    $details['verified_at'] = $batch->verified_at->toDateTimeString();
                }

                // Log the action
                AuditLog::create([
                    'user_id'      => $user->id,
                    'user_name'    => $user->name,
                    'action'       => 'delete_batch',
                    'item_details' => $details,
                    'reason'       => $request->input('reason'),
                ]);

                // Notify Super Admins and General Managers
                $recipients = User::whereIn('role', [
                    User::ROLE_SUPER_ADMIN,
                    User::ROLE_GENERAL_MANAGER
                ])->where('is_active', true)->get();

                Notification::send($recipients, new BatchDeletedNotification($batch, $user->name, $request->input('reason')));

                // Delete the batch
                $batch->delete();
            });

            return back()->with('success', 'Batch #' . $batch->batch_number . ' has been deleted. Action recorded in Audit Log.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
