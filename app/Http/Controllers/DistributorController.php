<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributorController extends Controller
{
    public function receivedIndex(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isDistributor(), 403);

        $search = $request->input('search', '');

        $dispatches = Dispatch::where('distributor_id', $user->id)
            ->with(['dispatcher', 'items.product'])
            ->when($search, fn($q) => $q->where('dispatch_number', 'like', "%{$search}%"))
            ->orderBy('dispatched_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('distributor.received.index', compact('user', 'dispatches', 'search'));
    }

    public function markAsReceived(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user->isDistributor(), 403);

        $dispatch = Dispatch::where('distributor_id', $user->id)->findOrFail($id);

        if ($dispatch->status === 'received') {
            return back()->with('error', 'Dispatch is already marked as received.');
        }

        $dispatch->update(['status' => 'received']);

        return back()->with('success', 'Dispatch #' . $dispatch->dispatch_number . ' marked as received.');
    }

    public function invoicesIndex(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isDistributor(), 403);

        $status = $request->input('status', 'all');

        $invoices = Invoice::where('distributor_id', $user->id)
            ->with('dispatch')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $outstandingBalance = Invoice::where('distributor_id', $user->id)
            ->whereIn('status', ['unpaid', 'partially_paid', 'pending_approval'])
            ->sum('due_amount');

        return view('distributor.invoices.index', compact('user', 'invoices', 'outstandingBalance', 'status'));
    }

    public function paymentsIndex(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isDistributor(), 403);

        $payments = Payment::where('distributor_id', $user->id)
            ->with('invoice')
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('distributor.payments.index', compact('user', 'payments'));
    }

    public function uploadPayment(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isDistributor(), 403);

        $request->validate([
            'invoice_id'       => ['required', 'exists:invoices,id'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_date'     => ['required', 'date'],
            'payment_method'   => ['required', 'string', 'max:50'],
            'proof_of_payment' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $invoice = Invoice::where('distributor_id', $user->id)
            ->findOrFail($request->input('invoice_id'));

        if ($invoice->status === 'pending_approval') {
            return back()->with('error', 'A payment proof has already been submitted for this invoice and is awaiting approval.');
        }

        if ($invoice->status === 'paid') {
            return back()->with('error', 'This invoice has already been fully paid.');
        }

        $amount = (float) $request->input('amount');

        if ($amount > (float) $invoice->due_amount) {
            return back()->with('error', 'Payment amount of ₦' . number_format($amount, 2) . ' cannot exceed outstanding invoice balance of ₦' . number_format($invoice->due_amount, 2));
        }

        // Store the uploaded proof of payment file on the public disk
        $path = $request->file('proof_of_payment')->store('proofs', 'public');

        try {
            DB::transaction(function () use ($invoice, $user, $amount, $request, $path) {
                $paymentCount = Payment::count() + 1;
                $paymentNumber = 'PAY-' . now()->format('Ymd') . '-' . str_pad($paymentCount, 5, '0', STR_PAD_LEFT);

                Payment::create([
                    'invoice_id'     => $invoice->id,
                    'distributor_id' => $user->id,
                    'payment_number' => $paymentNumber,
                    'amount'         => $amount,
                    'payment_date'   => $request->input('payment_date'),
                    'payment_method' => $request->input('payment_method'),
                    'reference'      => $path,
                    'recorded_by'    => $user->id,
                    'status'         => 'pending',
                ]);

                $invoice->update([
                    'status' => 'pending_approval',
                ]);
            });

            return back()->with('success', 'Payment proof uploaded successfully. Awaiting administrator approval.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
