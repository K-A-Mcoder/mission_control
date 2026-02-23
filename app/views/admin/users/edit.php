<?php $layout = 'app'; ?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/admin/users" class="text-muted hover:text-dark text-sm">← Users</a>
        <span class="text-muted">/</span>
        <a href="/admin/users/<?= $user['user_id'] ?>" class="text-muted hover:text-dark text-sm">
            <?= htmlspecialchars($user['full_name']) ?>
        </a>
        <span class="text-muted">/</span>
        <h2 class="text-xl font-semibold text-dark">Edit</h2>
    </div>

    <?= partial('partials.flash') ?>

    <form action="/admin/users/<?= $user['user_id'] ?>/update" method="POST" class="space-y-5">
        <?= csrf_field() ?>

        <!-- Profile -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-6">
            <h3 class="text-sm font-semibold text-dark mb-5 pb-3 border-b border-b-color">Profile</h3>
            <div class="grid grid-cols-2 gap-5">

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-dark mb-1.5" for="full_name">
                        Full Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" required
                        value="<?= htmlspecialchars($user['full_name']) ?>"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 text-sm
                                  focus:border-primary outline-none duration-300">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-dark mb-1.5" for="email">
                        Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                        value="<?= htmlspecialchars($user['email']) ?>"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 text-sm
                                  focus:border-primary outline-none duration-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark mb-1.5" for="role_id">Role</label>
                    <select id="role_id" name="role_id"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 text-sm
                                   focus:border-primary outline-none duration-300">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"
                                <?= (int)$r['id'] === (int)$user['role_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(str_replace('_', ' ', ucfirst($r['role_name']))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark mb-1.5" for="status">Status</label>
                    <select id="status" name="status"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 text-sm
                                   focus:border-primary outline-none duration-300">
                        <?php foreach (['active', 'pending', 'suspended', 'inactive'] as $s): ?>
                            <option value="<?= $s ?>" <?= $user['status'] === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Password reset (optional) -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-6">
            <h3 class="text-sm font-semibold text-dark mb-1">Reset Password</h3>
            <p class="text-xs text-muted mb-4">Leave blank to keep the current password.</p>
            <div class="grid grid-cols-2 gap-5">
                <div class="relative">
                    <label class="block text-sm font-medium text-dark mb-1.5">New Password</label>
                    <input type="password" name="new_password" id="new_password"
                        placeholder="Min. 8 characters"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 pr-10 text-sm
                                  focus:border-primary outline-none duration-300">
                    <button type="button" onclick="togglePass('new_password')"
                        class="absolute right-3 top-9 text-muted hover:text-dark text-sm">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between bg-white dark:bg-dark-card border border-b-color
                    rounded-xl px-6 py-4">
            <div class="flex items-center gap-3">
                <a href="/admin/users/<?= $user['user_id'] ?>" class="text-sm text-muted hover:text-dark">
                    Cancel
                </a>
                <!-- Danger: delete -->
                <?php if ((int)$user['user_id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                    <form action="/admin/users/<?= $user['user_id'] ?>/delete" method="POST"
                        onsubmit="return confirm('Deactivate this account? This cannot be undone easily.')">
                        <?= csrf_field() ?>
                        <button type="submit" class="text-sm text-danger hover:underline
                                                     bg-transparent border-0 cursor-pointer">
                            Deactivate Account
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <button type="submit"
                class="px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold
                           hover:bg-hover-primary transition-colors">
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
    function togglePass(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
</script>