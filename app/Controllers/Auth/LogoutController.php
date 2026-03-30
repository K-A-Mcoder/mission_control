<?php

namespace App\Controllers\Auth;

use App\Controllers\Auth\AuthMasterController;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;

class LogoutController extends AuthMasterController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle logout.
     * Route: POST /logout — middleware: ['auth', 'csrf']
     */
    public function __invoke(): Response
    {
        // ── 1. Grab user ID BEFORE any session work ───────────────────────────
        $userId = $this->authId();

        // ── 2. Clear remember-me token ────────────────────────────────────────
        if (isset($_COOKIE['remember_me'])) {
            if ($userId) {
                $this->user_model->clearRememberToken($userId);
            }

            setcookie('remember_me', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);

            unset($_COOKIE['remember_me']);
        }

        // ── 3. Wipe in-memory session data ────────────────────────────────────
        $_SESSION = [];

        // ── 4. Expire the session cookie in the browser ───────────────────────
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Strict',
            ]);
        }

        // ── 5. Destroy the on-disk session data ───────────────────────────────
        session_destroy();

        // ── 6. Force a brand-new session ID BEFORE restarting ─────────────────
        // This is the critical step — do NOT use session_regenerate_id() here.
        // After session_destroy(), set a fresh ID manually, then start clean.
        session_id(bin2hex(random_bytes(16)));
        session_start();

        // ── 7. Write only what the new session needs ──────────────────────────
        $_SESSION['just_logged_out'] = true;
        Flash::success('You have been logged out successfully.');

        // ── 8. Commit and close NOW — before the framework's shutdown hooks ───
        // Prevents the framework response pipeline from writing anything back
        // over this clean session.
        session_write_close();

        // ── 9. Redirect ───────────────────────────────────────────────────────
        return redirect('/login');
    }
}