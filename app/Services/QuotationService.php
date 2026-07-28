<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\QuotationStatus;
use App\Models\Booking;
use App\Models\Quotation;
use App\Models\User;
use App\Support\Money;
use Illuminate\Validation\ValidationException;

class QuotationService
{
    public function createForBooking(
        Booking $booking,
        User $admin,
        array $data
    ): Quotation {
        if ($booking->status !== BookingStatus::Pending) {
            throw ValidationException::withMessages([
                'booking' => [
                    'Quotation can only be created for pending bookings.',
                ],
            ]);
        }

        if ($booking->quotation()->exists()) {
            throw ValidationException::withMessages([
                'booking' => [
                    'This booking already has a quotation.',
                ],
            ]);
        }

        $servicePrice = Money::toMinor($booking->service_price);
        $extraFee = Money::toMinor($data['extra_fee'] ?? 0);
        $discountAmount = Money::toMinor(
            $data['discount_amount'] ?? 0
        );
        $totalAmount = $servicePrice + $extraFee - $discountAmount;

        if ($totalAmount < 0) {
            throw ValidationException::withMessages([
                'discount_amount' => [
                    'Discount cannot be greater than quotation amount.',
                ],
            ]);
        }

        return Quotation::create([
            'booking_id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'created_by' => $admin->id,
            'quotation_no' => $this->generateQuotationNo(),
            'service_name' => $booking->service_name,
            'service_price' => Money::fromMinor($servicePrice),
            'extra_fee' => Money::fromMinor($extraFee),
            'discount_amount' => Money::fromMinor($discountAmount),
            'total_amount' => Money::fromMinor($totalAmount),
            'status' => QuotationStatus::Sent,
            'admin_note' => $data['admin_note'] ?? null,
            'valid_until' => $data['valid_until'] ?? null,
            'sent_at' => now(),
        ]);
    }

    public function accept(
        Quotation $quotation,
        User $customer,
        ?string $note
    ): Quotation {
        $this->ensureCustomerOwnsQuotation(
            quotation: $quotation,
            customer: $customer
        );

        $this->ensureQuotationCanBeResponded($quotation);

        $quotation->update([
            'status' => QuotationStatus::Accepted,
            'customer_response_note' => $note,
            'accepted_at' => now(),
        ]);

        return $quotation->fresh();
    }

    public function reject(
        Quotation $quotation,
        User $customer,
        ?string $note
    ): Quotation {
        $this->ensureCustomerOwnsQuotation(
            quotation: $quotation,
            customer: $customer
        );

        $this->ensureQuotationCanBeResponded($quotation);

        $quotation->update([
            'status' => QuotationStatus::Rejected,
            'customer_response_note' => $note,
            'rejected_at' => now(),
        ]);

        return $quotation->fresh();
    }

    private function ensureCustomerOwnsQuotation(
        Quotation $quotation,
        User $customer
    ): void {
        if ($quotation->customer_id !== $customer->id) {
            abort(404);
        }
    }

    private function ensureQuotationCanBeResponded(
        Quotation $quotation
    ): void {
        if ($quotation->status !== QuotationStatus::Sent) {
            throw ValidationException::withMessages([
                'quotation' => [
                    'Only sent quotations can be accepted or rejected.',
                ],
            ]);
        }

        if (
            $quotation->valid_until !== null &&
            $quotation->valid_until->isPast()
        ) {
            $quotation->update([
                'status' => QuotationStatus::Expired,
                'expired_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'quotation' => [
                    'This quotation has expired.',
                ],
            ]);
        }
    }

    private function generateQuotationNo(): string
    {
        $date = now()->format('Ymd');

        $count = Quotation::query()
            ->whereDate('created_at', today())
            ->count() + 1;

        return 'QUO-'.$date.'-'.str_pad(
            (string) $count,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
