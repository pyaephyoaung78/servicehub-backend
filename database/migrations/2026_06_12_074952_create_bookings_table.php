<?php

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignIdFor(Service::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Snapshots preserve historical booking information.
            $table->string('service_name');
            $table->decimal('service_price', 12, 2);

            $table->dateTime('scheduled_at');
            $table->string('phone', 30);
            $table->text('address');
            $table->text('customer_note')->nullable();

            $table->string('status')->default('pending');

            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('scheduled_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};