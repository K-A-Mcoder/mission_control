<?php $layout = 'app'; ?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/teams/<?= $team['id'] ?>" class="text-muted hover:text-dark text-sm">
            ← <?= htmlspecialchars($team['name']) ?>
        </a>
        <span class="text-muted">/</span>
        <h2 class="text-xl font-semibold text-dark">New Task</h2>
    </div>

    <?= partial('partials.flash') ?>

    <form action="/teams/<?= $team['id'] ?>/tasks" method="POST"
        class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-6 space-y-5">
        <?= csrf_field() ?>

        <!-- Title -->
        <div>
            <label class="block mb-1 text-sm font-medium text-dark" for="title">
                Title <span class="text-danger">*</span>
            </label>
            <input type="text" id="title" name="title" required
                placeholder="What needs to be done?"
                class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm
                          focus:border-primary outline-none duration-300">
        </div>

        <!-- Description -->
        <div>
            <label class="block mb-1 text-sm font-medium text-dark" for="description">Description</label>
            <textarea id="description" name="description" rows="3"
                placeholder="Additional context or instructions..."
                class="form-control w-full border border-b-color rounded-md px-3 py-2 text-sm
                             focus:border-primary outline-none duration-300 resize-none"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <!-- Priority -->
            <div>
                <label class="block mb-1 text-sm font-medium text-dark" for="priority">Priority</label>
                <select id="priority" name="priority"
                    class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm
                               focus:border-primary outline-none duration-300">
                    <?php foreach ($priorities as $p): ?>
                        <option value="<?= $p ?>" <?= $p === 'medium' ? 'selected' : '' ?>>
                            <?= ucfirst($p) ?>
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
                        <option value="<?= $s ?>" <?= $s === 'open' ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Assignee -->
            <div>
                <label class="block mb-1 text-sm font-medium text-dark" for="assigned_to">
                    Assign To
                </label>
                <select id="assigned_to" name="assigned_to"
                    class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm
                               focus:border-primary outline-none duration-300">
                    <option value="">— Unassigned —</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?= $m['user_id'] ?>">
                            <?= htmlspecialchars($m['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Due date -->
            <div>
                <label class="block mb-1 text-sm font-medium text-dark" for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date"
                    min="<?= date('Y-m-d') ?>"
                    class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm
                              focus:border-primary outline-none duration-300">
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="/teams/<?= $team['id'] ?>" class="text-sm text-muted hover:text-dark">Cancel</a>
            <button type="submit" class="btn btn-primary px-6 py-2 text-sm rounded font-medium">
                Create Task
            </button>
        </div>
    </form>
</div>