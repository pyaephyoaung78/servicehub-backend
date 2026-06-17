<?php

use App\Models\Booking;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Booking::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignIdFor(StaffProfile::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('assigned_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('status')->default('pending');

            $table->text('admin_note')->nullable();
            $table->text('staff_response_note')->nullable();

            $table->timestamp('assigned_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['staff_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_assignments');
    }
};