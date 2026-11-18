<?php

declare(strict_types=1);

namespace Vendor\Catalog\Http;

final class Request
{
    private function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $body,
        public readonly array $headers,
        public readonly string $rawBody,
        public readonly bool $jsonError
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $rawHeaders = function_exists('getallheaders') ? getallheaders() : false;
        $headers = is_array($rawHeaders) ? $rawHeaders : self::headersFromServer();
        $headers = array_change_key_case($headers, CASE_LOWER);

        $rawBody = file_get_contents('php://input') ?: '';
        $body = [];
        $jsonError = false;

        $contentType = (string) ($headers['content-type'] ?? '');

        if ($rawBody !== '' && str_contains($contentType, 'application/json')) {
            $decoded = json_decode($rawBody, true);

            if (is_array($decoded)) {
                $body = $decoded;
            } else {
                $jsonError = json_last_error() !== JSON_ERROR_NONE;
            }
        } elseif ($rawBody !== '') {
            $parsed = [];
            parse_str($rawBody, $parsed);
            $body = $parsed;
        } else {
            $body = $_POST;
        }

        return new self(
            $method,
            $path,
            $_GET,
            $body,
            $headers,
            $rawBody,
            $jsonError
        );
    }

    private static function headersFromServer(): array
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $header = strtolower(str_replace('_', '-', substr($name, 5)));
                $headers[$header] = $value;
            } elseif (in_array($name, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $header = strtolower(str_replace('_', '-', $name));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }
}