<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('cancellation_reason')
                ->nullable()
                ->after('completed_at');

            $table->foreignId('cancelled_by')
                ->nullable()
                ->after('cancellation_reason')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('cancelled_by');

            $table->text('rejection_reason')
                ->nullable()
                ->after('cancelled_at');

            $table->foreignId('rejected_by')
                ->nullable()
                ->after('rejection_reason')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')
                ->nullable()
                ->after('rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropConstrainedForeignId('rejected_by');

            $table->dropColumn([
                'cancellation_reason',
                'cancelled_at',
                'rejection_reason',
                'rejected_at',
            ]);
        });
    }
};