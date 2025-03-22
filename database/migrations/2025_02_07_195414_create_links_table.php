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
            $table->string('description')->nullable()
                ->comment('A short description of the link e.g. instagram, x etc');
            $table->string('value')->nullable()
                ->comment('The actual link value e.g. https://instagram.com/username');
            $table->boolean('is_visible')->default(true)
                ->comment('Whether the link is visible to the public, can be toggled by the user');
            $table->unsignedBigInteger('click_count')->default(0)
                ->comment('The amount of times the link has been clicked');
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
