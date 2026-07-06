<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('on_the_way_at')
                ->nullable()
                ->after('status');

            $table->timestamp('started_at')
                ->nullable()
                ->after('on_the_way_at');

            $table->timestamp('completed_at')
                ->nullable()
                ->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'on_the_way_at',
                'started_at',
                'completed_at',
            ]);
        });
    }
};