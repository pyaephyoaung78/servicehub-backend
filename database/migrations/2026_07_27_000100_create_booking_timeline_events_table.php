<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Booking::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->nullableMorphs('actor');
            $table->string('event_type', 80);
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['booking_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_timeline_events');
    }
};
