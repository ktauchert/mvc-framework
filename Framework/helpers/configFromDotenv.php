<?php

$dotenv = Dotenv\Dotenv::CreateImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

return [
    'app' =>  [
        'name' => $_ENV['APP_NAME'] ?? 'MyApp',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
        'locale' => $_ENV['APP_LOCALE'] ?? 'en',
    ],
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_DATABASE'] ?? 'myapp',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ],
    'dir' => [
        'base' => __DIR__ . '/../../',
        'app' => __DIR__ . '/../../src/app/',
        'config' => __DIR__ . '/.',
        'public' => __DIR__ . '/../../public/',
        'storage' => __DIR__ . '/../../storage/',
    ],
];
