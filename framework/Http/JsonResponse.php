<?php

namespace Etus\Framework\Http;

class JsonResponse extends Response
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data, int $status = 200)
    {
        parent::__construct(
            content: json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            status: $status,
            headers: ['Content-Type' => 'application/json'],
        );
    }

    // ── Static factories ──────────────────────────────────────────────────────

    public static function success(string $message, array $data = [], int $status = 200): static
    {
        return new static(['success' => true, 'message' => $message, ...$data], $status);
    }

    public static function error(string $message, int $status = 400): static
    {
        return new static(['success' => false, 'error' => $message], $status);
    }

    public static function forbidden(string $message = 'Permission denied'): static
    {
        return static::error($message, 403);
    }

    public static function notFound(string $message = 'Not found'): static
    {
        return static::error($message, 404);
    }

    public static function serverError(string $message = 'Something went wrong'): static
    {
        return static::error($message, 500);
    }
}
