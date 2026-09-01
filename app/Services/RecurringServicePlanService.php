<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\RecurringServicePlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecurringServicePlanService
{
    public function createFromCompletedBooking(Booking $booking, User $customer, array $data): RecurringServicePlan
    {
        if ($booking->customer_id !== $customer->id || $booking->status !== BookingStatus::Completed) {
            throw ValidationException::withMessages(['booking' => ['Only your completed bookings can become a service plan.']]);
        }

        if ($data['reminder_days_before'] >= $data['interval_days']) {
            throw ValidationException::withMessages(['reminder_days_before' => ['Reminder lead time must be shorter than the service interval.']]);
        }

        return DB::transaction(function () use ($booking, $customer, $data) {
            $nextReminderAt = $booking->completed_at
                ->copy()
                ->addDays($data['interval_days'] - $data['reminder_days_before']);

            return RecurringServicePlan::updateOrCreate(
                ['customer_id' => $customer->id, 'service_id' => $booking->service_id],
                ['source_booking_id' => $booking->id, 'service_name' => $booking->service_name, 'interval_days' => $data['interval_days'], 'reminder_days_before' => $data['reminder_days_before'], 'next_reminder_at' => $nextReminderAt, 'last_reminded_at' => null, 'is_active' => true]
            );
        });
    }

    public function sendDueReminders(): int
    {
        $sent = 0;
        RecurringServicePlan::query()->with('customer')->where('is_active', true)->where('next_reminder_at', '<=', now())->orderBy('id')->chunkById(100, function ($plans) use (&$sent) {
            foreach ($plans as $plan) {
                $didSend = DB::transaction(function () use ($plan) {
                    $locked = RecurringServicePlan::query()->with('customer')->lockForUpdate()->find($plan->id);
                    if (! $locked || ! $locked->is_active || $locked->next_reminder_at->isFuture()) return false;
                    $locked->customer?->notify(new \App\Notifications\BookingWorkflowNotification($locked->source_booking_id, 'service_plan_due', 'Time to plan your next '.$locked->service_name, 'Your maintenance plan is due soon. Open the completed booking to choose a new appointment time.'));
                    $locked->update(['last_reminded_at' => now(), 'next_reminder_at' => $locked->next_reminder_at->copy()->addDays($locked->interval_days)]);
                    return true;
                });
                if ($didSend) $sent++;
            }
        });
        return $sent;
    }
}
