<?php

declare(strict_types=1);

namespace Vendor\Catalog\Http;

final class Response
{
    private array $headers;

    public function __construct(
        private readonly string $body = '',
        private readonly int $status = 200,
        array $headers = []
    ) {
        $this->headers = $headers;
    }

    public static function json(mixed $payload, int $status = 200): self
    {
        $body = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_PRESERVE_ZERO_FRACTION
            | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($body === false) {
            $body = '{"message":"Invalid JSON response"}';
        }

        return new self($body, $status, [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    public static function html(string $html, int $status = 200): self
    {
        return new self($html, $status, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    public static function noContent(int $status = 204): self
    {
        return new self('', $status);
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    public function withCors(): self
    {
        return $this
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        echo $this->body;
    }
}