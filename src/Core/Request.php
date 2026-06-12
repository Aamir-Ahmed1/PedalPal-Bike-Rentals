<?php

declare(strict_types=1);

namespace PedalPal\Core;

final class Request
{
    public readonly string $method;
    public readonly string $uri;
    public readonly array $query;
    public readonly array $body;
    public readonly array $headers;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->query = $_GET;
        $this->headers = $this->parseHeaders();

        $rawBody = file_get_contents('php://input');
        $this->body = $rawBody ? (json_decode($rawBody, true) ?? []) : [];
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }
}
