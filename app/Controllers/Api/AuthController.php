<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\User;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;
use Etus\Framework\Mail\Mailer;
use Etus\Framework\Support\Token;

class AuthController extends BaseController
{

    private const ACTIVATION_HOURS = 24;

    // Rate limit: max resend attempts per hour per IP
    private const MAX_RESENDS_PER_HOUR = 3;

    private User   $user;
    private Mailer $mailer;

    public function __construct()
    {
        $this->user   = new User();
        $this->mailer = new Mailer();
    }

    // ── GET /auth/activate?token={rawToken} ───────────────────────────────────

    /**
     * Validate the token, activate the account, redirect to login.
     *
     * Security properties:
     *  - Raw token never touches the DB; only its SHA-256 hash is stored.
     *  - hash_equals() in Token::verify() prevents timing attacks.
     *  - Token is single-use: cleared from DB immediately on success.
     *  - Expired tokens are rejected at the DB query level.
     *  - Already-active accounts are silently ignored (idempotent).
     */
    public function activate(): Response
    {
        $rawToken = trim($this->request->query('token', ''));

        // ── Structural check ──────────────────────────────────────────────────
        // A valid raw token is 64 hex characters (32 bytes × 2).
        if (! $this->isValidTokenFormat($rawToken)) {
            Flash::error('This activation link is invalid.');
            return redirect('/login');
        }

        $hashedToken = Token::hash($rawToken);

        // ── Lookup — DB query already filters expired and non-pending rows ─────
        $user = $this->user->findByActivationToken($hashedToken);

        if (! $user) {
            // Intentionally vague — don't reveal whether the email is known
            // or whether the token expired vs. never existed.
            Flash::error('This activation link is invalid or has expired. Please request a new one.');
            return redirect('/auth/resend');
        }

        // ── Activate ──────────────────────────────────────────────────────────
        // Single UPDATE: sets status = 'active', clears token and expiry.
        // If the same link is clicked twice, findByActivationToken returns null
        // on the second hit because status is no longer 'pending' — safe.
        try {
            $this->user->activate($user['user_id']);
        } catch (\Throwable) {
            Flash::error('Something went wrong. Please try again or contact support.');
            return redirect('/login');
        }

        Flash::success('Your account is active. You can now log in.');

        return redirect('/login');
    }

    // ── GET /auth/resend ──────────────────────────────────────────────────────

    /**
     * Show the resend-activation form.
     */
    public function showResend(): Response
    {
        return view('auth.resend', [
            'title' => 'Resend Activation Link',
        ]);
    }

    // ── POST /auth/resend ─────────────────────────────────────────────────────

    /**
     * Issue a fresh activation token and email it to the user.
     *
     * Security properties:
     *  - Response is identical whether the email exists or not (anti-enumeration).
     *  - Previous token is overwritten, invalidating old links immediately.
     *  - IP-based rate limiting prevents abuse.
     */
    public function resend(): Response
    {
        $email = filter_var(
            trim($this->request->input('email', '')),
            FILTER_SANITIZE_EMAIL,
        );

        // ── Rate limit ────────────────────────────────────────────────────────
        $ip = $this->request->server('REMOTE_ADDR', '0.0.0.0');

        if ($this->isRateLimited($ip)) {
            Flash::error('Too many requests. Please wait a while before trying again.');
            return redirect('/auth/resend');
        }

        $this->recordResendAttempt($ip);

        // ── Always show the same response (prevents user enumeration) ─────────
        $successMsg = 'If that email belongs to a pending account, a new activation link has been sent.';

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Invalid format — still show the generic message
            Flash::info($successMsg);
            return redirect('/auth/resend');
        }

        $user = $this->user->findPendingByEmail($email);

        if ($user) {
            try {
                // Overwrite old token — old activation links are now dead
                $token  = Token::make();
                $expiry = date('Y-m-d H:i:s', strtotime('+' . self::ACTIVATION_HOURS . ' hours'));

                $this->user->storeActivationToken((int) $user['user_id'], $token['hashed'], $expiry);

                $this->sendActivationEmail($user['email'], $user['full_name'], $token['raw']);
            } catch (\Throwable) {
                // Log internally but don't surface the error to the user
                error_log("Activation resend failed for {$email}");
            }
        }

        Flash::info($successMsg);

        return redirect('/auth/resend');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Validate that the token string is 64 lowercase hex characters.
     * Rejects empty strings, truncated tokens, and injection attempts
     * before we ever touch the database.
     */
    private function isValidTokenFormat(string $token): bool
    {
        return (bool) preg_match('/^[a-f0-9]{64}$/', $token);
    }

    private function sendActivationEmail(string $email, string $name, string $rawToken): void
    {
        $baseUrl       = rtrim(env('APP_URL', 'http://localhost:8500'), '/');
        $activationUrl = "{$baseUrl}/auth/activate?token={$rawToken}";

        $this->mailer->sendTemplate(
            to: $email,
            subject: 'Activate your ' . env('APP_NAME', 'App') . ' account',
            template: 'activation',
            data: [
                'appName'       => env('APP_NAME', 'App'),
                'userName'      => $name,
                'activationUrl' => $activationUrl,
                'expiryHours'   => self::ACTIVATION_HOURS,
            ],
        );
    }

    // ── Rate limiting (session-based per IP) ──────────────────────────────────

    private function isRateLimited(string $ip): bool
    {
        $key      = '_resend_attempts_' . md5($ip);
        $attempts = $_SESSION[$key] ?? [];

        // Purge attempts older than 1 hour
        $cutoff  = time() - 3600;
        $recent  = array_filter($attempts, fn(int $t) => $t > $cutoff);

        $_SESSION[$key] = array_values($recent);

        return count($recent) >= self::MAX_RESENDS_PER_HOUR;
    }

    private function recordResendAttempt(string $ip): void
    {
        $key               = '_resend_attempts_' . md5($ip);
        $_SESSION[$key][]  = time();
    }
}
