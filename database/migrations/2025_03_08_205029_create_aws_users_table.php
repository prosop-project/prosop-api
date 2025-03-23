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
        Schema::create('aws_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('aws_collection_id')->constrained();
            $table->string('external_user_id')->comment('The user id in the external aws system');
            $table->string('external_user_status')->nullable()
                ->comment('The user status in the external aws system');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('aws_collection_id');

            // Unique keys
            $table->unique(['aws_collection_id', 'external_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aws_users');
    }
};
