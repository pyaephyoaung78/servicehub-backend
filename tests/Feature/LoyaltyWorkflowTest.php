<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\LoyaltyPointTransaction;
use App\Models\LoyaltyReward;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProof;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\BookingStatusTransitionService;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoyaltyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_completion_awards_points_once_and_rewards_a_successful_referral(): void
    {
        $referrer = User::factory()->create([
            'role' => 'customer',
            'referral_code' => 'SHREFERRER',
        ]);
        [$customer, $booking] = $this->createInProgressBooking([
            'referred_by' => $referrer->id,
        ]);

        app(BookingStatusTransitionService::class)->transition($booking, 'complete');

        $this->assertDatabaseHas('loyalty_point_transactions', [
            'customer_id' => $customer->id,
            'booking_id' => $booking->id,
            'type' => 'booking_completed',
            'points' => LoyaltyService::COMPLETED_BOOKING_POINTS,
        ]);
        $this->assertSame(250, app(LoyaltyService::class)->pointsFor($customer));
        $this->assertSame(250, app(LoyaltyService::class)->pointsFor($referrer));

        app(LoyaltyService::class)->awardForCompletedBooking($booking->fresh());

        $this->assertSame(3, LoyaltyPointTransaction::query()->count());
    }

    public function test_customer_reward_redemption_debits_points_and_rejection_refunds_them(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $reward = LoyaltyReward::create([
            'name' => '10% service voucher',
            'points_cost' => 300,
            'is_active' => true,
        ]);
        LoyaltyPointTransaction::create([
            'customer_id' => $customer->id,
            'points' => 500,
            'type' => 'manual_seed',
            'description' => 'Test points.',
        ]);

        Sanctum::actingAs($customer);
        $this->postJson("/api/customer/loyalty/rewards/{$reward->id}/redeem")
            ->assertCreated()
            ->assertJsonPath('data.redemption.status', 'pending');

        $this->assertSame(200, app(LoyaltyService::class)->pointsFor($customer));

        $this->postJson("/api/customer/loyalty/rewards/{$reward->id}/redeem")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reward');

        $redemption = $customer->loyaltyRedemptions()->firstOrFail();
        app(LoyaltyService::class)->reviewRedemption($redemption, $admin, 'rejected', 'Unavailable in this area.');

        $this->assertSame(500, app(LoyaltyService::class)->pointsFor($customer));
        $this->assertDatabaseHas('loyalty_redemptions', [
            'id' => $redemption->id,
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_registration_links_a_valid_referrer_and_generates_a_code(): void
    {
        $referrer = User::factory()->create([
            'role' => 'customer',
            'referral_code' => 'SHWELCOME',
        ]);

        $this->postJson('/api/register', [
            'name' => 'New Customer',
            'email' => 'new-customer@example.test',
            'password' => 'password123',
            'referral_code' => 'SHWELCOME',
        ])->assertCreated();

        $customer = User::query()->where('email', 'new-customer@example.test')->firstOrFail();
        $this->assertSame($referrer->id, $customer->referred_by);
        $this->assertNotEmpty($customer->referral_code);
    }

    /**
     * @return array{0: User, 1: Booking}
     */
    private function createInProgressBooking(array $customerAttributes = []): array
    {
        $customer = User::factory()->create(array_merge(['role' => 'customer'], $customerAttributes));
        $staffUser = User::factory()->create(['role' => 'staff']);
        $staffProfile = StaffProfile::create([
            'user_id' => $staffUser->id,
            'phone' => '09987654321',
            'is_active' => true,
            'is_available' => true,
        ]);
        $category = ServiceCategory::create(['name' => 'Repairs', 'slug' => 'repairs']);
        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => 'Appliance repair',
            'slug' => 'appliance-repair',
            'base_price' => '40000.00',
        ]);
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_price' => $service->base_price,
            'scheduled_at' => now()->subHour(),
            'phone' => '09123456789',
            'address' => 'Yangon',
            'status' => BookingStatus::InProgress,
            'started_at' => now()->subMinutes(30),
        ]);

        foreach (['before', 'after'] as $kind) {
            ServiceProof::create([
                'booking_id' => $booking->id,
                'staff_profile_id' => $staffProfile->id,
                'kind' => $kind,
                'image_path' => "service-proofs/{$kind}.jpg",
                'image_original_name' => "{$kind}.jpg",
                'image_mime_type' => 'image/jpeg',
                'image_size' => 1000,
                'captured_at' => now(),
            ]);
        }

        return [$customer, $booking];
    }
}
