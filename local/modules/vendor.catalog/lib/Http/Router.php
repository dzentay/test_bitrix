<?php

declare(strict_types=1);

namespace Vendor\Catalog\Http;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET ' . $path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST ' . $path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        if ($request->method === 'OPTIONS' && str_starts_with($request->path, '/api/')) {
            return Response::noContent()->withCors();
        }

        $key = $request->method . ' ' . $request->path;

        if (!isset($this->routes[$key])) {
            return Response::json([
                'message' => 'Not found',
                'path' => $request->path,
            ], 404)->withCors();
        }

        $handler = $this->routes[$key];
        $response = $handler($request);

        if (str_starts_with($request->path, '/api/')) {
            $response = $response->withCors();
        }

        return $response;
    }
}