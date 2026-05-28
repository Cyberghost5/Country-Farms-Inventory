<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OversightDispatchController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isOversight(), 403);

        $search        = $request->input('search', '');
        $distributorId = $request->input('distributor_id', '');

        $dispatches = Dispatch::with(['distributor', 'dispatcher', 'items.product'])
            ->when($search, fn($q) => $q->where('dispatch_number', 'like', "%{$search}%"))
            ->when($distributorId, fn($q) => $q->where('distributor_id', $distributorId))
            ->orderBy('dispatched_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $distributors = User::where('role', User::ROLE_DISTRIBUTOR)->orderBy('name')->get();

        return view('admin.dispatches.index', compact('user', 'dispatches', 'distributors', 'search', 'distributorId'));
    }

    public function paymentsIndex(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isOversight(), 403);

        $status = $request->input('status', 'all');
        $distributorId = $request->input('distributor_id', '');

        $payments = Payment::with(['invoice', 'distributor', 'recorder'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($distributorId, fn($q) => $q->where('distributor_id', $distributorId))
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $distributors = User::where('role', User::ROLE_DISTRIBUTOR)->orderBy('name')->get();

        return view('admin.payments.index', compact('user', 'payments', 'distributors', 'status', 'distributorId'));
    }

    public function distributorsIndex(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isOversight(), 403);

        $distributors = User::where('role', User::ROLE_DISTRIBUTOR)
            ->with(['invoices', 'payments'])
            ->orderBy('name')
            ->paginate(25);

        // Fetch unpaid/partially paid invoices for the "Record Payment" form modal
        $unpaidInvoices = Invoice::whereIn('status', ['unpaid', 'partially_paid'])
            ->with('distributor')
            ->orderBy('invoice_number')
            ->get();

        // Fetch pending payments uploaded by distributors
        $pendingPayments = Payment::where('status', 'pending')
            ->with(['distributor', 'invoice'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculate overall financials (only approved payments are included in revenue)
        $totalOutstanding = Invoice::whereIn('status', ['unpaid', 'partially_paid', 'pending_approval'])->sum('due_amount');
        $totalRevenue     = Payment::where('status', 'approved')->sum('amount');

        $stats = [
            'total_outstanding' => $totalOutstanding,
            'total_revenue'     => $totalRevenue,
        ];

        return view('admin.distributors.index', compact('user', 'distributors', 'unpaidInvoices', 'pendingPayments', 'stats'));
    }

    public function recordPayment(Request $request, $invoiceId)
    {
        $user = Auth::user();
        abort_unless($user->isSuperAdmin(), 403); // General Manager is read-only, cannot record payments

        $request->validate([
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_date'     => ['required', 'date'],
            'payment_method'   => ['required', 'string', 'max:50'],
            'proof_of_payment' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $invoice = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->findOrFail($invoiceId);
        $amount = (float) $request->input('amount');

        if ($amount > (float) $invoice->due_amount) {
            return back()->with('error', 'Payment amount of ₦' . number_format($amount, 2) . ' cannot exceed outstanding invoice balance of ₦' . number_format($invoice->due_amount, 2));
        }

        // Store the proof of payment file on the public disk
        $path = $request->file('proof_of_payment')->store('proofs', 'public');

        try {
            DB::transaction(function () use ($invoice, $amount, $request, $user, $path) {
                $paymentCount = Payment::count() + 1;
                $paymentNumber = 'PAY-' . now()->format('Ymd') . '-' . str_pad($paymentCount, 5, '0', STR_PAD_LEFT);

                Payment::create([
                    'invoice_id'     => $invoice->id,
                    'distributor_id' => $invoice->distributor_id,
                    'payment_number' => $paymentNumber,
                    'amount'         => $amount,
                    'payment_date'   => $request->input('payment_date'),
                    'payment_method' => $request->input('payment_method'),
                    'reference'      => $path,
                    'recorded_by'    => $user->id,
                    'status'         => 'approved',
                ]);

                $newDue = (float) $invoice->due_amount - $amount;
                $status = 'partially_paid';
                if ($newDue <= 0.01) {
                    $newDue = 0;
                    $status = 'paid';
                }

                $invoice->update([
                    'due_amount' => $newDue,
                    'status'     => $status,
                ]);
            });

            return back()->with('success', 'Payment of ₦' . number_format($amount, 2) . ' has been recorded successfully against Invoice #' . $invoice->invoice_number);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approvePayment(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user->isSuperAdmin(), 403);

        $payment = Payment::where('status', 'pending')->findOrFail($id);
        $invoice = $payment->invoice;

        if ($payment->amount > (float) $invoice->due_amount) {
            return back()->with('error', 'Payment amount of ₦' . number_format($payment->amount, 2) . ' exceeds invoice balance of ₦' . number_format($invoice->due_amount, 2));
        }

        try {
            DB::transaction(function () use ($payment, $invoice) {
                $payment->update([
                    'status' => 'approved',
                ]);

                $newDue = (float) $invoice->due_amount - (float) $payment->amount;
                $status = 'partially_paid';
                if ($newDue <= 0.01) {
                    $newDue = 0;
                    $status = 'paid';
                }

                $invoice->update([
                    'due_amount' => $newDue,
                    'status'     => $status,
                ]);
            });

            return back()->with('success', 'Payment Approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectPayment(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user->isSuperAdmin(), 403);

        $payment = Payment::where('status', 'pending')->findOrFail($id);
        $invoice = $payment->invoice;

        try {
            DB::transaction(function () use ($payment, $invoice) {
                $payment->update(['status' => 'rejected']);

                $status = 'unpaid';
                if ((float)$invoice->due_amount < (float)$invoice->total_amount && (float)$invoice->due_amount > 0.01) {
                    $status = 'partially_paid';
                }

                $invoice->update(['status' => $status]);
            });

            return back()->with('success', 'Payment Rejected successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
