<?php

use App\Models\Booking;
use App\Models\StaffProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Booking::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(StaffProfile::class)->constrained()->restrictOnDelete();
            $table->string('kind', 20);
            $table->string('image_path');
            $table->string('image_original_name');
            $table->string('image_mime_type', 100);
            $table->unsignedBigInteger('image_size');
            $table->text('note')->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['booking_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_proofs');
    }
};
