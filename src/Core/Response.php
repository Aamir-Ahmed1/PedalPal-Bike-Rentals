<?php

declare(strict_types=1);

namespace PedalPal\Core;

final class Response
{
    private static array $corsHeaders = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type',
    ];

    public static function json(mixed $data, int $status = 200, array $extraHeaders = []): never
    {
        http_response_code($status);
        self::sendHeaders($extraHeaders);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 400, mixed $data = null): never
    {
        self::json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private static function sendHeaders(array $extraHeaders): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        foreach (self::$corsHeaders as $key => $value) {
            header("$key: $value");
        }

        foreach ($extraHeaders as $key => $value) {
            header("$key: $value");
        }
    }
}
