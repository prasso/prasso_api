'connections' => [

    // ...

    'testing' => [
        'driver' => 'mysql',
        'host' => env('DB_TEST_HOST', '127.0.0.1'),
        'port' => env('DB_TEST_PORT', '3306'),
        'database' => env('DB_TEST_DATABASE', 'forge'),
        'username' => env('DB_TEST_USERNAME', 'forge'),
        'password' => env('DB_TEST_PASSWORD', ''),
        'unix_socket' => env('DB_TEST_SOCKET', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],

    // ...
]