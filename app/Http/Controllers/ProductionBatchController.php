<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\User;
use App\Notifications\BatchUploadedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class ProductionBatchController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isProductionManager(), 403);

        $search = $request->input('search', '');
        $status = $request->input('status', 'all');

        $batches = ProductionBatch::where('uploaded_by', $user->id)
            ->with('product')
            ->when($search, fn($q) => $q->where('batch_number', 'like', "%{$search}%"))
            ->when($status !== 'all', function ($q) use ($status) {
                if ($status === 'verified') {
                    return $q->verified();
                } elseif ($status === 'pending') {
                    return $q->pending();
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $products = Product::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total'    => ProductionBatch::where('uploaded_by', $user->id)->count(),
            'pending'  => ProductionBatch::where('uploaded_by', $user->id)->pending()->count(),
            'verified' => ProductionBatch::where('uploaded_by', $user->id)->verified()->count(),
        ];

        return view('production.batches.index', compact('user', 'batches', 'products', 'stats', 'search', 'status'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isProductionManager(), 403);

        $data = $request->validate([
            'product_id'      => ['required', 'exists:products,id'],
            'quantity'        => ['required', 'integer', 'min:1'],
            'batch_number'    => ['required', 'string', 'max:100', 'unique:production_batches,batch_number'],
            'production_date' => ['required', 'date'],
            'expiry_date'     => ['required', 'date', 'after:production_date'],
            'remarks'         => ['nullable', 'string'],
        ]);

        // Check if product is active
        $product = Product::findOrFail($data['product_id']);
        if (!$product->is_active) {
            return back()->with('error', 'Cannot upload a batch for an inactive product.')->withInput();
        }

        $data['uploaded_by'] = $user->id;
        $data['is_verified'] = false;

        $batch = ProductionBatch::create($data);

        // Notify Store Managers, Super Admins, and General Managers
        $recipients = User::whereIn('role', [
            User::ROLE_STORE_MANAGER,
            User::ROLE_SUPER_ADMIN,
            User::ROLE_GENERAL_MANAGER
        ])->where('is_active', true)->get();

        Notification::send($recipients, new BatchUploadedNotification($batch));

        return back()->with('success', 'Batch #' . $batch->batch_number . ' uploaded successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user->isProductionManager(), 403);

        $batch = ProductionBatch::where('uploaded_by', $user->id)->findOrFail($id);

        if ($batch->is_verified) {
            return back()->with('error', 'Cannot edit a verified batch.');
        }

        $data = $request->validate([
            'product_id'      => ['required', 'exists:products,id'],
            'quantity'        => ['required', 'integer', 'min:1'],
            'batch_number'    => ['required', 'string', 'max:100', "unique:production_batches,batch_number,{$batch->id}"],
            'production_date' => ['required', 'date'],
            'expiry_date'     => ['required', 'date', 'after:production_date'],
            'remarks'         => ['nullable', 'string'],
        ]);

        // Check if product is active
        $product = Product::findOrFail($data['product_id']);
        if (!$product->is_active) {
            return back()->with('error', 'Cannot edit a batch to use an inactive product.')->withInput();
        }

        $batch->update($data);

        return back()->with('success', 'Batch updated successfully.');
    }

    public function productsIndex(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isProductionManager(), 403);

        $search   = $request->input('search', '');
        $category = $request->input('category', '');

        $products = Product::where('is_active', true)
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"))
            ->when($category, fn($q) => $q->where('category', $category))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('production.products.index', compact('user', 'products', 'search', 'category'));
    }
}
