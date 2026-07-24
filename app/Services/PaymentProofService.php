<?php

namespace App\Services;

use App\Enums\PaymentProofStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PaymentProof;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentProofService
{
    public function submit(
        Invoice $invoice,
        User $customer,
        UploadedFile $proof,
        array $data
    ): PaymentProof {
        if ($invoice->customer_id !== $customer->id) {
            abort(404);
        }

        if ($invoice->payment_status === PaymentStatus::Paid) {
            throw ValidationException::withMessages([
                'invoice' => [
                    'This invoice is already fully paid.',
                ],
            ]);
        }

        if ($invoice->paymentProofs()
            ->where('status', PaymentProofStatus::Pending->value)
            ->exists()) {
            throw ValidationException::withMessages([
                'proof' => [
                    'This invoice already has a payment proof waiting for review.',
                ],
            ]);
        }

        $amount = (float) $data['amount'];

        if ($amount > (float) $invoice->remaining_amount) {
            throw ValidationException::withMessages([
                'amount' => [
                    'Payment amount cannot be greater than the remaining amount.',
                ],
            ]);
        }

        $path = $proof->store(
            'payment-proofs/'.$invoice->id,
            'local'
        );

        try {
            return $invoice->paymentProofs()->create([
                'customer_id' => $customer->id,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'proof_path' => $path,
                'proof_original_name' => $proof->getClientOriginalName(),
                'proof_mime_type' => $proof->getMimeType(),
                'proof_size' => $proof->getSize(),
                'note' => $data['note'] ?? null,
                'status' => PaymentProofStatus::Pending,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }

    public function approve(
        PaymentProof $paymentProof,
        User $admin,
        InvoiceService $invoiceService,
        ?string $reviewNote
    ): PaymentProof {
        if ($paymentProof->status !== PaymentProofStatus::Pending) {
            throw ValidationException::withMessages([
                'payment_proof' => [
                    'Only pending payment proofs can be approved.',
                ],
            ]);
        }

        $invoice = $paymentProof->invoice;
        $paymentNote = 'Payment proof #'.$paymentProof->id.' approved.';

        if ($paymentProof->note !== null) {
            $paymentNote .= "\n".$paymentProof->note;
        }

        $updatedInvoice = $invoiceService->recordPayment(
            invoice: $invoice,
            admin: $admin,
            data: [
                'amount' => $paymentProof->amount,
                'payment_method' => $paymentProof->payment_method,
                'note' => $paymentNote,
            ]
        );

        $invoicePayment = InvoicePayment::query()
            ->where('invoice_id', $updatedInvoice->id)
            ->latest('id')
            ->firstOrFail();

        $paymentProof->update([
            'invoice_payment_id' => $invoicePayment->id,
            'status' => PaymentProofStatus::Approved,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'review_note' => $reviewNote,
        ]);

        return $paymentProof->fresh([
            'invoice.customer',
            'invoicePayment',
            'reviewedBy',
        ]);
    }

    public function reject(
        PaymentProof $paymentProof,
        User $admin,
        ?string $reviewNote
    ): PaymentProof {
        if ($paymentProof->status !== PaymentProofStatus::Pending) {
            throw ValidationException::withMessages([
                'payment_proof' => [
                    'Only pending payment proofs can be rejected.',
                ],
            ]);
        }

        $paymentProof->update([
            'status' => PaymentProofStatus::Rejected,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'review_note' => $reviewNote,
        ]);

        return $paymentProof->fresh([
            'invoice.customer',
            'reviewedBy',
        ]);
    }
}
