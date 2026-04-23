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
        Schema::table('lectures', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published'])->default('draft')->after('description');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['status', 'teacher_id']);
        });
    }
};
