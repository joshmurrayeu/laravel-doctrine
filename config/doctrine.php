<?php

declare(strict_types=1);

return [
    'debug' => env('APP_DEBUG', false),

    'manager' => [
        'driver' => env('DB_CONNECTION', 'sqlite'),
        'host' => env('DB_HOST'),
        'port' => env('DB_PORT'),
        'dbname' => env('DB_DATABASE', database_path('database.sqlite')), // support for sqlite
        'user' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
    ],

    'paths' => [
        // app_path('Auth/Entities'),
    ],

    'proxies' => [
        'namespace' => 'DoctrineProxies',
        'path' => storage_path('proxies'),
        'auto_generate' => env('DOCTRINE_PROXY_AUTOGENERATE', false),
    ],

    'caching' => [
        'redis' => [
            'namespace' => 'doctrine.',
            'lifetime' => 30,
        ],
    ],

    'subscribers' => [
        // LaravelDoctrine\Subscribers\TimestampSubscriber::class,
    ],

    'filters' => [
        // LaravelDoctrine\Filters\SoftDeleteFilter::class,
    ],

    /**
     * By default, query logging has been enabled. Remove the
     */
    'middlewares' => [
        function (Illuminate\Foundation\Application $application): Doctrine\DBAL\Driver\Middleware|false {
            return new Doctrine\DBAL\Logging\Middleware(
                $application->make(Illuminate\Log\Logger::class)
            );
        },
    ],
];
