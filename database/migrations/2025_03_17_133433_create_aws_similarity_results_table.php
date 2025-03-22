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
        Schema::create('aws_similarity_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_operation_id')->constrained();
            $table->foreignId('aws_user_id')
                ->nullable()
                ->comment('The user who is found by the analysis operation')
                ->constrained();
            $table->foreignId('aws_face_id')
                ->nullable()
                ->comment('The face that is found by the analysis operation')
                ->constrained();
            $table->decimal('similarity', 5, 2);
            $table->jsonb('metadata')
                ->nullable()
                ->comment('Additional metadata for the similarity result e.g. external_user_status on aws side');
            $table->timestamps();

            // Indexes
            $table->index('analysis_operation_id');
            $table->index('aws_user_id');
            $table->index('aws_face_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aws_similarity_results');
    }
};
