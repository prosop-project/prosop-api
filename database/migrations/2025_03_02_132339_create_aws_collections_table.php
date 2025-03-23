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
            $table->string('external_collection_id');
            $table->string('external_collection_arn');
            $table->json('tags')->nullable()
                ->comment('Tags attached to collection. see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-rekognition-2016-06-27.html#createcollection');
            $table->string('face_model_version')->nullable()
                ->comment('Version number of the face detection model associated with the collection');
            $table->timestamps();

            // Unique keys
            $table->unique('external_collection_id');
            $table->unique('external_collection_arn');
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
