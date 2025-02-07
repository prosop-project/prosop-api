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
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('type')->nullable();
            $table->string('description')->nullable();
            $table->string('value')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedBigInteger('click_count')->default(0);
            $table->timestamps();

            // Indexes - Postgres requires indexes for foreign keys to be assigned manually.
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
