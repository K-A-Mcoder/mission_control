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
     * Route: POST /logout — apply ['auth', 'csrf'] middleware in web.php.
     */
    public function __invoke(): Response
    {
        $userId = $this->authId() ?? null;

        // Clear remember-me cookie and token if one exists
        if ($userId && isset($_COOKIE['remember_me'])) {
            $this->user_model->clearRememberToken($userId);

            // Expire the cookie immediately
            setcookie('remember_me', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        }

        // Destroy session data
        $_SESSION = [];

        // Destroy the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 3600,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => 'Strict',
                ]
            );
        }

        session_destroy();

        // Start a fresh session just long enough to flash the message
        session_start();
        Flash::success('You have been logged out successfully.');

        return redirect('/login');
    }
}
