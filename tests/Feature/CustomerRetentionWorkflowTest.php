<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerRetentionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_review_a_completed_booking_once(): void
    {
        [$customer, $booking] = $this->createCompletedBooking();
        Sanctum::actingAs($customer);

        $this->postJson("/api/customer/bookings/{$booking->id}/review", [
            'rating' => 5,
            'comment' => 'Professional and punctual service.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.review.status', 'pending')
            ->assertJsonPath('data.review.rating', 5);

        $this->assertDatabaseHas('booking_reviews', [
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'rating' => 5,
            'status' => 'pending',
        ]);

        $this->getJson("/api/customer/bookings/{$booking->id}")
            ->assertOk()
            ->assertJsonPath('data.booking.review.rating', 5);

        $this->postJson("/api/customer/bookings/{$booking->id}/review", [
            'rating' => 4,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('booking');
    }

    public function test_rebooking_uses_current_service_price_and_a_customer_selected_schedule(): void
    {
        [$customer, $booking, $service] = $this->createCompletedBooking();
        $service->update(['base_price' => '55000.00']);
        $scheduledAt = now()->addDays(4)->setTime(10, 30);
        Sanctum::actingAs($customer);

        $this->postJson("/api/customer/bookings/{$booking->id}/rebook", [
            'scheduled_at' => $scheduledAt->toISOString(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.booking.status', 'pending')
            ->assertJsonPath('data.booking.service.booked_price', '55000.00');

        $newBooking = Booking::query()
            ->where('customer_id', $customer->id)
            ->where('id', '!=', $booking->id)
            ->firstOrFail();

        $this->assertSame(BookingStatus::Pending, $newBooking->status);
        $this->assertSame('55000.00', (string) $newBooking->service_price);
        $this->assertSame($booking->address, $newBooking->address);
        $this->assertSame(
            $scheduledAt->toISOString(),
            $newBooking->scheduled_at->toISOString()
        );
        $this->assertDatabaseHas('booking_timeline_events', [
            'booking_id' => $newBooking->id,
            'event_type' => 'booking_rebooked',
        ]);
    }

    public function test_a_customer_can_save_and_remove_a_favorite_service(): void
    {
        [$customer, , $service] = $this->createCompletedBooking();
        Sanctum::actingAs($customer);

        $this->postJson("/api/customer/services/{$service->id}/favorite")
            ->assertCreated()
            ->assertJsonPath('data.is_favorite', true);

        $this->getJson('/api/customer/favorite-services')
            ->assertOk()
            ->assertJsonPath('data.services.0.id', $service->id);

        $this->postJson("/api/customer/services/{$service->id}/favorite")
            ->assertOk()
            ->assertJsonPath('data.is_favorite', false);

        $this->assertDatabaseMissing('customer_favorite_services', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
        ]);
    }

    /**
     * @return array{0: User, 1: Booking, 2: Service}
     */
    private function createCompletedBooking(): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = ServiceCategory::create([
            'name' => 'Home care',
            'slug' => 'home-care',
        ]);
        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => 'Air conditioner cleaning',
            'slug' => 'air-conditioner-cleaning',
            'base_price' => '45000.00',
            'is_active' => true,
        ]);
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_price' => $service->base_price,
            'scheduled_at' => now()->subDay(),
            'phone' => '09123456789',
            'address' => 'Yangon',
            'customer_note' => 'Please call before arriving.',
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
        ]);

        return [$customer, $booking, $service];
    }
}
