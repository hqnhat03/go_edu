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
        Schema::create('exam_answer_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_result_id')->constrained('exam_results')->cascadeOnDelete();
            $table->uuid('question_id'); // Using UUID for question_id
            $table->text('answer_content')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->boolean('is_correct')->nullable(); // null for essays not yet graded
            $table->text('teacher_comment')->nullable();
            $table->timestamps();
            
            // Add foreign key constraint for question_id if needed, 
            // but since it's UUID and might come from a table with HasUuids, 
            // we'll just refer to it. Assuming exam_questions table exists.
            $table->foreign('question_id')->references('id')->on('exam_questions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_answer_details');
    }
};
