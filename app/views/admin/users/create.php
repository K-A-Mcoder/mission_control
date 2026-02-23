<?php $layout = 'app'; ?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/admin/users" class="text-muted hover:text-dark text-sm">← Users</a>
        <span class="text-muted">/</span>
        <h2 class="text-xl font-semibold text-dark"><?= htmlspecialchars($title) ?></h2>
    </div>

    <?= partial('partials.flash') ?>

    <form action="/admin/users" method="POST" class="space-y-5">
        <?= csrf_field() ?>

        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-6">
            <h3 class="text-sm font-semibold text-dark mb-5 pb-3 border-b border-b-color">
                Account Details
            </h3>
            <div class="grid grid-cols-2 gap-5">

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-dark mb-1.5" for="full_name">
                        Full Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" required
                        placeholder="Jane Smith"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 text-sm
                                  focus:border-primary outline-none duration-300">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-dark mb-1.5" for="email">
                        Email Address <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                        placeholder="jane@example.com"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 text-sm
                                  focus:border-primary outline-none duration-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark mb-1.5" for="role_id">
                        Role <span class="text-danger">*</span>
                    </label>
                    <select id="role_id" name="role_id" required
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 text-sm
                                   focus:border-primary outline-none duration-300">
                        <option value="">— Select role —</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', ucfirst($r['role_name']))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark mb-1.5" for="status">
                        Status
                    </label>
                    <select id="status" name="status"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 text-sm
                                   focus:border-primary outline-none duration-300">
                        <option value="active">Active</option>
                        <option value="pending">Pending (requires activation)</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>

            </div>
        </div>

        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-6">
            <h3 class="text-sm font-semibold text-dark mb-5 pb-3 border-b border-b-color">Password</h3>
            <div class="grid grid-cols-2 gap-5">
                <div class="relative">
                    <label class="block text-sm font-medium text-dark mb-1.5" for="password">
                        Password <span class="text-danger">*</span>
                    </label>
                    <input type="password" id="password" name="password" required
                        placeholder="Min. 8 characters"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 pr-10 text-sm
                                  focus:border-primary outline-none duration-300">
                    <button type="button" onclick="togglePass('password')"
                        class="absolute right-3 top-9 text-muted hover:text-dark text-sm">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <div class="relative">
                    <label class="block text-sm font-medium text-dark mb-1.5" for="password_confirm">
                        Confirm <span class="text-danger">*</span>
                    </label>
                    <input type="password" id="password_confirm" name="password_confirm" required
                        placeholder="Repeat password"
                        class="form-control w-full h-10 border border-b-color rounded-lg px-3 pr-10 text-sm
                                  focus:border-primary outline-none duration-300">
                    <button type="button" onclick="togglePass('password_confirm')"
                        class="absolute right-3 top-9 text-muted hover:text-dark text-sm">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between bg-white dark:bg-dark-card border border-b-color
                    rounded-xl px-6 py-4">
            <a href="/admin/users" class="text-sm text-muted hover:text-dark">Cancel</a>
            <button type="submit"
                class="px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold
                           hover:bg-hover-primary transition-colors">
                Create User
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