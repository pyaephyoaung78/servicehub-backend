<?php

use App\Models\ServiceCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(ServiceCategory::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->decimal('base_price', 12, 2)->default(0);
            $table->unsignedInteger('estimated_duration_minutes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('service_category_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};