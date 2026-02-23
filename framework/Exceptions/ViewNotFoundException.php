<?php

namespace Etus\Framework\Exceptions;

class ViewNotFoundException extends \RuntimeException
{

    public function __construct(string $message = 'View File Not Found')
    {
        parent::__construct($message, 404);
    }
}
