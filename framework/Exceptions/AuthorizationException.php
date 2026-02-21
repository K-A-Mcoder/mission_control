<?php

namespace Etus\Framework\Exceptions;

class AuthorizationException extends \RuntimeException
{
    public function __construct(string $message = 'This action is unauthorized.', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
