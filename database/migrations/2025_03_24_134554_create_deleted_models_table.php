<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @see https://github.com/spatie/laravel-deleted-models
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deleted_models', function (Blueprint $table) {
            $table->id();

            $table->string('key', 40);
            $table->string('model');
            $table->json('values');

            $table->timestamps();

            $table->unique(['model', 'key']);
        });
    }
};
