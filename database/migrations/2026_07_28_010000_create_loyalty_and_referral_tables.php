<?php

use App\Models\Booking;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltyReward;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->unique()->after('role');
            $table->foreignId('referred_by')->nullable()->after('referral_code')
                ->constrained('users')->nullOnDelete();
        });

        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('points_cost');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('loyalty_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(LoyaltyReward::class)->constrained()->restrictOnDelete();
            $table->unsignedInteger('points_cost');
            $table->string('redemption_code', 24)->unique();
            $table->string('status', 20)->default('pending');
            $table->foreignIdFor(User::class, 'reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('loyalty_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'customer_id')->constrained('users')->cascadeOnDelete();
            $table->integer('points');
            $table->string('type', 40);
            $table->foreignIdFor(Booking::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(LoyaltyRedemption::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class, 'referred_customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('description');
            $table->timestamps();

            $table->unique(['customer_id', 'booking_id', 'type']);
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_point_transactions');
        Schema::dropIfExists('loyalty_redemptions');
        Schema::dropIfExists('loyalty_rewards');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropUnique(['referral_code']);
            $table->dropColumn('referral_code');
        });
    }
};
