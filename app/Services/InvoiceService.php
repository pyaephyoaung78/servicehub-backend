<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function createFromBooking(
        Booking $booking,
        User $admin,
        array $data
    ): Invoice {
        if ($booking->status !== BookingStatus::Completed) {
            throw ValidationException::withMessages([
                'booking' => [
                    'Invoice can only be created for completed bookings.',
                ],
            ]);
        }

        if ($booking->invoice()->exists()) {
            throw ValidationException::withMessages([
                'booking' => [
                    'This booking already has an invoice.',
                ],
            ]);
        }

        $servicePrice = (float) $booking->service_price;
        $extraFee = (float) ($data['extra_fee'] ?? 0);
        $discountAmount =
            (float) ($data['discount_amount'] ?? 0);
        $paidAmount = (float) ($data['paid_amount'] ?? 0);

        $totalAmount =
            $servicePrice + $extraFee - $discountAmount;

        if ($totalAmount < 0) {
            throw ValidationException::withMessages([
                'discount_amount' => [
                    'Discount cannot be greater than the invoice amount.',
                ],
            ]);
        }

        if ($paidAmount > $totalAmount) {
            throw ValidationException::withMessages([
                'paid_amount' => [
                    'Paid amount cannot be greater than total amount.',
                ],
            ]);
        }

        $remainingAmount = $totalAmount - $paidAmount;

        $paymentStatus = $this->calculatePaymentStatus(
            paidAmount: $paidAmount,
            totalAmount: $totalAmount
        );

        $invoice = Invoice::create([
            'booking_id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'issued_by' => $admin->id,
            'invoice_no' => $this->generateInvoiceNo(),
            'service_name' => $booking->service_name,
            'service_price' => $servicePrice,
            'extra_fee' => $extraFee,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_status' => $paymentStatus,
            'issued_at' => now(),
            'paid_at' => $paymentStatus === PaymentStatus::Paid
                ? now()
                : null,
            'note' => $data['note'] ?? null,
        ]);

        if ($paidAmount > 0) {
            $invoice->payments()->create([
                'received_by' => $admin->id,
                'amount' => $paidAmount,
                'payment_method' =>
                    $data['payment_method'] ?? null,
                'note' => 'Initial payment',
                'paid_at' => now(),
            ]);
        }

        return $invoice->fresh();
    }

    public function recordPayment(
        Invoice $invoice,
        User $admin,
        array $data
    ): Invoice {
        if ($invoice->payment_status === PaymentStatus::Paid) {
            throw ValidationException::withMessages([
                'invoice' => [
                    'This invoice is already fully paid.',
                ],
            ]);
        }

        $amount = (float) $data['amount'];

        if ($amount > (float) $invoice->remaining_amount) {
            throw ValidationException::withMessages([
                'amount' => [
                    'Payment amount cannot be greater than remaining amount.',
                ],
            ]);
        }

        $invoice->payments()->create([
            'received_by' => $admin->id,
            'amount' => $amount,
            'payment_method' => $data['payment_method'] ?? null,
            'note' => $data['note'] ?? null,
            'paid_at' => now(),
        ]);

        $paidAmount =
            (float) $invoice->paid_amount + $amount;

        $remainingAmount =
            (float) $invoice->total_amount - $paidAmount;

        $paymentStatus = $this->calculatePaymentStatus(
            paidAmount: $paidAmount,
            totalAmount: (float) $invoice->total_amount
        );

        $invoice->update([
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === PaymentStatus::Paid
                ? now()
                : null,
        ]);

        return $invoice->fresh();
    }

    private function calculatePaymentStatus(
        float $paidAmount,
        float $totalAmount
    ): PaymentStatus {
        if ($paidAmount <= 0) {
            return PaymentStatus::Unpaid;
        }

        if ($paidAmount < $totalAmount) {
            return PaymentStatus::Partial;
        }

        return PaymentStatus::Paid;
    }

    private function generateInvoiceNo(): string
    {
        $date = now()->format('Ymd');

        $count = Invoice::query()
            ->whereDate('created_at', today())
            ->count() + 1;

        return 'INV-' . $date . '-' . str_pad(
            (string) $count,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}