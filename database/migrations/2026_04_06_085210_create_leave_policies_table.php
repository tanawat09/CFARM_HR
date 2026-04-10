<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();        // e.g. annual, sick, personal
            $table->string('name', 100);                 // Thai display name
            $table->string('icon', 10)->default('📋');   // Emoji icon
            $table->integer('quota_days')->default(0);
            $table->boolean('requires_reason')->default(true);
            $table->integer('requires_attachment_after_days')->nullable(); // null = never
            $table->boolean('probation_allowed')->default(true);
            $table->boolean('is_tenure_based')->default(false);     // annual-leave style
            $table->integer('tenure_threshold_years')->nullable();  // e.g. 5
            $table->integer('tenure_bonus_days')->nullable();       // extra days after threshold
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
