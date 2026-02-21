<?php

namespace Etus\Framework\Exceptions;

class HttpNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Not Found')
    {
        parent::__construct($message, 404);
    }
}
