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
        Schema::create('aws_collections', function (Blueprint $table) {
            $table->id();
            $table->string('external_collection_id')->unique();
            $table->string('external_collection_arn')->unique();
            $table->json('tags')->nullable();
            $table->string('face_model_version')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aws_collections');
    }
};
