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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained();
            $table->year('year');
            $table->decimal('entitled_days', 4, 1);
            $table->decimal('used_days', 4, 1)->default(0);
            $table->decimal('remaining_days', 4, 1);
            $table->timestamps();
            $table->unique(['employee_id', 'leave_type_id', 'year'], 'leave_balances_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
