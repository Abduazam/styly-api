<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Gemini API Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify your Gemini API Key and organization. This will be
    | used to authenticate with the Gemini API - you can find your API key
    | on Google AI Studio, at https://aistudio.google.com/app/apikey.
    */

    'api_key' => env('GEMINI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Gemini Base URL
    |--------------------------------------------------------------------------
    |
    | If you need a specific base URL for the Gemini API, you can provide it here.
    | Otherwise, leave empty to use the default value.
    */
    'base_url' => env('GEMINI_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('GEMINI_REQUEST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Image Processing Defaults
    |--------------------------------------------------------------------------
    |
    | These options control how we talk to Gemini when post-processing wardrobe
    | uploads. Update the model or prompt if you roll out a different flow.
    */
    'images' => [
        'edit_model' => env('GEMINI_IMAGE_EDIT_MODEL', 'models/imageediting'),
        'prompt' => env('GEMINI_IMAGE_EDIT_PROMPT', 'Remove the current background and put the product on a clean white surface with a soft shadow.'),
        'disk' => env('GEMINI_IMAGE_DISK', 'public'),
        'output_directory' => env('GEMINI_IMAGE_OUTPUT_DIR', 'wardrobe/processed'),
    ],
];
