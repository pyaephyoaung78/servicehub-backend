<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\BookingStatusTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingCheckInWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_must_use_the_customer_check_in_code_to_start_work(): void
    {
        [$customer, $booking] = $this->createAcceptedBooking();
        $transitionService = app(BookingStatusTransitionService::class);

        $onTheWayBooking = $transitionService->transition(
            $booking,
            'mark_on_the_way'
        );

        $checkInCode = $onTheWayBooking->check_in_code;

        $this->assertSame(BookingStatus::OnTheWay, $onTheWayBooking->status);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $checkInCode);
        $this->assertNotNull($onTheWayBooking->check_in_code_expires_at);
        $this->assertDatabaseHas('booking_timeline_events', [
            'booking_id' => $booking->id,
            'event_type' => 'staff_on_the_way',
        ]);

        Sanctum::actingAs($customer);
        $this->getJson("/api/customer/bookings/{$booking->id}")
            ->assertOk()
            ->assertJsonPath(
                'data.booking.check_in.code',
                $checkInCode
            );

        $notification = $customer->notifications()->latest()->first();
        $this->assertSame('staff_on_the_way', $notification->data['event_type']);

        $startedBooking = $transitionService->transition(
            $onTheWayBooking,
            'start',
            $checkInCode
        );

        $this->assertSame(BookingStatus::InProgress, $startedBooking->status);
        $this->assertNotNull($startedBooking->checked_in_at);
        $this->assertNull($startedBooking->check_in_code);
        $this->assertDatabaseHas('booking_timeline_events', [
            'booking_id' => $booking->id,
            'event_type' => 'service_started',
        ]);
    }

    public function test_an_invalid_check_in_code_cannot_start_work(): void
    {
        [, $booking] = $this->createAcceptedBooking();
        $transitionService = app(BookingStatusTransitionService::class);

        $onTheWayBooking = $transitionService->transition(
            $booking,
            'mark_on_the_way'
        );

        try {
            $transitionService->transition(
                $onTheWayBooking,
                'start',
                '000000'
            );

            $this->fail('An invalid check-in code should prevent service start.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'check_in_code',
                $exception->errors()
            );
        }

        $booking->refresh();

        $this->assertSame(BookingStatus::OnTheWay, $booking->status);
        $this->assertNull($booking->checked_in_at);
    }

    /**
     * @return array{0: User, 1: Booking}
     */
    private function createAcceptedBooking(): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = ServiceCategory::create([
            'name' => 'Repairs',
            'slug' => 'repairs',
        ]);
        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => 'Water pump repair',
            'slug' => 'water-pump-repair',
            'base_price' => '30000.00',
        ]);
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_price' => $service->base_price,
            'scheduled_at' => now()->addDay(),
            'phone' => '09123456789',
            'address' => 'Yangon',
            'status' => BookingStatus::Accepted,
        ]);

        return [$customer, $booking];
    }
}
