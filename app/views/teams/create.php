<?php $layout = 'app'; ?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/teams" class="text-muted hover:text-dark text-sm">← Teams</a>
        <span class="text-muted">/</span>
        <h2 class="text-xl font-semibold text-dark">New Team</h2>
    </div>

    <?= partial('partials.flash') ?>

    <form action="/teams" method="POST"
        class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-6 space-y-5">
        <?= csrf_field() ?>

        <!-- Name -->
        <div>
            <label class="block mb-1 text-sm font-medium text-dark" for="name">
                Team Name <span class="text-danger">*</span>
            </label>
            <input type="text" id="name" name="name" required
                placeholder="e.g. Alpha Strike"
                class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm
                          focus:border-primary outline-none duration-300">
        </div>

        <!-- Description -->
        <div>
            <label class="block mb-1 text-sm font-medium text-dark" for="description">Description</label>
            <textarea id="description" name="description" rows="3"
                placeholder="What is this team responsible for?"
                class="form-control w-full border border-b-color rounded-md px-3 py-2 text-sm
                             focus:border-primary outline-none duration-300 resize-none"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <!-- Team Lead -->
            <div>
                <label class="block mb-1 text-sm font-medium text-dark" for="lead_id">Team Lead</label>
                <select id="lead_id" name="lead_id"
                    class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm
                               focus:border-primary outline-none duration-300">
                    <option value="">— No lead assigned —</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['user_id'] ?>">
                            <?= htmlspecialchars($user['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block mb-1 text-sm font-medium text-dark" for="status">Status</label>
                <select id="status" name="status"
                    class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm
                               focus:border-primary outline-none duration-300">
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= $s === 'active' ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Members -->
        <div>
            <label class="block mb-2 text-sm font-medium text-dark">Add Members</label>
            <div class="border border-b-color rounded-md divide-y divide-b-color max-h-56 overflow-y-auto">
                <?php foreach ($users as $user): ?>
                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50
                                  dark:hover:bg-dark cursor-pointer text-sm">
                        <input type="checkbox" name="member_ids[]"
                            value="<?= $user['user_id'] ?>"
                            class="form-check-input">
                        <span class="flex-1"><?= htmlspecialchars($user['full_name']) ?></span>
                        <span class="text-xs text-muted"><?= htmlspecialchars($user['email']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-muted mt-1">
                The team lead will be added automatically if not checked.
            </p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="/teams" class="text-sm text-muted hover:text-dark">Cancel</a>
            <button type="submit" class="btn btn-primary px-6 py-2 text-sm rounded font-medium">
                Create Team
            </button>
        </div>
    </form>
</div>