<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'key' => env('STRIPE_KEY'),
    ],
    
    'github' => [
        'token' => env('GITHUB_TOKEN'),
        'username' => env('GITHUB_USERNAME'),
    ],

    'aws' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bedrock' => [
            // Available text models (make sure you've enabled access in AWS Bedrock console):
            // - anthropic.claude-v2 (older but widely available)
            // - anthropic.claude-instant-v1 (faster responses)
            // - anthropic.claude-3-haiku-20240307-v1:0 (smaller Claude 3 model)
            // - anthropic.claude-3-sonnet-20240229-v1:0 (larger Claude 3 model)
            'text_model_id' => env('AWS_BEDROCK_TEXT_MODEL_ID', 'anthropic.claude-3-sonnet-20240229-v1:0'),
            
            // Available image models:
            // - stability.stable-diffusion-xl-v1
            // - stability.stable-diffusion-xl-v0
            'image_model_id' => env('AWS_BEDROCK_IMAGE_MODEL_ID', 'stability.stable-diffusion-xl-v1'),
        ],
    ],
    
    'ai' => [
        'api_key' => env('AI_API_KEY'),
        'endpoint' => env('AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'model' => env('AI_MODEL', 'gpt-4'),
        'temperature' => env('AI_TEMPERATURE', 0.2),
        'max_tokens' => env('AI_MAX_TOKENS', 1000),
    ],

];
