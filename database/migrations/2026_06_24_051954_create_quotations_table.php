<?php

use App\Enums\QuotationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('quotation_no')->unique();

            $table->string('service_name');
            $table->decimal('service_price', 12, 2);

            $table->decimal('extra_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            $table->string('status')
                ->default(QuotationStatus::Sent->value);

            $table->text('admin_note')->nullable();
            $table->text('customer_response_note')->nullable();

            $table->timestamp('valid_until')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};