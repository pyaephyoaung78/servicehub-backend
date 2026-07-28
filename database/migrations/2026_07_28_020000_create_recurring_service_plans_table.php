<?php

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_service_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Service::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Booking::class, 'source_booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('service_name');
            $table->unsignedSmallInteger('interval_days');
            $table->unsignedSmallInteger('reminder_days_before')->default(7);
            $table->timestamp('next_reminder_at');
            $table->timestamp('last_reminded_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['customer_id', 'service_id']);
            $table->index(['is_active', 'next_reminder_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_service_plans');
    }
};
