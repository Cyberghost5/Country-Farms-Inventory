<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ensure only permitted roles can view
        abort_unless(
            $user->isSuperAdmin() || 
            $user->isGeneralManager() || 
            $user->isStoreManager() || 
            $user->isProductionManager() || 
            $user->isDistributor(),
            403
        );

        $status = $request->input('status', 'all');
        $search = $request->input('search', '');

        $query = Order::query()
            ->with(['distributor', 'items.product', 'processor'])
            ->orderBy('created_at', 'desc');

        // Filter by role
        if ($user->isDistributor()) {
            $query->where('distributor_id', $user->id);
        }

        // Apply filters
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('distributor', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(25)->withQueryString();

        return view('orders.index', compact('user', 'orders', 'status', 'search'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $user = Auth::user();
        abort_unless($user->isDistributor(), 403);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $product->available_stock = $product->verifiedStock();
                return $product;
            });

        // Preload pricing map for this distributor
        $pricingMap = [];
        foreach ($products as $product) {
            $pricingMap[$product->id] = $product->calculatedPriceForDistributor($user->id);
        }

        return view('orders.create', compact('user', 'products', 'pricingMap'));
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isDistributor(), 403);

        $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $order = DB::transaction(function () use ($request, $user) {
                $today = now()->format('Ymd');
                $count = Order::whereDate('created_at', now()->toDateString())->count() + 1;
                $orderNumber = 'ORD-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

                $totalAmount = 0.0;
                $itemsData = [];

                foreach ($request->input('items') as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    
                    if (!$product->is_active) {
                        throw new \Exception('Product "' . $product->name . '" is currently inactive.');
                    }

                    $unitPrice = (float) $product->calculatedPriceForDistributor($user->id);
                    $subtotal = $unitPrice * (int) $item['quantity'];
                    $totalAmount += $subtotal;

                    $itemsData[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];
                }

                $order = Order::create([
                    'distributor_id' => $user->id,
                    'order_number' => $orderNumber,
                    'status' => 'pending',
                    'total_amount' => $totalAmount,
                    'remarks' => $request->input('remarks'),
                ]);

                foreach ($itemsData as $data) {
                    $data['order_id'] = $order->id;
                    OrderItem::create($data);
                }

                return $order;
            });

            return redirect()->route('orders.index')
                ->with('success', 'Order #' . $order->order_number . ' placed successfully.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        abort_unless(
            $user->isSuperAdmin() || 
            $user->isGeneralManager() || 
            $user->isStoreManager() || 
            $user->isProductionManager() || 
            $user->isDistributor(),
            403
        );

        if ($user->isDistributor()) {
            abort_unless($order->distributor_id === $user->id, 404);
        }

        // Load relationships if they aren't loaded yet
        $order->loadMissing(['distributor', 'items.product', 'processor']);

        return view('orders.show', compact('user', 'order'));
    }

    /**
     * Approve the specified order.
     */
    public function approve(Order $order)
    {
        $user = Auth::user();
        // Only Super Admin and General Manager can approve orders
        abort_unless($user->isSuperAdmin() || $user->isGeneralManager(), 403);
        abort_unless($order->status === 'pending', 404);

        $order->update([
            'status' => 'approved',
            'processed_by' => $user->id,
            'processed_at' => now(),
        ]);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order #' . $order->order_number . ' has been approved.');
    }

    /**
     * Reject the specified order.
     */
    public function reject(Order $order)
    {
        $user = Auth::user();
        // Only Super Admin and General Manager can reject orders
        abort_unless($user->isSuperAdmin() || $user->isGeneralManager(), 403);
        abort_unless($order->status === 'pending', 404);

        $order->update([
            'status' => 'rejected',
            'processed_by' => $user->id,
            'processed_at' => now(),
        ]);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order #' . $order->order_number . ' has been rejected.');
    }
}
