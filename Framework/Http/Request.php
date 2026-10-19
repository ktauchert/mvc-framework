<?php

declare(strict_types=1);

namespace Framework\Http;

class Request
{
    public function __construct(private string $method, private string $uri, private ?array $headers = null, private ?string $path = null)
    {
        $this->headers = $headers ?? getallheaders();
        $this->path = $path ?? parse_url($uri, PHP_URL_PATH);
    }

    public static function capture(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $headers = getallheaders();
        $path = parse_url($uri, PHP_URL_PATH);

        return new self($method, $uri, $headers, $path);
    }

    public function getMethod(): string
    {
        return $this->method;
    }
    public function getUri(): string
    {
        return $this->uri;
    }
    public function getHeaders(): array
    {
        return $this->headers;
    }
    public function getPath(): string
    {
        return is_string($this->path) ? rtrim($this->path, '/') : '';
    }
}
