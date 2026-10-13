<?php

require_once __DIR__ . '/../vendor/autoload.php';

$baseAppPath = config('dir.app');

try {


    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = rtrim($path, '/');

    if ($path === '/' || $path === '') {
        $file = $baseAppPath . '/IndexClass.php';
        $className = "App\\Controllers\\IndexClass";
    } else {
        $file = $baseAppPath . $path . '/IndexClass.php';

        $segments = explode('/', trim($path, '/'));
        $segments = array_map('ucfirst', $segments);
        $className = "App\\Controllers\\" . implode("\\", $segments) . "\\IndexClass";
    }


    if (file_exists($file)) {
        require_once $file;

        if (class_exists($className)) {
            $controller = new $className();
            if (method_exists($controller, 'run')) {
                $controller->run();
            } else {
                throwException("Method 'run' not found in class '$className'", 500);
            }
        } else {
            throwException("Class '$className' not found in file '$file'", 500);
        }
    } else {
        throwException("The path '$path' is not known, please return to the Homepage.", 404);
    }
} catch (Throwable $e) {
    catchException($e, $e->getCode() ?: 500);

}
