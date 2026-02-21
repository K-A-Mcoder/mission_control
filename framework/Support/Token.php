<?php

namespace Etus\Framework\Support;

class Token
{
    /**
     * Generate a cryptographically secure raw token.
     * Returns a URL-safe hex string.
     *
     * @param int $bytes Number of random bytes (32 = 64-char hex = 256-bit entropy)
     */
    public static function generate(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Hash a raw token for safe storage in the database.
     * Never store the raw token — only store this.
     */
    public static function hash(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }

    /**
     * Verify a raw token against a stored hash using
     * a constant-time comparison to prevent timing attacks.
     */
    public static function verify(string $rawToken, string $storedHash): bool
    {
        return hash_equals($storedHash, static::hash($rawToken));
    }

    /**
     * Generate a token and return both the raw (for the URL/email)
     * and the hashed (for the database) versions together.
     *
     * @return array{raw: string, hashed: string}
     */
    public static function make(int $bytes = 32): array
    {
        $raw = static::generate($bytes);

        return [
            'raw'    => $raw,
            'hashed' => static::hash($raw),
        ];
    }
}
