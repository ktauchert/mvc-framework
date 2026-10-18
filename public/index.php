<?php

/**
 * Entry point for the application.
 * This script captures the incoming HTTP request, resolves the appropriate controller,
 * and executes the controller's run method to handle the request.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $request = Framework\Http\Request::capture();
    $resolver = new Framework\Core\Resolver();

    $path = $request->getPath();
    $className = $resolver->resolveRouteToClass($path);

    if (!$className || !class_exists($className)) {
        throw new Exception("Controller class '$className' not found.", 404);
    } else {
        $controller = new $className($request);
        if (!method_exists($controller, 'run')) {
            throw new Exception("Method 'run' not found in controller '$className'.", 404);
        }
        $controller->run();
        $controller->send();

    }


} catch (Throwable $e) {
    catchException($e, $e->getCode() ?: 500);
}
