<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\RecurringServicePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringServicePlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_completed_booking_creates_one_plan_and_a_due_plan_sends_a_reminder(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = ServiceCategory::create(['name' => 'Maintenance', 'slug' => 'maintenance']);
        $service = Service::create(['service_category_id' => $category->id, 'name' => 'Aircon cleaning', 'slug' => 'aircon-cleaning', 'base_price' => '40000.00']);
        $booking = Booking::create(['customer_id' => $customer->id, 'service_id' => $service->id, 'service_name' => $service->name, 'service_price' => $service->base_price, 'scheduled_at' => now()->subDays(90), 'phone' => '09123456789', 'address' => 'Yangon', 'status' => BookingStatus::Completed, 'completed_at' => now()->subDays(90)]);
        $plans = app(RecurringServicePlanService::class);

        $plan = $plans->createFromCompletedBooking($booking, $customer, ['interval_days' => 90, 'reminder_days_before' => 7]);
        $samePlan = $plans->createFromCompletedBooking($booking, $customer, ['interval_days' => 90, 'reminder_days_before' => 7]);

        $this->assertSame($plan->id, $samePlan->id);
        $plan->update(['next_reminder_at' => now()->subMinute()]);

        $this->assertSame(1, $plans->sendDueReminders());
        $this->assertSame('service_plan_due', $customer->notifications()->latest()->first()->data['event_type']);
        $this->assertTrue($plan->fresh()->next_reminder_at->isFuture());
    }
}
