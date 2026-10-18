<?php

declare(strict_types=1);

namespace Framework\Core;

class Resolver
{
    public static function ResolveView(string $view): string
    {
        $viewPath = str_replace('.', '/', $view);
        $fullPath = config('dir.views') . $viewPath . '.php';

        if (!file_exists($fullPath)) {
            throw new \Exception("View file not found: " . $fullPath);
        }

        return $fullPath;
    }

    public static function resolveRouteToClass(string $route): string|bool
    {
        if ($route === '/' || $route === '') {
            $className = 'App\\Controllers\\IndexClass';
        } else {
            $route = trim($route, '/');
            $segments = explode('/', $route);
            $segments = array_map('ucfirst', $segments);
            $className = 'App\\Controllers\\' . implode('\\', $segments) . '\\IndexClass';
        }

        if (!class_exists($className)) {
            return false;
        }
        return $className;
    }

    public static function isAPIRoute(string $route): bool
    {
        return str_starts_with($route, '/api/') || str_starts_with($route, 'api/');
    }
}
