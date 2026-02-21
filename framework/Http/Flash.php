<?php

namespace Etus\Framework\Http;

class Flash
{
    private const KEY = '_flash';

    /**
     * Store a flash message. It will be available once then destroyed.
     */
    public static function set(string $type, string $message): void
    {
        $_SESSION[self::KEY] = [
            'type' => $type,
            'msg'  => $message,
        ];
    }

    public static function success(string $message): void
    {
        static::set('success', $message);
    }

    public static function error(string $message): void
    {
        static::set('error', $message);
    }

    public static function info(string $message): void
    {
        static::set('info', $message);
    }

    /**
     * Read the flash message and immediately remove it from the session.
     *
     * @return array{type: string, msg: string}|null
     */
    public static function get(): ?array
    {
        if (! isset($_SESSION[self::KEY])) {
            return null;
        }

        $flash = $_SESSION[self::KEY];
        unset($_SESSION[self::KEY]);

        return $flash;
    }

    /**
     * Check whether a flash message is waiting without consuming it.
     */
    public static function has(): bool
    {
        return isset($_SESSION[self::KEY]);
    }
}
