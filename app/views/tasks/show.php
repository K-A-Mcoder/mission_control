<?php $layout = 'app';

$priorityClass = match ($task['priority']) {
    'critical' => 'text-danger bg-danger-light border-danger-light',
    'high'     => 'text-warning bg-warning-light border-warning-light',
    'medium'   => 'text-primary bg-primary-light border-primary-light',
    default    => 'text-muted bg-gray-100 border-gray-200',
};

$statusClass = match ($task['status']) {
    'closed'      => 'text-success bg-success-light',
    'in_progress' => 'text-primary bg-primary-light',
    'review'      => 'text-warning bg-warning-light',
    default       => 'text-muted bg-gray-100',
};
?>

<!-- Header -->
<div class="flex items-start justify-between mb-6">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <?php if ($task['team_id']): ?>
                <a href="/teams/<?= $task['team_id'] ?>"
                    class="text-muted hover:text-dark text-sm">
                    ← <?= htmlspecialchars($task['team_name'] ?? 'Team') ?>
                </a>
            <?php else: ?>
                <a href="/teams" class="text-muted hover:text-dark text-sm">← Teams</a>
            <?php endif; ?>
        </div>

        <h2 class="text-xl font-semibold text-dark flex flex-wrap items-center gap-2">
            <?= htmlspecialchars($task['title']) ?>
            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full border <?= $priorityClass ?>">
                <?= $task['priority'] ?>
            </span>
            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full <?= $statusClass ?>">
                <?= str_replace('_', ' ', $task['status']) ?>
            </span>
        </h2>

        <p class="text-xs text-muted mt-1">
            <?php if ($task['mission_title']): ?>
                Mission: <a href="/missions/<?= $task['mission_id'] ?>"
                    class="text-primary hover:underline">
                    <?= htmlspecialchars($task['mission_title']) ?>
                </a> &middot;
            <?php endif; ?>
            Created <?= date('M d, Y', strtotime($task['created_at'])) ?>
        </p>
    </div>

    <?php if (has_any_role(['admin', 'manager'])): ?>
        <div class="flex gap-2">
            <a href="/tasks/<?= $task['id'] ?>/edit"
                class="btn btn-secondary text-sm px-4 py-2">Edit</a>
            <form action="/tasks/<?= $task['id'] ?>/delete" method="POST"
                onsubmit="return confirm('Delete this task?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger text-sm px-4 py-2">Delete</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?= partial('partials.flash') ?>

<div class="grid grid-cols-3 gap-6">

    <!-- ── Main ──────────────────────────────────────────────────────────── -->
    <div class="col-span-2 space-y-5">

        <!-- Description -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
            <h3 class="text-sm font-semibold text-dark mb-3">Description</h3>
            <?php if (! empty($task['description'])): ?>
                <p class="text-sm text-body-color leading-relaxed">
                    <?= nl2br(htmlspecialchars($task['description'])) ?>
                </p>
            <?php else: ?>
                <p class="text-sm text-muted italic">No description provided.</p>
            <?php endif; ?>
        </div>

        <!-- Status change -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
            <h3 class="text-sm font-semibold text-dark mb-3">Update Status</h3>
            <form action="/tasks/<?= $task['id'] ?>/status" method="POST"
                class="flex flex-wrap gap-2">
                <?= csrf_field() ?>
                <?php foreach ($statuses as $s): ?>
                    <button type="submit" name="status" value="<?= $s ?>"
                        class="text-xs font-semibold uppercase px-3 py-1.5 rounded border transition-all
                            <?= $task['status'] === $s
                                ? 'bg-primary border-primary text-white'
                                : 'border-b-color text-muted hover:border-primary hover:text-primary' ?>">
                        <?= str_replace('_', ' ', $s) ?>
                    </button>
                <?php endforeach; ?>
            </form>
        </div>

        <!-- Comments -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg overflow-hidden"
            id="comments">
            <div class="px-5 py-3 border-b border-b-color">
                <h3 class="text-sm font-semibold text-dark">
                    Comments
                    <span class="text-xs text-muted font-normal ml-1">(<?= count($comments) ?>)</span>
                </h3>
            </div>

            <?php if (! empty($comments)): ?>
                <div class="divide-y divide-b-color">
                    <?php foreach ($comments as $comment): ?>
                        <div class="px-5 py-4">
                            <div class="flex items-baseline justify-between mb-2">
                                <p class="text-sm font-semibold text-dark">
                                    <?= htmlspecialchars($comment['author_name']) ?>
                                </p>
                                <p class="text-xs text-muted">
                                    <?= date('M d, Y H:i', strtotime($comment['created_at'])) ?>
                                </p>
                            </div>
                            <p class="text-sm text-body-color leading-relaxed">
                                <?= nl2br(htmlspecialchars($comment['body'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Add comment -->
            <div class="px-5 py-4 border-t border-b-color bg-gray-50 dark:bg-dark">
                <form action="/tasks/<?= $task['id'] ?>/comment" method="POST" class="space-y-3">
                    <?= csrf_field() ?>
                    <textarea name="body" rows="3" required
                        placeholder="Write a comment..."
                        class="form-control w-full border border-b-color rounded-md px-3 py-2 text-sm
                                     focus:border-primary outline-none duration-300 resize-none"></textarea>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="btn btn-primary text-sm px-4 py-2 rounded font-medium">
                            Post Comment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Sidebar ─────────────────────────────────────────────────────────── -->
    <div class="space-y-5">

        <!-- Details card -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5 space-y-4 text-sm">
            <h3 class="text-sm font-semibold text-dark">Details</h3>

            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Assignee</p>
                <p class="text-dark">
                    <?= $task['assignee_name'] ? htmlspecialchars($task['assignee_name']) : '—' ?>
                </p>
            </div>

            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Team</p>
                <p>
                    <?php if ($task['team_id']): ?>
                        <a href="/teams/<?= $task['team_id'] ?>"
                            class="text-primary hover:underline">
                            <?= htmlspecialchars($task['team_name'] ?? '') ?>
                        </a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </p>
            </div>

            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Due Date</p>
                <?php if ($task['due_date']): ?>
                    <?php $isOverdue = $task['status'] !== 'closed' && strtotime($task['due_date']) < time(); ?>
                    <p class="<?= $isOverdue ? 'text-danger font-semibold' : 'text-dark' ?>">
                        <?= date('M d, Y', strtotime($task['due_date'])) ?>
                        <?= $isOverdue ? '(Overdue)' : '' ?>
                    </p>
                <?php else: ?>
                    <p class="text-dark">—</p>
                <?php endif; ?>
            </div>

            <?php if ($task['completed_at']): ?>
                <div>
                    <p class="text-xs text-muted uppercase tracking-wide mb-1">Completed</p>
                    <p class="text-success">
                        <?= date('M d, Y H:i', strtotime($task['completed_at'])) ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reassign (admin/manager only) -->
        <?php if (has_any_role(['admin', 'manager']) && ! empty($members)): ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
                <h3 class="text-sm font-semibold text-dark mb-3">Reassign</h3>
                <form action="/tasks/<?= $task['id'] ?>/edit" method="GET">
                    <p class="text-xs text-muted mb-2">
                        To reassign, use the
                        <a href="/tasks/<?= $task['id'] ?>/edit"
                            class="text-primary hover:underline">edit form</a>.
                    </p>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>