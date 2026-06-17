<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('phone', 30);
            $table->text('bio')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};