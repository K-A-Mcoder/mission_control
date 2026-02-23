<?php $layout = 'app'; ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Role Management</h2>
        <p class="text-sm text-muted mt-0.5">Define roles and control which permissions each one holds</p>
    </div>
    <a href="/admin/roles/create" class="btn btn-primary text-sm px-4 py-2">
        <i class="fa-solid fa-plus mr-1.5 text-xs"></i> New Role
    </a>
</div>

<?= partial('partials.flash') ?>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <?php foreach ($roles as $role):
        $isSystem = in_array($role['role_name'], ['super_admin', 'admin', 'manager', 'user']);
        $roleColor = match ($role['role_name']) {
            'super_admin' => 'border-danger/30 bg-danger-light',
            'admin'       => 'border-primary/30 bg-primary-light',
            'manager'     => 'border-warning/30 bg-warning-light',
            default       => 'border-b-color bg-white dark:bg-dark-card',
        };
        $iconColor = match ($role['role_name']) {
            'super_admin' => 'text-danger bg-danger-light',
            'admin'       => 'text-primary bg-primary-light',
            'manager'     => 'text-warning bg-warning-light',
            default       => 'text-muted bg-gray-100',
        };
    ?>
        <div class="border rounded-xl p-5 <?= $roleColor ?> flex flex-col gap-4">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 <?= $iconColor ?>">
                        <i class="fa-solid fa-shield-halved text-sm"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-dark capitalize">
                            <?= htmlspecialchars(str_replace('_', ' ', $role['role_name'])) ?>
                        </p>
                        <?php if ($isSystem): ?>
                            <span class="text-[9px] uppercase tracking-wide font-bold text-muted">
                                System Role
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="/admin/roles/<?= $role['id'] ?>"
                    class="text-xs text-primary hover:underline whitespace-nowrap">
                    Manage →
                </a>
            </div>

            <div class="flex gap-6 text-sm">
                <div class="text-center">
                    <p class="text-xl font-bold text-dark"><?= (int)$role['user_count'] ?></p>
                    <p class="text-xs text-muted">Users</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-dark"><?= (int)$role['permission_count'] ?></p>
                    <p class="text-xs text-muted">Permissions</p>
                </div>
            </div>

            <?php if (! $isSystem): ?>
                <form action="/admin/roles/<?= $role['id'] ?>/delete" method="POST"
                    onsubmit="return confirm('Delete this role? Users must be reassigned first.')">
                    <?= csrf_field() ?>
                    <button type="submit"
                        class="text-xs text-danger hover:underline bg-transparent border-0 cursor-pointer">
                        Delete Role
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>