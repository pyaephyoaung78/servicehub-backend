<?php

use App\Models\Booking;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Booking::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Service::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(StaffProfile::class)->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignIdFor(User::class, 'reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique('booking_id');
            $table->index(['status', 'rating']);
            $table->index(['staff_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_reviews');
    }
};
