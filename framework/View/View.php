<?php

namespace Etus\Framework\View;

use Etus\Framework\Exceptions\ViewNotFoundException;

class View
{
    private static string $viewPath   = '';
    private static string $layoutPath = '';

    public static function setViewPath(string $path): void
    {
        static::$viewPath   = rtrim($path, '/');
        static::$layoutPath = rtrim($path, '/') . '/layout';
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = []): string
    {
        $file = static::$viewPath . '/' . str_replace('.', '/', $template) . '.php';

        if (! file_exists($file)) {
            throw new ViewNotFoundException("View [{$template}] not found at [{$file}].");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        $content = ob_get_clean();

        // If the template declared a $layout, wrap it
        if (! empty($layout)) {
            $layoutFile = static::$layoutPath . '/' . $layout . '.php';

            if (! file_exists($layoutFile)) {
                throw new ViewNotFoundException("Layout [{$layout}] not found at [{$layoutFile}].");
            }

            ob_start();
            require $layoutFile;
            $content = ob_get_clean();
        }

        return $content;
    }
}
