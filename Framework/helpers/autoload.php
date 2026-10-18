<?php

use Framework\Core\Config;

if (!function_exists('config')) {
    $configArray = require __DIR__ . '/configFromDotenv.php';
    Config::Load($configArray);

    function config(string $key, $default = null)
    {
        return Config::Get($key, $default);
    }
}

if (!function_exists('dd')) {
    function dd(string|array|int|float|bool ...$args)
    {
        foreach ($args as $arg) {
            echo '<pre>';
            var_dump($arg);
            echo '</pre>';
        }
        die();
    }
}

if (!function_exists('throwException')) {
    function throwException(string $message, int $code = 500)
    {
        throw new Exception($message, $code);
    }
}

if (!function_exists('catchException')) {
    function catchException(Throwable $e, int $http_response_code = 500, ?Framework\Http\Response $response = null)
    {
        http_response_code($http_response_code);

        if ($http_response_code === 500) {
            $debug = config('app.debug', false);
            $exception = $e;
            require_once config('dir.base') . '/Framework/Views/errors/500.php';
            exit;
        }
        if ($http_response_code === 404) {
            $message = $e->getMessage();
            require_once config('dir.base') . '/Framework/Views/errors/404.php';
            exit;
        }
    }
}
