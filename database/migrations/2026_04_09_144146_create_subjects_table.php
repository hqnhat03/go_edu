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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("category");
            $table->string("training_level");
            $table->enum("student_type", ["student", "employee"])->default("student");
            $table->enum("status", ["draft", "published", "archived"])->default("draft");
            $table->foreignId("created_by")->constrained("users")->cascadeOnDelete();

            $table->timestamps();
            $table->unique(["name", "training_level"], 'unique_name_training_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
