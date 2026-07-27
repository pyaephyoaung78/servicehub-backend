<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'body' => $this->body, 'attachment' => $this->attachment_path ? ['name' => $this->attachment_original_name, 'mime_type' => $this->attachment_mime_type, 'size' => $this->attachment_size, 'url' => route('api.booking-messages.attachment', [$this->booking_id, $this->id])] : null, 'sender' => $this->whenLoaded('sender', fn () => ['id' => $this->sender->id, 'name' => $this->sender->name, 'role' => $this->sender->role]), 'created_at' => $this->created_at?->toISOString()];
    }
}
