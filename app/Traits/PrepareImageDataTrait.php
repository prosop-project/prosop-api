<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use MoeMizrak\Rekognition\Data\ImageData;

/**
 * Trait PrepareImageDataTrait for preparing image data from the image path.
 *
 * @class PrepareImageDataTrait
 */
trait PrepareImageDataTrait
{
    /**
     * Prepare the image data to be sent to AWS Rekognition.
     *
     * @param string $imagePath
     *
     * @return ImageData
     */
    protected function prepareImageData(string $imagePath): ImageData
    {
        // Read the image content
        $imageContent = Storage::get($imagePath);
        // Encode the image in base64
        $base64Image = base64_encode($imageContent);

        return new ImageData(
            bytes: $base64Image,
        );
    }
}
