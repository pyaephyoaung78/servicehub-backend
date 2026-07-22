<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\PaymentProofStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewPaymentProofRequest;
use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Services\InvoiceService;
use App\Services\PaymentProofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPaymentProofController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => [
                'nullable',
                Rule::enum(PaymentProofStatus::class),
            ],
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $paymentProofs = PaymentProof::query()
            ->with([
                'invoice',
                'customer',
                'reviewedBy',
            ])
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($proofQuery) use ($search) {
                        $proofQuery
                            ->where('payment_method', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhereHas(
                                'invoice',
                                fn ($invoiceQuery) => $invoiceQuery
                                    ->where('invoice_no', 'like', "%{$search}%")
                            )
                            ->orWhereHas(
                                'customer',
                                fn ($customerQuery) => $customerQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payment-proofs.index', [
            'paymentProofs' => $paymentProofs,
            'statuses' => PaymentProofStatus::cases(),
        ]);
    }

    public function show(PaymentProof $paymentProof): View
    {
        $paymentProof->load([
            'invoice.customer',
            'invoicePayment',
            'customer',
            'reviewedBy',
        ]);

        return view('admin.payment-proofs.show', [
            'paymentProof' => $paymentProof,
        ]);
    }

    public function file(PaymentProof $paymentProof)
    {
        $disk = Storage::disk('local');

        abort_unless($disk->exists($paymentProof->proof_path), 404);

        return response()->file(
            $disk->path($paymentProof->proof_path),
            [
                'Content-Type' => $paymentProof->proof_mime_type
                    ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.
                    addslashes($paymentProof->proof_original_name ?? 'payment-proof').
                    '"',
            ]
        );
    }

    public function approve(
        ReviewPaymentProofRequest $request,
        PaymentProof $paymentProof,
        PaymentProofService $paymentProofService,
        InvoiceService $invoiceService
    ): RedirectResponse {
        DB::transaction(function () use (
            $request,
            $paymentProof,
            $paymentProofService,
            $invoiceService
        ) {
            $lockedProof = PaymentProof::query()
                ->lockForUpdate()
                ->findOrFail($paymentProof->id);
            $lockedProof->load('invoice');

            $lockedInvoice = Invoice::query()
                ->lockForUpdate()
                ->findOrFail($lockedProof->invoice_id);
            $lockedProof->setRelation('invoice', $lockedInvoice);

            $paymentProofService->approve(
                paymentProof: $lockedProof,
                admin: $request->user(),
                invoiceService: $invoiceService,
                reviewNote: $request->validated('review_note')
            );
        });

        return redirect()
            ->route('admin.payment-proofs.show', $paymentProof)
            ->with('success', 'Payment proof approved and payment recorded.');
    }

    public function reject(
        ReviewPaymentProofRequest $request,
        PaymentProof $paymentProof,
        PaymentProofService $paymentProofService
    ): RedirectResponse {
        DB::transaction(function () use (
            $request,
            $paymentProof,
            $paymentProofService
        ) {
            $lockedProof = PaymentProof::query()
                ->lockForUpdate()
                ->findOrFail($paymentProof->id);

            $paymentProofService->reject(
                paymentProof: $lockedProof,
                admin: $request->user(),
                reviewNote: $request->validated('review_note')
            );
        });

        return redirect()
            ->route('admin.payment-proofs.show', $paymentProof)
            ->with('success', 'Payment proof rejected.');
    }
}
