<?php

namespace Etus\Framework\Mail;

class Mailer
{
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $this->fromAddress = env('MAIL_FROM_ADDRESS', 'no-reply@example.com');
        $this->fromName    = env('MAIL_FROM_NAME', env('APP_NAME', 'App'));
    }

    /**
     * Send a plain-text or HTML email.
     *
     * @param string|array<int,string> $to      Single address or list of addresses
     * @param string                   $subject Email subject line
     * @param string                   $body    HTML body (will also generate text fallback)
     */
    public function send(string|array $to, string $subject, string $body): bool
    {
        $recipients = is_array($to) ? implode(', ', $to) : $to;

        $headers   = [];
        $headers[] = "From: {$this->fromName} <{$this->fromAddress}>";
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'X-Mailer: PHP/' . PHP_VERSION;

        return mail(
            $recipients,
            '=?UTF-8?B?' . base64_encode($subject) . '?=',
            $body,
            implode("\r\n", $headers),
        );
    }

    /**
     * Send a named template from app/views/emails/.
     *
     * @param array<string, mixed> $data Variables passed into the template
     */
    public function sendTemplate(
        string|array $to,
        string $subject,
        string $template,
        array $data = [],
    ): bool {
        $path = VIEWS_PATH . '/emails/' . str_replace('.', '/', $template) . '.php';

        if (! file_exists($path)) {
            throw new \RuntimeException("Email template not found: [{$path}]");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        $body = ob_get_clean();

        return $this->send($to, $subject, (string) $body);
    }
}
