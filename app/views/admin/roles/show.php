<?php $layout = 'app'; ?>

<div class="flex items-center gap-3 mb-6">
    <a href="/admin/roles" class="text-muted hover:text-dark text-sm">← Roles</a>
    <span class="text-muted">/</span>
    <h2 class="text-xl font-semibold text-dark"><?= htmlspecialchars($title) ?></h2>
    <?php if ($isSystem): ?>
        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-gray-100 text-muted">
            System Role
        </span>
    <?php endif; ?>
</div>

<?= partial('partials.flash') ?>

<div class="grid grid-cols-3 gap-6">

    <!-- Permissions form -->
    <div class="col-span-2">
        <form action="/admin/roles/<?= $role['id'] ?>/permissions" method="POST">
            <?= csrf_field() ?>

            <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-b-color">
                    <h3 class="text-sm font-semibold text-dark">Permission Matrix</h3>
                    <?php if (! $isSystem || $role['role_name'] !== 'super_admin'): ?>
                        <div class="flex gap-2">
                            <button type="button" onclick="checkAll(true)"
                                class="text-xs text-primary hover:underline">Select all</button>
                            <span class="text-muted text-xs">|</span>
                            <button type="button" onclick="checkAll(false)"
                                class="text-xs text-muted hover:text-dark">Clear</button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="divide-y divide-b-color">
                    <?php foreach ($allPerms as $group => $perms): ?>
                        <div class="px-6 py-5">
                            <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-3">
                                <i class="fa-solid fa-layer-group mr-1.5"></i>
                                <?= htmlspecialchars(ucfirst($group)) ?>
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                <?php foreach ($perms as $perm):
                                    $checked  = in_array((int)$perm['id'], $assignedIds, true);
                                    $disabled = ($isSystem && $role['role_name'] === 'super_admin');
                                ?>
                                    <label class="flex items-center gap-2.5 cursor-pointer
                                                  <?= $disabled ? 'opacity-60 cursor-not-allowed' : 'hover:bg-gray-50 dark:hover:bg-dark' ?>
                                                  rounded-lg px-3 py-2 transition-colors">
                                        <input type="checkbox"
                                            name="permissions[]"
                                            value="<?= $perm['id'] ?>"
                                            <?= $checked  ? 'checked'   : '' ?>
                                            <?= $disabled ? 'disabled'  : '' ?>
                                            class="w-3.5 h-3.5 accent-primary">
                                        <div>
                                            <span class="text-sm text-dark"><?= htmlspecialchars($perm['label']) ?></span>
                                            <span class="block text-[10px] text-muted font-mono"><?= htmlspecialchars($perm['name']) ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (! ($isSystem && $role['role_name'] === 'super_admin')): ?>
                    <div class="px-6 py-4 border-t border-b-color flex justify-end bg-gray-50 dark:bg-dark">
                        <button type="submit"
                            class="px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold
                                       hover:bg-hover-primary transition-colors">
                            Save Permissions
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Sidebar: users with this role -->
    <div>
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-b-color flex items-center justify-between">
                <h3 class="text-sm font-semibold text-dark">Users</h3>
                <span class="text-xs text-muted"><?= count($roleUsers) ?></span>
            </div>
            <?php if (empty($roleUsers)): ?>
                <p class="px-5 py-6 text-sm text-muted text-center">No users have this role.</p>
            <?php else: ?>
                <div class="divide-y divide-b-color max-h-96 overflow-y-auto">
                    <?php foreach ($roleUsers as $u): ?>
                        <div class="flex items-center gap-3 px-5 py-3">
                            <div class="w-7 h-7 rounded-full bg-primary-light flex items-center
                                        justify-center text-primary text-xs font-bold shrink-0">
                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="/admin/users/<?= $u['user_id'] ?>"
                                    class="text-sm text-dark hover:text-primary truncate block">
                                    <?= htmlspecialchars($u['full_name']) ?>
                                </a>
                                <p class="text-xs text-muted truncate"><?= htmlspecialchars($u['email']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function checkAll(state) {
        document.querySelectorAll('input[name="permissions[]"]:not(:disabled)')
            .forEach(cb => cb.checked = state);
    }
</script>