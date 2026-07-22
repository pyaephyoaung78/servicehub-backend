<?php

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Invoice::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('invoice_payment_id')
                ->nullable()
                ->unique()
                ->constrained('invoice_payments')
                ->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 100);
            $table->string('proof_path');
            $table->string('proof_original_name')->nullable();
            $table->string('proof_mime_type', 100)->nullable();
            $table->unsignedInteger('proof_size')->nullable();
            $table->text('note')->nullable();

            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();

            $table->index(['invoice_id', 'status']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
