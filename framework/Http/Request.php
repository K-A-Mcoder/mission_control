<?php

namespace Etus\Framework\Http;

class Request
{
    private static ?self $instance = null;

    private function __construct(
        private readonly array $server,
        private readonly array $get,
        private readonly array $post,
        private readonly array $files,
        private readonly array $cookie,
        private readonly array $env,
    ) {}

    public static function capture(): static
    {
        if (null === static::$instance) {
            static::$instance = new static(
                $_SERVER,
                $_GET,
                $_POST,
                $_FILES,
                $_COOKIE,
                $_ENV,
            );
        }

        return static::$instance;
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getUri(): string
    {
        return $this->server['REQUEST_URI'];
    }

    public function query(string $name, mixed $default = null): mixed
    {
        return $this->get[$name] ?? $default;
    }

    public function input(string $name, mixed $default = null): mixed
    {
        return $this->post[$name] ?? $default;
    }

    public function file(string $name): mixed
    {
        return $this->files[$name] ?? null;
    }

    public function cookie(string $name, mixed $default = null): mixed
    {
        return $this->cookie[$name] ?? $default;
    }

    public function env(string $name, mixed $default = null): mixed
    {
        return $this->env[$name] ?? $default;
    }

    public function server(string $name, mixed $default = null): mixed
    {
        return $this->server[$name] ?? $default;
    }
}
