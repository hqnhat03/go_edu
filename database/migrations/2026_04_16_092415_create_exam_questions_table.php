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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->text('question');
            $table->enum('type', ['multiple_choice', 'essay'])->default('multiple_choice');
            $table->json('options')->nullable();            // Các lựa chọn (chỉ với multiple_choice)
            $table->string('correct_answer')->nullable();  // Đáp án đúng (chỉ với multiple_choice)
            $table->decimal('score', 5, 2)->default(1);
            $table->integer('order_number')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
