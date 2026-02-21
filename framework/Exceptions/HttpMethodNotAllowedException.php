<?php

namespace Etus\Framework\Exceptions;

class HttpMethodNotAllowedException extends \RuntimeException
{
    public function __construct(string $message = 'Method Not Allowed')
    {
        parent::__construct($message, 405);
    }
}
