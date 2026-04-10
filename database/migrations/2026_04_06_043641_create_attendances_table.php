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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('shift_id')->constrained();
            
            $table->timestamp('check_in_at')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_in_accuracy', 8, 2)->nullable();
            $table->string('check_in_address', 500)->nullable();
            $table->string('check_in_photo', 500)->nullable();
            $table->text('check_in_note')->nullable();
            $table->foreignId('check_in_worksite_id')->nullable()->constrained('worksites')->nullOnDelete();
            $table->boolean('check_in_within_geofence')->nullable();
            $table->text('check_in_outside_reason')->nullable();
            
            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->decimal('check_out_accuracy', 8, 2)->nullable();
            $table->string('check_out_address', 500)->nullable();
            $table->string('check_out_photo', 500)->nullable();
            $table->text('check_out_note')->nullable();
            $table->foreignId('check_out_worksite_id')->nullable()->constrained('worksites')->nullOnDelete();
            $table->boolean('check_out_within_geofence')->nullable();
            $table->text('check_out_outside_reason')->nullable();
            
            $table->enum('status', ['checked_in', 'checked_out', 'missed_checkout', 'absent', 'leave', 'holiday'])->default('checked_in');
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->boolean('is_early_leave')->default(false);
            $table->integer('early_leave_minutes')->default(0);
            $table->integer('working_minutes')->default(0);
            $table->integer('ot_minutes')->default(0);
            $table->boolean('is_outside_geofence')->default(false);
            
            $table->string('device_info', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_amended')->default(false);
            
            $table->timestamps();
            
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
