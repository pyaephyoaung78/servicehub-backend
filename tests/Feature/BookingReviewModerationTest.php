<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingReview;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_approve_a_pending_customer_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $review = $this->createReview();

        $this->actingAs($admin)
            ->patch(route('admin.booking-reviews.moderate', $review), [
                'status' => 'approved',
            ])
            ->assertRedirect(route('admin.booking-reviews.index'));

        $this->assertDatabaseHas('booking_reviews', [
            'id' => $review->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_an_already_moderated_review_cannot_be_moderated_again(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $review = $this->createReview(['status' => 'approved']);

        $this->actingAs($admin)
            ->from(route('admin.booking-reviews.index'))
            ->patch(route('admin.booking-reviews.moderate', $review), [
                'status' => 'hidden',
            ])
            ->assertRedirect(route('admin.booking-reviews.index'))
            ->assertSessionHasErrors('review');

        $this->assertDatabaseHas('booking_reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);
    }

    public function test_the_admin_queue_displays_pending_reviews(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $review = $this->createReview(['comment' => 'Arrived on time and did excellent work.']);

        $this->actingAs($admin)
            ->get(route('admin.booking-reviews.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee($review->customer->name)
            ->assertSee('Arrived on time and did excellent work.');
    }

    private function createReview(array $attributes = []): BookingReview
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = ServiceCategory::create([
            'name' => 'Cleaning',
            'slug' => 'cleaning',
        ]);
        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => 'Deep home cleaning',
            'slug' => 'deep-home-cleaning',
            'base_price' => '45000.00',
        ]);
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_price' => $service->base_price,
            'scheduled_at' => now()->subDay(),
            'phone' => '09123456789',
            'address' => 'Yangon',
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
        ]);

        return BookingReview::create(array_merge([
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'rating' => 5,
            'comment' => 'Very good service.',
            'status' => 'pending',
        ], $attributes))->load('customer');
    }
}
