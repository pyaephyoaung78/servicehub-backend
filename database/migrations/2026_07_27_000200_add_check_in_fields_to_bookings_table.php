<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('check_in_code')->nullable()->after('started_at');
            $table->timestamp('check_in_code_expires_at')
                ->nullable()
                ->after('check_in_code');
            $table->timestamp('checked_in_at')
                ->nullable()
                ->after('check_in_code_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_code',
                'check_in_code_expires_at',
                'checked_in_at',
            ]);
        });
    }
};
