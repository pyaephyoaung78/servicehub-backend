<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentProofStatus;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PaymentProofService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_a_payment_proof_records_the_invoice_payment(): void
    {
        Storage::fake('local');

        [$customer, $admin, $invoice] = $this->createInvoice();
        $proof = app(PaymentProofService::class)->submit(
            invoice: $invoice,
            customer: $customer,
            proof: UploadedFile::fake()->image('kpay-receipt.jpg'),
            data: [
                'amount' => '25000.10',
                'payment_method' => 'KPay',
                'note' => 'Customer transfer reference 1234',
            ]
        );

        Storage::disk('local')->assertExists($proof->proof_path);

        $approvedProof = app(PaymentProofService::class)->approve(
            paymentProof: $proof->load('invoice'),
            admin: $admin,
            invoiceService: app(InvoiceService::class),
            reviewNote: 'Transfer verified.',
        );

        $invoice->refresh();

        $this->assertSame(PaymentProofStatus::Approved, $approvedProof->status);
        $this->assertSame('25000.10', (string) $invoice->paid_amount);
        $this->assertSame('75000.15', (string) $invoice->remaining_amount);
        $this->assertNotNull($approvedProof->invoice_payment_id);
        $this->assertDatabaseHas('invoice_payments', [
            'id' => $approvedProof->invoice_payment_id,
            'invoice_id' => $invoice->id,
            'amount' => '25000.10',
        ]);
    }

    public function test_rejecting_a_payment_proof_does_not_change_the_invoice_balance(): void
    {
        Storage::fake('local');

        [$customer, $admin, $invoice] = $this->createInvoice();
        $proof = app(PaymentProofService::class)->submit(
            invoice: $invoice,
            customer: $customer,
            proof: UploadedFile::fake()->image('invalid-receipt.jpg'),
            data: [
                'amount' => 25000,
                'payment_method' => 'WavePay',
            ]
        );

        $rejectedProof = app(PaymentProofService::class)->reject(
            paymentProof: $proof,
            admin: $admin,
            reviewNote: 'The receipt does not match the transfer reference.',
        );

        $invoice->refresh();

        $this->assertSame(PaymentProofStatus::Rejected, $rejectedProof->status);
        $this->assertSame('0.00', (string) $invoice->paid_amount);
        $this->assertSame('100000.25', (string) $invoice->remaining_amount);
        $this->assertDatabaseCount('invoice_payments', 0);
    }

    /**
     * @return array{0: User, 1: User, 2: Invoice}
     */
    private function createInvoice(): array
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
            'base_price' => '100000.25',
        ]);
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_name' => 'Home cleaning',
            'service_price' => '100000.25',
            'scheduled_at' => now()->subDay(),
            'phone' => '09123456789',
            'address' => 'Yangon',
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
        ]);
        $invoice = app(InvoiceService::class)->createFromBooking(
            booking: $booking,
            admin: $admin,
            data: []
        );

        return [$customer, $admin, $invoice];
    }
}
