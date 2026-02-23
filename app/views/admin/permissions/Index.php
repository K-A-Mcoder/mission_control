<?php $layout = 'app'; ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Permission Management</h2>
        <p class="text-sm text-muted mt-0.5">
            All system permissions — grouped by module. Assign them to roles on the Roles page.
        </p>
    </div>
    <button onclick="document.getElementById('createPanel').classList.toggle('hidden')"
        class="btn btn-primary text-sm px-4 py-2">
        <i class="fa-solid fa-plus mr-1.5 text-xs"></i> New Permission
    </button>
</div>

<?= partial('partials.flash') ?>

<!-- Inline create form (toggled) -->
<div id="createPanel" class="hidden mb-6">
    <div class="bg-white dark:bg-dark-card border border-primary/30 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-dark mb-4">Create New Permission</h3>
        <form action="/admin/permissions" method="POST" class="grid grid-cols-3 gap-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-medium text-dark mb-1">Name (slug) *</label>
                <input type="text" name="name" required placeholder="e.g. view-reports"
                    class="form-control w-full h-9 border border-b-color rounded-lg px-3 text-sm
                              focus:border-primary outline-none duration-300">
            </div>
            <div>
                <label class="block text-xs font-medium text-dark mb-1">Label *</label>
                <input type="text" name="label" required placeholder="e.g. View Reports"
                    class="form-control w-full h-9 border border-b-color rounded-lg px-3 text-sm
                              focus:border-primary outline-none duration-300">
            </div>
            <div>
                <label class="block text-xs font-medium text-dark mb-1">Group</label>
                <select name="group"
                    class="form-control w-full h-9 border border-b-color rounded-lg px-3 text-sm
                               focus:border-primary outline-none duration-300">
                    <?php foreach ($groups as $slug => $label): ?>
                        <option value="<?= $slug ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                    <option value="general">General</option>
                </select>
            </div>
            <div class="col-span-3 flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('createPanel').classList.add('hidden')"
                    class="text-sm text-muted hover:text-dark">Cancel</button>
                <button type="submit"
                    class="px-5 py-2 bg-primary text-white rounded-lg text-sm font-semibold
                               hover:bg-hover-primary transition-colors">
                    Create Permission
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Grouped permissions -->
<div class="space-y-5">
    <?php
    // echo "<pre>";
    // var_dump($grouped);
    // echo "</pre>";
    // die();
    ?>
    <?php foreach ($grouped as $group => $perms): ?>
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">

            <div class="flex items-center justify-between px-5 py-3 border-b border-b-color
                        bg-gray-50 dark:bg-dark">
                <h3 class="text-sm font-semibold text-dark flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-xs text-muted"></i>
                    <?= htmlspecialchars(ucfirst($group)) ?>
                </h3>
                <span class="text-xs text-muted"><?= count($perms) ?> permissions</span>
            </div>

            <div class="divide-y divide-b-color">
                <?php foreach ($perms as $perm): ?>
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-dark">
                                    <?= htmlspecialchars($perm['label']) ?>
                                </span>
                                <code class="text-[10px] font-mono bg-gray-100 px-1.5 py-0.5 rounded
                                             text-muted">
                                    <?= htmlspecialchars($perm['name']) ?>
                                </code>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0 ml-4">
                            <span class="text-xs text-muted">
                                <?= (int)$perm['role_count'] ?> role<?= (int)$perm['role_count'] !== 1 ? 's' : '' ?>
                            </span>
                            <!-- Inline edit -->
                            <button onclick="toggleEdit(<?= $perm['id'] ?>)"
                                class="text-xs text-primary hover:underline">Edit</button>
                            <!-- Delete -->
                            <form action="/admin/permissions/<?= $perm['id'] ?>/delete" method="POST"
                                onsubmit="return confirm('Delete permission \'<?= addslashes($perm['name']) ?>\'?')">
                                <?= csrf_field() ?>
                                <button type="submit"
                                    class="text-xs text-danger hover:underline bg-transparent border-0 cursor-pointer p-0">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Inline edit row (hidden by default) -->
                    <div id="edit-<?= $perm['id'] ?>" class="hidden px-5 py-4 bg-gray-50 dark:bg-dark">
                        <form action="/admin/permissions/<?= $perm['id'] ?>/update" method="POST"
                            class="flex gap-3">
                            <?= csrf_field() ?>
                            <input type="text" name="label"
                                value="<?= htmlspecialchars($perm['label']) ?>"
                                class="form-control h-9 border border-b-color rounded-lg px-3 text-sm
                                          focus:border-primary outline-none flex-1 duration-300">
                            <select name="group"
                                class="form-control h-9 border border-b-color rounded-lg px-3 text-sm
                                           focus:border-primary outline-none duration-300">
                                <?php foreach ($groups as $slug => $gl): ?>
                                    <option value="<?= $slug ?>"
                                        <?= $perm['group'] === $slug ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($gl) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit"
                                class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium
                                           hover:bg-hover-primary transition-colors">
                                Save
                            </button>
                            <button type="button" onclick="toggleEdit(<?= $perm['id'] ?>)"
                                class="text-sm text-muted hover:text-dark">Cancel</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function toggleEdit(id) {
        document.getElementById('edit-' + id).classList.toggle('hidden');
    }
</script>