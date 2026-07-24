<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\QuotationStatus;
use App\Models\Booking;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceQuotationPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepted_quotation_pricing_is_copied_to_the_invoice(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $category = ServiceCategory::create([
            'name' => 'Cleaning',
            'slug' => 'cleaning',
        ]);
        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => 'Home cleaning',
            'slug' => 'home-cleaning',
            'base_price' => 50000,
        ]);
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_name' => 'Home cleaning',
            'service_price' => '50000.10',
            'scheduled_at' => now()->subDay(),
            'phone' => '09123456789',
            'address' => 'Yangon',
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
        ]);
        $quotation = Quotation::create([
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'quotation_no' => 'QUO-TEST-0001',
            'service_name' => 'Premium home cleaning',
            'service_price' => '90000.10',
            'extra_fee' => '15000.20',
            'discount_amount' => '5000.05',
            'total_amount' => '100000.25',
            'status' => QuotationStatus::Accepted,
            'sent_at' => now()->subDays(2),
            'accepted_at' => now()->subDay(),
        ]);

        $invoice = app(InvoiceService::class)->createFromBooking(
            booking: $booking,
            admin: $admin,
            data: [
                'extra_fee' => '999999.99',
                'discount_amount' => 0,
                'paid_amount' => '25000.10',
            ]
        );

        $this->assertSame('Premium home cleaning', $invoice->service_name);
        $this->assertSame('90000.10', (string) $invoice->service_price);
        $this->assertSame('15000.20', (string) $invoice->extra_fee);
        $this->assertSame('5000.05', (string) $invoice->discount_amount);
        $this->assertSame('100000.25', (string) $invoice->total_amount);
        $this->assertSame('75000.15', (string) $invoice->remaining_amount);
        $this->assertSame($quotation->id, $booking->quotation->id);
    }
}
