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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Actor (Who did it?)
            $table->nullableMorphs('causer');
            
            // Action & Subject (What happened?)
            $table->string('action');
            $table->nullableMorphs('subject');
            
            // Visibility & Scope (Who can see?)
            $table->string('visibility')->default('admin');
            $table->unsignedBigInteger('group_id')->nullable(); 

            // Details
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamp('created_at')->useCurrent();

            // Indexes for faster querying
            $table->index(['visibility', 'group_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
