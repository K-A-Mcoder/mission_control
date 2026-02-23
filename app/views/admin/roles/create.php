<?php $layout = 'app'; ?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/admin/roles" class="text-muted hover:text-dark text-sm">← Roles</a>
        <span class="text-muted">/</span>
        <h2 class="text-xl font-semibold text-dark">Create Role</h2>
    </div>

    <?= partial('partials.flash') ?>

    <form action="/admin/roles" method="POST" class="space-y-5">
        <?= csrf_field() ?>

        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-6">
            <h3 class="text-sm font-semibold text-dark mb-5 pb-3 border-b border-b-color">Role Name</h3>
            <div>
                <label class="block text-sm font-medium text-dark mb-1.5" for="role_name">
                    Role Name <span class="text-danger">*</span>
                    <span class="text-xs text-muted font-normal ml-1">(lowercase, underscores)</span>
                </label>
                <input type="text" id="role_name" name="role_name" required
                    placeholder="e.g. field_agent"
                    pattern="[a-z_]+"
                    class="form-control w-full h-10 border border-b-color rounded-lg px-3 text-sm
                              focus:border-primary outline-none duration-300">
                <p class="text-xs text-muted mt-1">
                    Will be stored as-is. Use lowercase letters and underscores only.
                </p>
            </div>
        </div>

        <!-- Initial permissions -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-b-color">
                <h3 class="text-sm font-semibold text-dark">Assign Permissions</h3>
                <div class="flex gap-2">
                    <button type="button" onclick="checkAll(true)"
                        class="text-xs text-primary hover:underline">Select all</button>
                    <span class="text-muted text-xs">|</span>
                    <button type="button" onclick="checkAll(false)"
                        class="text-xs text-muted hover:text-dark">Clear</button>
                </div>
            </div>

            <div class="divide-y divide-b-color">
                <?php foreach ($allPerms as $group => $perms): ?>
                    <div class="px-6 py-5">
                        <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-3">
                            <?= htmlspecialchars(ucfirst($group)) ?>
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <?php foreach ($perms as $perm): ?>
                                <label class="flex items-center gap-2.5 cursor-pointer
                                              hover:bg-gray-50 dark:hover:bg-dark rounded-lg px-3 py-2">
                                    <input type="checkbox" name="permissions[]"
                                        value="<?= $perm['id'] ?>"
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

            <div class="px-6 py-4 border-t border-b-color flex items-center justify-between
                        bg-gray-50 dark:bg-dark">
                <a href="/admin/roles" class="text-sm text-muted hover:text-dark">Cancel</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold
                               hover:bg-hover-primary transition-colors">
                    Create Role
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function checkAll(state) {
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = state);
    }
</script>