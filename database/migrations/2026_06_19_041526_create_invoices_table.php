<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('issued_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('invoice_no')->unique();

            $table->string('service_name');
            $table->decimal('service_price', 12, 2);

            $table->decimal('extra_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);

            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('remaining_amount', 12, 2);

            $table->string('payment_status')
                ->default(PaymentStatus::Unpaid->value);

            $table->timestamp('issued_at');
            $table->timestamp('paid_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('customer_id');
            $table->index('payment_status');
            $table->index('issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};