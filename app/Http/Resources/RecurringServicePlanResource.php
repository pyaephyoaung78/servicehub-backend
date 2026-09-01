<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringServicePlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'service_name' => $this->service_name, 'interval_days' => $this->interval_days, 'reminder_days_before' => $this->reminder_days_before, 'next_reminder_at' => $this->next_reminder_at?->toISOString(), 'is_active' => $this->is_active, 'source_booking_id' => $this->source_booking_id];
    }
}
