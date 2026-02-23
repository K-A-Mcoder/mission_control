<?php

namespace App\Controllers\Auth;

use App\Controllers\Auth\AuthMasterController;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;
use Etus\Framework\Mail\Mailer;
use Etus\Framework\Support\Token;

class RegisterController extends AuthMasterController
{
    private const ACTIVATION_HOURS = 24;

    private Mailer $mailer;

    public function __construct()
    {
        parent::__construct();
        $this->mailer = new Mailer();
    }

    // ── GET /register ─────────────────────────────────────────────────────────

    public function index(): Response
    {
        return view('auth.register', [
            'title' => 'Create an Account',
            'description' => 'Join ' . env('APP_NAME', 'our app') . ' and start managing your tasks efficiently.',
        ]);
    }

    // ── POST /register ────────────────────────────────────────────────────────

    public function store(): Response
    {
        $fullName  = trim($this->request->input('full_name', ''));
        $email     = filter_var(trim($this->request->input('email', '')), FILTER_SANITIZE_EMAIL);
        $password  = $this->request->input('password', '');
        $password2 = $this->request->input('password_confirmation', '');


        // ── Validation ────────────────────────────────────────────────────────
        $errors = [];

        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if (! preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (! preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if ($password !== $password2) {
            $errors[] = 'Passwords do not match.';
        }

        if ($errors) {
            Flash::error(implode(' ', $errors));
            return redirect('/register');
        }

        // ── Duplicate email check ─────────────────────────────────────────────
        // Use the same vague message whether the email exists or not
        // to avoid user enumeration.
        if ($this->user_model->findByEmail($email)) {
            Flash::error('If this email is not already registered, you will receive a confirmation link shortly.');
            return redirect('/register');
        }

        // ── Create user ───────────────────────────────────────────────────────
        try {
            $uuid = uuid();
            $this->user_model->create([
                'user_id'    => $uuid,
                'full_name'  => $fullName,
                'email'      => $email,
                'password'   => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                'status'     => 'inactive',   // must activate before login
                'role_id'    => $this->defaultRoleId(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // ── Generate and store activation token ───────────────────────────
            $token  = Token::make();
            $expiry = date('Y-m-d H:i:s', strtotime('+' . self::ACTIVATION_HOURS . ' hours'));
            $this->user_model->storeActivationToken($uuid, $token['hashed'], $expiry);

            // ── Send activation email ─────────────────────────────────────────
            $this->sendActivationEmail($email, $fullName, $token['raw']);

            Flash::success('Account created! Please check your email to activate your account. Activation token: ' . $token['raw']);

            return redirect('/login');
        } catch (\Throwable $e) {
            Flash::error('Something went wrong during registration. Please try again.' . $e->getMessage());
            log_error("Registration failed for {$email}: " . $e->getMessage());
            return redirect('/register');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function sendActivationEmail(string $email, string $name, string $rawToken)
    {
        $baseUrl       = rtrim(env('APP_URL', 'http://localhost:8500'), '/');
        $activationUrl = "{$baseUrl}/auth/activate?token={$rawToken}";

        $data = [
            'appName'       => env('APP_NAME', 'App'),
            'userName'      => $name,
            'activationUrl' => $activationUrl,
            'expiryHours'   => self::ACTIVATION_HOURS,
        ];
        $this->mailer->sendTemplate(
            to: $email,
            subject: 'Activate your ' . env('APP_NAME', 'App') . ' account',
            template: 'activation',
            data: $data,
        );
    }

    private function defaultRoleId(): int
    {
        // Fetch the 'user' role ID from the database
        $db   = \Etus\Framework\Database\Connection::getInstance();

        $role = ($this->user_model->isFirstUser())
            ? $db->selectOne("SELECT id FROM roles WHERE role_name = 'super_admin' LIMIT 1")
            : $db->selectOne("SELECT id FROM roles WHERE role_name = 'user' LIMIT 1");
        if (! $role) {
            throw new \RuntimeException("Default role not found. Run the roles migration.");
        }

        return (int) $role['id'];
    }
}
