<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuotationStatus;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use App\Support\Money;
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

        $acceptedQuotation = $booking->quotation()
            ->where('status', QuotationStatus::Accepted->value)
            ->first();

        $serviceName = $acceptedQuotation?->service_name
            ?? $booking->service_name;
        $servicePrice = Money::toMinor(
            $acceptedQuotation?->service_price
            ?? $booking->service_price
        );
        $extraFee = Money::toMinor(
            $acceptedQuotation?->extra_fee
            ?? ($data['extra_fee'] ?? 0)
        );
        $discountAmount = Money::toMinor(
            $acceptedQuotation?->discount_amount
            ?? ($data['discount_amount'] ?? 0)
        );
        $paidAmount = Money::toMinor($data['paid_amount'] ?? 0);

        $totalAmount = $acceptedQuotation
            ? Money::toMinor($acceptedQuotation->total_amount)
            : $servicePrice + $extraFee - $discountAmount;

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
            'service_name' => $serviceName,
            'service_price' => Money::fromMinor($servicePrice),
            'extra_fee' => Money::fromMinor($extraFee),
            'discount_amount' => Money::fromMinor($discountAmount),
            'total_amount' => Money::fromMinor($totalAmount),
            'paid_amount' => Money::fromMinor($paidAmount),
            'remaining_amount' => Money::fromMinor($remainingAmount),
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
                'amount' => Money::fromMinor($paidAmount),
                'payment_method' => $data['payment_method'] ?? null,
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

        $amount = Money::toMinor($data['amount']);

        $remainingAmount = Money::toMinor($invoice->remaining_amount);

        if ($amount > $remainingAmount) {
            throw ValidationException::withMessages([
                'amount' => [
                    'Payment amount cannot be greater than remaining amount.',
                ],
            ]);
        }

        $invoice->payments()->create([
            'received_by' => $admin->id,
            'amount' => Money::fromMinor($amount),
            'payment_method' => $data['payment_method'] ?? null,
            'note' => $data['note'] ?? null,
            'paid_at' => now(),
        ]);

        $paidAmount = Money::toMinor($invoice->paid_amount) + $amount;
        $totalAmount = Money::toMinor($invoice->total_amount);
        $remainingAmount = $totalAmount - $paidAmount;

        $paymentStatus = $this->calculatePaymentStatus(
            paidAmount: $paidAmount,
            totalAmount: $totalAmount
        );

        $invoice->update([
            'paid_amount' => Money::fromMinor($paidAmount),
            'remaining_amount' => Money::fromMinor($remainingAmount),
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === PaymentStatus::Paid
                ? now()
                : null,
        ]);

        return $invoice->fresh();
    }

    private function calculatePaymentStatus(
        int $paidAmount,
        int $totalAmount
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

        return 'INV-'.$date.'-'.str_pad(
            (string) $count,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
