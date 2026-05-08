<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_attendance_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('class_rooms')->cascadeOnDelete();
            $table->integer('total_sessions')->default(0);
            $table->integer('present_count')->default(0);
            $table->integer('late_count')->default(0);
            $table->integer('absent_count')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance_stats');
    }
};
