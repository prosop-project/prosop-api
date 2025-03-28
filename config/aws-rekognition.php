<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | AWS credentials
    |--------------------------------------------------------------------------
    |
    | Here you may specify the credentials for AWS used to sign requests.
    | See https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.AwsClient.html#method___construct for more information.
    |
    */
    'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS region
    |--------------------------------------------------------------------------
    |
    | Here you may specify the region to connect to.
    | See https://docs.aws.amazon.com/general/latest/gr/rande.html for a list of available regions.
    */
    'region' => env('AWS_REGION', 'us-east-1'),

    /*
    |--------------------------------------------------------------------------
    | AWS version of the webservice to utilize (e.g., 2006-03-01)
    |--------------------------------------------------------------------------
    |
    | Here you may specify the version of the webservice.
    | See https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.AwsClient.html#method___construct for more information.
    */
    'version' => env('AWS_VERSION', 'latest'),

    /*
     |--------------------------------------------------------------------------
     | AWS Rekognition reference prefix
     |--------------------------------------------------------------------------
     |
     | Here you may specify the prefix for the AWS Rekognition service which can be used for external user id and so on.
     */
    'reference_prefix' => env('AWS_REKOGNITION_REFERENCE_PREFIX', 'rekognition-v1'),

    /*
     |--------------------------------------------------------------------------
     | AWS Rekognition default value for threshold (used for associate faces, search faces by image, etc.)
     |--------------------------------------------------------------------------
     */
    'user_match_threshold' => env('AWS_USER_MATCH_THRESHOLD', 80),

    /*
     |--------------------------------------------------------------------------
     | AWS Rekognition default value for max users to return for search results e.g. searchUsersByImage max_users parameter.
     |--------------------------------------------------------------------------
     */
    'search_result_max_users' => env('AWS_SEARCH_RESULT_MAX_USERS', 5),

    /*
     |--------------------------------------------------------------------------
     | AWS Rekognition default value for max faces per user (max faces can be indexed for a user).
     |--------------------------------------------------------------------------
     */
    'max_faces_per_user' => env('AWS_MAX_FACES_PER_USER', 5),
];
