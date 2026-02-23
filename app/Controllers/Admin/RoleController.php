<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Role;
use App\Models\Permission;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;

class RoleController extends BaseController
{
    protected array $middleware = ['auth'];

    private Role       $roles;
    private Permission $perms;

    public function __construct()
    {
        $this->roles = new Role();
        $this->perms = new Permission();
    }

    private function guard(): void
    {
        if (! Gate::hasRole('super_admin')) {
            Flash::error('Access denied. Super admin only.');
            redirect('/dashboard')->send();
            exit;
        }
    }

    // ── GET /admin/roles ──────────────────────────────────────────────────────

    public function index(): Response
    {
        $this->guard();

        return view('admin/roles/Index', [
            'title' => 'Role Management',
            'roles' => $this->roles->allWithCounts(),
        ]);
    }

    // ── GET /admin/roles/create ───────────────────────────────────────────────

    public function create(): Response
    {
        $this->guard();

        return view('admin/roles/create', [
            'title'      => 'Create Role',
            'allPerms'   => $this->perms->allGrouped(),
        ]);
    }

    // ── POST /admin/roles ─────────────────────────────────────────────────────

    public function store(): Response
    {
        $this->guard();

        $name    = trim(strtolower(str_replace(' ', '_', $this->request->input('role_name', ''))));
        $permIds = array_map('intval', (array) $this->request->input('permissions', []));

        if (empty($name)) {
            Flash::error('Role name is required.');
            return redirect('/admin/roles/create');
        }

        if ($this->roles->findByName($name)) {
            Flash::error("A role named \"{$name}\" already exists.");
            return redirect('/admin/roles/create');
        }

        $roleId = $this->roles->create([
            'role_name'  => $name,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (! empty($permIds)) {
            $this->roles->syncPermissions($roleId, $permIds);
        }

        Flash::success("Role \"{$name}\" created.");
        return redirect('/admin/roles');
    }

    // ── GET /admin/roles/{id} ─────────────────────────────────────────────────

    public function show(string $id): Response
    {
        $this->guard();

        $role = $this->roles->find((int) $id);
        if (! $role) {
            Flash::error('Role not found.');
            return redirect('/admin/roles');
        }

        return view('admin.roles.show', [
            'title'       => ucfirst(str_replace('_', ' ', $role['role_name'])) . ' Role',
            'role'        => $role,
            'allPerms'    => $this->perms->allGrouped(),
            'assignedIds' => $this->roles->permissionIds((int) $id),
            'roleUsers'   => $this->roles->users((int) $id),
            'isSystem'    => $this->roles->isSystemRole($role['role_name']),
        ]);
    }

    // ── POST /admin/roles/{id}/permissions ────────────────────────────────────

    public function syncPermissions(string $id): Response
    {
        $this->guard();

        $roleId  = (int) $id;
        $role    = $this->roles->find($roleId);

        if (! $role) {
            Flash::error('Role not found.');
            return redirect('/admin/roles');
        }

        // Super admin always keeps all permissions — cannot be stripped
        if ($role['role_name'] === 'super_admin') {
            Flash::error('Super admin permissions cannot be modified.');
            return redirect("/admin/roles/{$roleId}");
        }

        $permIds = array_map('intval', (array) $this->request->input('permissions', []));
        $this->roles->syncPermissions($roleId, $permIds);

        // Flush Gate permission cache so changes take effect immediately
        \Etus\Framework\Auth\Gate::flushCache();

        Flash::success('Permissions updated for role "' . $role['role_name'] . '".');
        return redirect("/admin/roles/{$roleId}");
    }

    // ── POST /admin/roles/{id}/delete ─────────────────────────────────────────

    public function destroy(string $id): Response
    {
        $this->guard();

        $roleId = (int) $id;
        $role   = $this->roles->find($roleId);

        if (! $role) {
            Flash::error('Role not found.');
            return redirect('/admin/roles');
        }

        if ($this->roles->isSystemRole($role['role_name'])) {
            Flash::error('System roles cannot be deleted.');
            return redirect('/admin/roles');
        }

        // Check if any users have this role
        $counts = $this->roles->allWithCounts();
        $entry  = array_filter($counts, fn($r) => (int)$r['id'] === $roleId);
        $entry  = reset($entry);

        if ($entry && (int)$entry['user_count'] > 0) {
            Flash::error('Cannot delete a role that still has users. Reassign users first.');
            return redirect('/admin/roles');
        }

        $this->roles->delete($roleId);

        Flash::success("Role \"{$role['role_name']}\" deleted.");
        return redirect('/admin/roles');
    }
}
