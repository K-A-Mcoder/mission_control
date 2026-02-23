<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\User;
use App\Models\Role;
use App\Models\Notification;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;

class UserController extends BaseController
{
    protected array $middleware = ['auth'];

    private User $users;
    private Role $roles;

    public function __construct()
    {
        $this->users = new User();
        $this->roles = new Role();
    }

    // ── Guard ─────────────────────────────────────────────────────────────────

    private function guard(): void
    {
        if (! Gate::hasRole('super_admin')) {
            Flash::error('Access denied. Super admin only.');
            redirect('/dashboard')->send();
            exit;
        }
    }

    // ── GET /admin/users ──────────────────────────────────────────────────────

    public function index(): Response
    {
        $this->guard();

        $users = $this->users->allWithRoles();
        $roles = $this->roles->get();

        // Stats
        $total     = count($users);
        $active    = count(array_filter($users, fn($u) => $u['status'] === 'active'));
        $pending   = count(array_filter($users, fn($u) => $u['status'] === 'pending'));
        $suspended = count(array_filter($users, fn($u) => $u['status'] === 'suspended'));

        return view('admin/users/Index', [
            'title'     => 'User Management',
            'users'     => $users,
            'roles'     => $roles,
            'stats'     => compact('total', 'active', 'pending', 'suspended'),
        ]);
    }

    // ── GET /admin/users/create ───────────────────────────────────────────────

    public function create(): Response
    {
        $this->guard();

        return view('admin/users/create', [
            'title' => 'Create User',
            'roles' => $this->roles->get(),
        ]);
    }

    // ── POST /admin/users ─────────────────────────────────────────────────────

    public function store(): Response
    {
        $this->guard();

        $name     = trim($this->request->input('full_name', ''));
        $email    = trim($this->request->input('email', ''));
        $password = $this->request->input('password', '');
        $roleId   = (int) $this->request->input('role_id', 0);
        $status   = $this->request->input('status', 'active');

        // Validate
        if (empty($name) || empty($email) || empty($password) || ! $roleId) {
            Flash::error('All fields are required.');
            return redirect('/admin/users/create');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Invalid email address.');
            return redirect('/admin/users/create');
        }

        if (strlen($password) < 8) {
            Flash::error('Password must be at least 8 characters.');
            return redirect('/admin/users/create');
        }

        if ($this->users->findByEmail($email)) {
            Flash::error('A user with that email already exists.');
            return redirect('/admin/users/create');
        }

        $userId = $this->users->create([
            'full_name'  => $name,
            'email'      => $email,
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'role_id'    => $roleId,
            'status'     => in_array($status, User::STATUSES, true) ? $status : 'active',
            'created_by' => $this->authId(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Flash::success("User \"{$name}\" created successfully.");
        return redirect('/admin/users');
    }

    // ── GET /admin/users/{id} ─────────────────────────────────────────────────

    public function show(string $id): Response
    {
        $this->guard();

        $user = $this->users->findWithRole((int) $id);
        if (! $user) {
            Flash::error('User not found.');
            return redirect('/admin/users');
        }

        return view('admin/users/show', [
            'title'      => htmlspecialchars($user['full_name']),
            'user'       => $user,
            'userTeams'  => $this->users->teams((int) $id),
            'recentTasks' => $this->users->recentTasks((int) $id),
            'stats'      => $this->users->stats((int) $id),
            'activity'   => $this->users->activityLog((int) $id),
            'roles'      => $this->roles->get(),
        ]);
    }

    // ── GET /admin/users/{id}/edit ────────────────────────────────────────────

    public function edit(string $id): Response
    {
        $this->guard();

        $user = $this->users->findWithRole((int) $id);
        if (! $user) {
            Flash::error('User not found.');
            return redirect('/admin/users');
        }

        return view('admin/users/edit', [
            'title' => 'Edit ' . htmlspecialchars($user['full_name']),
            'user'  => $user,
            'roles' => $this->roles->get(),
        ]);
    }

    // ── POST /admin/users/{id}/update ────────────────────────────────────────

    public function update(string $id): Response
    {
        $this->guard();

        $userId = (int) $id;
        $user   = $this->users->findWithRole($userId);

        if (! $user) {
            Flash::error('User not found.');
            return redirect('/admin/users');
        }

        $name   = trim($this->request->input('full_name', ''));
        $email  = trim($this->request->input('email', ''));
        $roleId = (int) $this->request->input('role_id', $user['role_id']);
        $status = $this->request->input('status', $user['status']);

        if (empty($name) || empty($email)) {
            Flash::error('Name and email are required.');
            return redirect("/admin/users/{$userId}/edit");
        }

        // Prevent changing own role or status if super_admin
        if ($userId === $this->authId() && $status !== 'active') {
            Flash::error('You cannot suspend or deactivate your own account.');
            return redirect("/admin/users/{$userId}/edit");
        }

        $this->users->updateProfile($userId, ['full_name' => $name, 'email' => $email]);
        $this->users->changeRole($userId, $roleId);

        if (in_array($status, User::STATUSES, true)) {
            $this->users->setStatus($userId, $status);
        }

        // Optional password reset
        $newPass = $this->request->input('new_password', '');
        if (! empty($newPass)) {
            if (strlen($newPass) < 8) {
                Flash::error('Password must be at least 8 characters.');
                return redirect("/admin/users/{$userId}/edit");
            }
            $this->users->resetPassword($userId, password_hash($newPass, PASSWORD_BCRYPT));
        }

        Flash::success('User updated.');
        return redirect("/admin/users/{$userId}");
    }

    // ── POST /admin/users/{id}/status ────────────────────────────────────────

    public function setStatus(string $id): Response
    {
        $this->guard();

        $userId = (int) $id;
        $status = $this->request->input('status', '');

        if ($userId === $this->authId()) {
            Flash::error('You cannot change your own status.');
            return redirect('/admin/users');
        }

        if (! in_array($status, User::STATUSES, true)) {
            Flash::error('Invalid status.');
            return redirect('/admin/users');
        }

        $this->users->setStatus($userId, $status);

        Flash::success('User status updated to ' . $status . '.');
        return redirect('/admin/users');
    }

    // ── POST /admin/users/{id}/delete ────────────────────────────────────────

    public function destroy(string $id): Response
    {
        $this->guard();

        $userId = (int) $id;

        if ($userId === $this->authId()) {
            Flash::error('You cannot delete your own account.');
            return redirect('/admin/users');
        }

        $this->users->softDelete($userId);

        Flash::success('User deactivated.');
        return redirect('/admin/users');
    }
}
