<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_uuid')
                ->default(DB::raw('gen_random_uuid()'))
                ->comment('Publicly accessible uuid used in api requests that require user identification instead of user id which may be sensitive');
            $table->string('name')->nullable();
            $table->string('username');
            $table->string('description')->nullable()->comment('User short description - bio');
            $table->string('password');
            $table->unsignedBigInteger('views')->default(0)->comment('Number of profile views');
            $table->string('avatar')->nullable();
            $table->timestamp('avatar_updated_at')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();

            // Unique keys
            $table->unique('public_uuid');
            $table->unique('username');
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
