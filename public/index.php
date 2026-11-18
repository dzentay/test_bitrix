<?php

declare(strict_types=1);

use Vendor\Catalog\Application\Bootstrap;
use Vendor\Catalog\Application\Env;
use Vendor\Catalog\Exceptions\ConflictException;
use Vendor\Catalog\Exceptions\ValidationException;
use Vendor\Catalog\Http\Request;
use Vendor\Catalog\Http\Response;
use Vendor\Catalog\Http\Router;

if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $file = __DIR__ . $path;

    if ($path !== '/' && is_file($file)) {
        return false;
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';

Bootstrap::init();

$container = Bootstrap::container();
$router = new Router();

$router->get('/', static function (): Response {
    $html = <<<'HTML'
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Каталог товаров</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<div id="app">Загрузка каталога...</div>
<script src="/assets/app.js"></script>
</body>
</html>
HTML;

    return Response::html($html);
});

$router->get('/api/v1/products', static function (Request $request) use ($container): Response {
    return $container->apiController()->list($request);
});

$router->post('/api/v1/products', static function (Request $request) use ($container): Response {
    return $container->apiController()->create($request);
});

$request = Request::fromGlobals();

try {
    $response = $router->dispatch($request);
} catch (ValidationException $e) {
    $response = Response::json([
        'message' => $e->getMessage(),
        'errors' => $e->errors,
    ], 422)->withCors();
} catch (ConflictException $e) {
    $response = Response::json([
        'message' => $e->getMessage(),
    ], 409)->withCors();
} catch (Throwable $e) {
    error_log((string) $e);

    $payload = ['message' => 'Internal server error'];

    if (Env::bool('APP_DEBUG', false)) {
        $payload['error'] = $e->getMessage();
    }

    $response = Response::json($payload, 500)->withCors();
}

$response->send();