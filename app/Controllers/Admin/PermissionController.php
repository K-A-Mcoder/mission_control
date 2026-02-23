<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Permission;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;

class PermissionController extends BaseController
{
    protected array $middleware = ['auth'];

    private Permission $perms;

    public function __construct()
    {
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

    // ── GET /admin/permissions ────────────────────────────────────────────────

    public function index(): Response
    {
        $this->guard();

        return view('admin/permissions/Index', [
            'title'   => 'Permission Management',
            'grouped' => $this->perms->allGrouped(),
            'all'     => $this->perms->allWithRoleCount(),
            'groups'  => Permission::GROUPS,
        ]);
    }

    // ── POST /admin/permissions ───────────────────────────────────────────────

    public function store(): Response
    {
        $this->guard();

        $name  = trim(strtolower(str_replace(' ', '-', $this->request->input('name', ''))));
        $label = trim($this->request->input('label', ''));
        $group = trim($this->request->input('group', 'general'));

        if (empty($name) || empty($label)) {
            Flash::error('Name and label are required.');
            return redirect('/admin/permissions');
        }

        if ($this->perms->nameExists($name)) {
            Flash::error("Permission \"{$name}\" already exists.");
            return redirect('/admin/permissions');
        }

        $this->perms->create([
            'name'       => $name,
            'label'      => $label,
            '`group`'      => $group,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Flash::success("Permission \"{$name}\" created.");
        return redirect('/admin/permissions');
    }

    // ── POST /admin/permissions/{id}/update ───────────────────────────────────

    public function update(string $id): Response
    {
        $this->guard();

        $permId = (int) $id;
        $perm   = $this->perms->find($permId);

        if (! $perm) {
            Flash::error('Permission not found.');
            return redirect('/admin/permissions');
        }

        $label = trim($this->request->input('label', ''));
        $group = trim($this->request->input('group', $perm['group']));

        if (empty($label)) {
            Flash::error('Label is required.');
            return redirect('/admin/permissions');
        }

        $this->perms->update($permId, [
            'label'      => $label,
            '`group`'      => $group,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Gate::flushCache();
        Flash::success('Permission updated.');
        return redirect('/admin/permissions');
    }

    // ── POST /admin/permissions/{id}/delete ───────────────────────────────────

    public function destroy(string $id): Response
    {
        $this->guard();

        $permId = (int) $id;
        $perm   = $this->perms->find($permId);

        if (! $perm) {
            Flash::error('Permission not found.');
            return redirect('/admin/permissions');
        }

        $this->perms->delete($permId);
        Gate::flushCache();

        Flash::success("Permission \"{$perm['name']}\" deleted.");
        return redirect('/admin/permissions');
    }
}
