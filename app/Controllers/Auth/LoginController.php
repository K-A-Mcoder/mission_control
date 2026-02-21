<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\Role;
use App\Models\User;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Auth\Auth;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;

class LoginController extends BaseController
{
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_SECONDS = 15 * 60;

    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * Show the login form.
     */
    public function index(): Response
    {
        return view('auth.login', [
            'title' => 'Login Into Your Account',
        ]);
    }

    /**
     * Handle the login form submission.
     * Route: POST /login — apply ['guest', 'csrf'] middleware in web.php.
     */
    public function doLogin(): Response
    {
        $email    = filter_var(trim($this->request->input('login-email', '')), FILTER_SANITIZE_EMAIL);
        $password = trim($this->request->input('login-password', ''));
        $remember = (bool) $this->request->input('remember_me');
        $ip       = $this->request->server('REMOTE_ADDR', 'UNKNOWN');

        if (empty($email) || empty($password)) {
            return $this->failWith('Please enter both email and password.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->failWith('Invalid email format.');
        }

        $user = $this->user->findByEmailWithRole($email);

        if (! $user) {
            return $this->failWith('No account found with that email.');
        }

        if ($this->isLockedOut($user)) {
            return $this->failWith('Too many failed attempts. Please try again after 15 minutes.');
        }

        if ($user['status'] !== 'active') {
            return $this->failWith("Your account is currently {$user['status']}. Contact support.");
        }

        if (! password_verify($password, $user['password'])) {
            return $this->handleFailedAttempt($user, $ip);
        }

        return $this->handleSuccessfulLogin($user, $remember, $ip);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function isLockedOut(array $user): bool
    {
        return $user['failed_attempts'] >= self::MAX_ATTEMPTS
            && ! empty($user['last_failed_at'])
            && strtotime($user['last_failed_at']) > (time() - self::LOCKOUT_SECONDS);
    }

    private function handleSuccessfulLogin(array $user, bool $remember, string $ip): Response
    {
        $this->user->resetFailedAttempts($user['user_id']);

        session_regenerate_id(true);

        $_SESSION['user_id']      = $user['user_id'];
        $_SESSION['user_name']    = $user['full_name'];
        $_SESSION['user_email']   = $user['email'];
        $_SESSION['role']         = $user['role_name'];
        $_SESSION['is_logged_in'] = true;

        // Pre-load this role's permissions into Gate so every
        // can() check during this request hits the cache, not the DB.
        $role        = (new Role)->findByName($user['role_name']);
        $permissions = $role ? (new Role)->permissionNames((int) $role['id']) : [];
        Gate::cacheRolePermissions($user['role_name'], $permissions);

        if ($remember) {
            $this->setRememberMeCookie($user['user_id']);
        }

        Flash::success('Welcome back, ' . htmlspecialchars($user['full_name']) . '!');

        return redirect('/dashboard');
    }

    private function handleFailedAttempt(array $user, string $ip): Response
    {
        $this->user->incrementFailedAttempts($user['user_id']);
        $this->user->recordLoginAttempt($user['email'], $ip);

        $newCount = (int) $user['failed_attempts'] + 1;

        if ($newCount >= self::MAX_ATTEMPTS) {
            $this->user->suspend($user['user_id']);
            return $this->failWith('Account temporarily locked. Try again later.');
        }

        $remaining = self::MAX_ATTEMPTS - $newCount;

        return $this->failWith("Incorrect password. {$remaining} attempt(s) remaining.");
    }

    private function setRememberMeCookie(int $userId): void
    {
        $token       = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiry      = date('Y-m-d H:i:s', strtotime('+30 days'));

        $this->user->storeRememberToken($userId, $hashedToken, $expiry);

        setcookie('remember_me', $userId . '|' . $token, [
            'expires'  => time() + (30 * 24 * 60 * 60),
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    /**
     * Store a flash error and redirect back to the login page.
     */
    private function failWith(string $message): Response
    {
        Flash::error($message);

        return redirect('/login');
    }
}
