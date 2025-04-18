<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-rekognition-2016-06-27.html#shape-face
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aws_faces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aws_user_id')->nullable()->constrained();
            $table->foreignId('aws_collection_id')->constrained();
            $table->uuid('external_face_id')->comment('This is the face id that is set by aws.');
            $table->decimal('confidence', 5, 2)
                ->comment('The confidence level of detection contains a face.');
            $table->string('external_image_id')->nullable()
                ->comment('The external image id that is set by us in order to distinguish the faces by the image.');
            $table->uuid('image_id')->comment('The image uuid that is set by aws for the image.');
            $table->timestamps();

            // Indexes
            $table->index('aws_user_id');
            $table->index('aws_collection_id');

            // Unique keys
            $table->unique('external_face_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aws_faces');
    }
};
