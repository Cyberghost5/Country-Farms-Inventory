<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Models\UserOperatingArea;
use App\Notifications\DispatchCompletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isStoreManager(), 403);

        $search = $request->input('search', '');

        $dispatches = Dispatch::where('dispatched_by', $user->id)
            ->with(['distributor', 'items.product'])
            ->when($search, fn($q) => $q->where('dispatch_number', 'like', "%{$search}%"))
            ->orderBy('dispatched_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('store.dispatches.index', compact('user', 'dispatches', 'search'));
    }

    public function create()
    {
        $user = Auth::user();
        abort_unless($user->isStoreManager(), 403);

        $distributors = User::where('role', User::ROLE_DISTRIBUTOR)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $product->available_stock = $product->verifiedStock();
                return $product;
            });

        $distributors = User::where('role', 'distributor')
            ->where('is_active', true)
            ->with('operatingAreas')
            ->orderBy('name')
            ->get();

        // Get unique operating states from active distributors
        $operatingStates = UserOperatingArea::whereHas('user', function ($q) {
            $q->where('role', 'distributor')->where('is_active', true);
        })
        ->distinct()
        ->pluck('state')
        ->toArray();

        // Preload pricing map per state for the frontend JS to display unit prices instantly
        $pricingMap = [];
        foreach ($operatingStates as $state) {
            $pricingMap[$state] = [];
            foreach ($products as $product) {
                $pricingMap[$state][$product->id] = $product->calculatedPriceForState($state);
            }
        }

        return view('store.dispatches.create', compact('user', 'distributors', 'products', 'pricingMap', 'operatingStates'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isStoreManager(), 403);

        $request->validate([
            'distributor_id' => ['required', 'exists:users,id'],
            'state'          => ['required', 'string'],
            'remarks'        => ['nullable', 'string'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $distributorId = $request->input('distributor_id');
        $distributor = User::findOrFail($distributorId);
        $state = $request->input('state');

        if (!$distributor->is_active) {
            return back()->with('error', 'Cannot dispatch to an inactive distributor.')->withInput();
        }

        // Validate distributor operates in the selected state
        $operatesInState = $distributor->operatingAreas()->where('state', $state)->exists();
        if (!$operatesInState) {
            return back()->with('error', 'The selected distributor does not operate in ' . $state)->withInput();
        }

        try {
            $dispatch = DB::transaction(function () use ($request, $user, $distributorId, $state) {
                // Generate unique dispatch number
                $today = now()->format('Ymd');
                $dispatchCount = Dispatch::whereDate('created_at', now()->toDateString())->count() + 1;
                $dispatchNumber = 'DSP-' . $today . '-' . str_pad($dispatchCount, 4, '0', STR_PAD_LEFT);

                $totalAmount = 0.0;
                $itemsData = [];

                foreach ($request->input('items') as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    if (!$product->is_active) {
                        throw new \Exception('Product "' . $product->name . '" is inactive and cannot be dispatched.');
                    }

                    $availableStock = $product->verifiedStock();
                    if ($item['quantity'] > $availableStock) {
                        throw new \Exception('Insufficient stock for product "' . $product->name . '". Only ' . $availableStock . ' available.');
                    }

                    $unitPrice = $product->calculatedPriceForDistributor($distributorId, $state);
                    $subtotal = $unitPrice * $item['quantity'];
                    $totalAmount += $subtotal;

                    $itemsData[] = [
                        'product_id' => $product->id,
                        'quantity'   => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'subtotal'   => $subtotal,
                    ];
                }

                // Create dispatch
                $dispatch = Dispatch::create([
                    'distributor_id'  => $distributorId,
                    'state'           => $state,
                    'dispatch_number' => $dispatchNumber,
                    'status'          => 'dispatched',
                    'dispatched_by'   => $user->id,
                    'dispatched_at'   => now(),
                    'remarks'         => $request->input('remarks'),
                    'total_amount'    => $totalAmount,
                ]);

                // Create dispatch items
                foreach ($itemsData as $data) {
                    $data['dispatch_id'] = $dispatch->id;
                    DispatchItem::create($data);
                }

                // Auto-generate invoice
                $invoiceCount = Invoice::count() + 1;
                $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . str_pad($invoiceCount, 5, '0', STR_PAD_LEFT);

                Invoice::create([
                    'dispatch_id'    => $dispatch->id,
                    'distributor_id' => $distributorId,
                    'invoice_number' => $invoiceNumber,
                    'total_amount'   => $totalAmount,
                    'due_amount'     => $totalAmount,
                    'status'         => 'unpaid',
                    'due_date'       => now()->addDays(30),
                ]);

                return $dispatch;
            });

            // Trigger Notifications
            $recipients = User::whereIn('role', [
                User::ROLE_SUPER_ADMIN,
                User::ROLE_GENERAL_MANAGER
            ])->where('is_active', true)->get();

            $recipients->push($distributor);

            Notification::send($recipients, new DispatchCompletedNotification($dispatch));

            return redirect()->route('store.dispatches.index')
                ->with('success', 'Dispatch #' . $dispatch->dispatch_number . ' completed successfully. Invoice automatically generated.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
