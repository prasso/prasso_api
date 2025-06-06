<?php

return [

    'default' => env('FILESYSTEM_DRIVER', 'local'),
    'cloud' => env('FILESYSTEM_CLOUD', 's3'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        'public' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'options' => [
                'http' => [
                    'verify' => base_path('cacert.pem'),
                ],
            ],
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'options' => [
                'http' => [
                    'verify' => base_path('cacert.pem'),
                ],
            ],
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
