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
        Schema::create('analysis_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->comment('The user who requested the analysis')
                ->constrained();
            $table->foreignId('aws_collection_id')->constrained();
            $table->string('operation')
                ->comment('The operation to be performed on the collection e.g. search_users_by_image');
            $table->string('status')->nullable();
            $table->jsonb('metadata')
                ->nullable()
                ->comment('Additional metadata for the operation e.g. max_users');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'aws_collection_id', 'operation',]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_requests');
    }
};
