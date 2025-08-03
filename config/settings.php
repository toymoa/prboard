<?php

declare(strict_types=1);

use Monolog\Level;

return [
    'settings' => [
        'displayErrorDetails' => $_ENV['APP_DEBUG'] === 'true',
        'logErrors' => true,
        'logErrorDetails' => true,

        'logger' => [
            'name' => $_ENV['APP_NAME'] ?? 'app',
            'path' => __DIR__ . '/../' . ($_ENV['LOG_PATH'] ?? 'logs/app.log'),
            'level' => Level::Debug,
        ],

        'redis' => [
            'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
            'password' => $_ENV['REDIS_PASSWORD'] ?? '',
            'database' => (int) ($_ENV['REDIS_DATABASE'] ?? 0),
        ],

        'twig' => [
            'template_path' => __DIR__ . '/../templates',
            'twig' => [
                'cache' => $_ENV['APP_ENV'] === 'production' ? __DIR__ . '/../var/cache/twig' : false,
                'debug' => $_ENV['APP_DEBUG'] === 'true',
                'auto_reload' => $_ENV['APP_ENV'] === 'development',
            ],
        ],
    ],
];
