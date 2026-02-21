<?php

namespace Etus\Framework\Http;

class Response
{
    public function __construct(
        private readonly ?string $content = '',
        private readonly int $status = 200,
        private readonly array $headers = [],
    ) {}

    public function getStatus(): int
    {
        return $this->status;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
    }
}
