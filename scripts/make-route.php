<?php

define('BASE_ROUTES_PATH', __DIR__ . '/../src/app');

$routeArg = $argv[1] ?? null;
$routePath = strtolower(trim($routeArg, '/'));

if($routePath === '' || $routePath === '.') {
    $dir = BASE_ROUTES_PATH;
    $namespace = "App\\Controllers";
} else {
    $dir = BASE_ROUTES_PATH . '/' . $routePath;
    $namespaceParts = array_map('ucfirst', explode('/', $routePath));
    $namespace = "App\\Controllers\\" . implode("\\", $namespaceParts);
}

$filePath = $dir . '/IndexClass.php';

if(!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$code = "<?php\n\nnamespace {$namespace};\n\nclass IndexClass {\n    public function run() {\n        echo \"<h1>Route: /{$routePath}</h1>\";\n    }\n}\n";

file_put_contents($filePath, $code);

echo "Erfolgreich erstellt: src/app/{$routePath}/IndexClass.php\n";
