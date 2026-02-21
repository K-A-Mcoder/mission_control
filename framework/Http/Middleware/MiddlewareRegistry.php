<?php

namespace Etus\Framework\Http\Middleware;

class MiddlewareRegistry
{
    /** @var array<string, class-string<MiddlewareInterface>> */
    private static array $map = [];

    /**
     * Register a middleware alias.
     *
     * @param class-string<MiddlewareInterface> $class
     */
    public static function register(string $alias, string $class): void
    {
        static::$map[$alias] = $class;
    }

    /**
     * Resolve an alias or class name into a MiddlewareInterface instance.
     */
    public static function resolve(string $aliasOrClass): MiddlewareInterface
    {
        $class = static::$map[$aliasOrClass] ?? $aliasOrClass;

        if (! class_exists($class)) {
            throw new \InvalidArgumentException("Middleware [{$aliasOrClass}] is not registered.");
        }

        return new $class;
    }

    /**
     * Resolve multiple aliases/class names at once.
     *
     * @param  array<int, string>              $aliases
     * @return array<int, MiddlewareInterface>
     */
    public static function resolveMany(array $aliases): array
    {
        return array_map(fn($alias) => static::resolve($alias), $aliases);
    }
}
