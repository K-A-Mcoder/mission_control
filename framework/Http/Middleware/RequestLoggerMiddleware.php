<?php

namespace Etus\Framework\Http\Middleware;

use Etus\Framework\Http\Request;
use Etus\Framework\Http\Response;

class RequestLoggerMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $start = microtime(true);

        $response = $next();

        $duration = round((microtime(true) - $start) * 1000, 2);

        $this->write($request, $response, $duration);

        return $response;
    }

    private function write(Request $request, Response $response, float $durationMs): void
    {
        $logDir  = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : sys_get_temp_dir();
        $logFile = $logDir . '/requests.log';

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, recursive: true);
        }

        $line = implode(' ', [
            '[' . date('Y-m-d H:i:s') . ']',
            $request->getMethod(),
            $request->getUri(),
            '→',
            $response->getStatus(),
            $durationMs . 'ms',
        ]);

        file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
