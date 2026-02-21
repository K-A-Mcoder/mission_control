<?php $layout = 'app';

$statusBadge = match ($team['status']) {
    'inactive' => 'text-warning bg-warning-light',
    'archived' => 'text-muted bg-gray-100',
    default    => 'text-success bg-success-light',
};

$totalTasks     = array_sum($taskStats);
$completedTasks = $taskStats['closed'] ?? 0;
$progress       = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
?>

<!-- Page header -->
<div class="flex items-start justify-between mb-6">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <a href="/teams" class="text-muted hover:text-dark text-sm">← Teams</a>
        </div>
        <h2 class="text-xl font-semibold text-dark flex items-center gap-3">
            <?= htmlspecialchars($team['name']) ?>
            <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full <?= $statusBadge ?>">
                <?= $team['status'] ?>
            </span>
        </h2>
        <?php if ($team['lead_name']): ?>
            <p class="text-xs text-muted mt-1 flex items-center gap-1.5">
                <i class="fa-solid fa-user-tie"></i>
                Lead: <?= htmlspecialchars($team['lead_name']) ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if (has_any_role(['admin', 'manager'])): ?>
        <div class="flex gap-2">
            <a href="/teams/<?= $team['id'] ?>/tasks/create"
                class="btn btn-secondary text-sm px-4 py-2">+ Task</a>
            <a href="/teams/<?= $team['id'] ?>/edit"
                class="btn btn-secondary text-sm px-4 py-2">Edit</a>
            <?php if (has_role('admin')): ?>
                <form action="/teams/<?= $team['id'] ?>/delete" method="POST"
                    onsubmit="return confirm('Delete this team?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger text-sm px-4 py-2">Delete</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?= partial('partials.flash') ?>

<div class="grid grid-cols-3 gap-6">

    <!-- ── Main column ─────────────────────────────────────────────────────── -->
    <div class="col-span-2 space-y-6">

        <!-- Task progress summary -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-dark">Task Progress</h3>
                <a href="/teams/<?= $team['id'] ?>/tasks/create"
                    class="text-xs text-primary hover:underline">+ Add task</a>
            </div>

            <!-- Stat pills -->
            <div class="grid grid-cols-4 gap-3 mb-4">
                <?php
                $pillMap = [
                    'open'        => ['label' => 'Open',        'class' => 'bg-gray-100 text-muted'],
                    'in_progress' => ['label' => 'In Progress', 'class' => 'bg-primary-light text-primary'],
                    'review'      => ['label' => 'Review',      'class' => 'bg-warning-light text-warning'],
                    'closed'      => ['label' => 'Closed',      'class' => 'bg-success-light text-success'],
                ];
                foreach ($pillMap as $key => $pill): ?>
                    <div class="flex flex-col items-center justify-center rounded-lg py-3
                                <?= $pill['class'] ?>">
                        <span class="text-xl font-bold"><?= $taskStats[$key] ?? 0 ?></span>
                        <span class="text-[10px] uppercase tracking-wide mt-0.5"><?= $pill['label'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Progress bar -->
            <div>
                <div class="flex justify-between text-xs text-muted mb-1">
                    <span>Overall completion</span>
                    <span><?= $completedTasks ?> / <?= $totalTasks ?> &mdash; <?= $progress ?>%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all duration-500"
                        style="width: <?= $progress ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Task list -->
        <?php if (! empty($tasks)): ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg overflow-hidden">
                <div class="px-5 py-3 border-b border-b-color">
                    <h3 class="text-sm font-semibold text-dark">Tasks</h3>
                </div>
                <div class="divide-y divide-b-color">
                    <?php foreach ($tasks as $task):
                        $priorityClass = match ($task['priority']) {
                            'critical' => 'text-danger',
                            'high'     => 'text-warning',
                            'medium'   => 'text-primary',
                            default    => 'text-muted',
                        };
                        $statusClass = match ($task['status']) {
                            'closed'      => 'line-through text-muted',
                            'in_progress' => 'text-primary',
                            'review'      => 'text-warning',
                            default       => 'text-dark',
                        };
                    ?>
                        <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50
                                    dark:hover:bg-dark transition-colors">
                            <!-- Priority dot -->
                            <span class="w-2 h-2 rounded-full shrink-0 <?= $priorityClass ?>"
                                style="background:currentColor" title="<?= $task['priority'] ?>"></span>

                            <!-- Title -->
                            <a href="/tasks/<?= $task['id'] ?>"
                                class="flex-1 text-sm <?= $statusClass ?> hover:text-primary">
                                <?= htmlspecialchars($task['title']) ?>
                            </a>

                            <!-- Assignee -->
                            <span class="text-xs text-muted shrink-0">
                                <?= $task['assignee_name'] ? htmlspecialchars($task['assignee_name']) : '—' ?>
                            </span>

                            <!-- Due date -->
                            <?php if ($task['due_date']): ?>
                                <?php
                                $isOverdue = $task['status'] !== 'closed'
                                    && strtotime($task['due_date']) < time();
                                ?>
                                <span class="text-xs shrink-0 <?= $isOverdue ? 'text-danger font-medium' : 'text-muted' ?>">
                                    <?= date('M d', strtotime($task['due_date'])) ?>
                                </span>
                            <?php endif; ?>

                            <!-- Status chip -->
                            <span class="text-[10px] uppercase font-semibold px-2 py-0.5 rounded-full
                                <?= match ($task['status']) {
                                    'closed'      => 'bg-success-light text-success',
                                    'in_progress' => 'bg-primary-light text-primary',
                                    'review'      => 'bg-warning-light text-warning',
                                    default       => 'bg-gray-100 text-muted',
                                } ?>">
                                <?= str_replace('_', ' ', $task['status']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Missions -->
        <?php if (! empty($missions)): ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg overflow-hidden">
                <div class="px-5 py-3 border-b border-b-color">
                    <h3 class="text-sm font-semibold text-dark">Assigned Missions</h3>
                </div>
                <div class="divide-y divide-b-color">
                    <?php foreach ($missions as $m): ?>
                        <div class="flex items-center gap-4 px-5 py-3 text-sm">
                            <span class="font-mono text-xs text-muted shrink-0">
                                <?= htmlspecialchars($m['m_code']) ?>
                            </span>
                            <a href="/missions/<?= $m['id'] ?>"
                                class="flex-1 text-dark hover:text-primary">
                                <?= htmlspecialchars($m['title']) ?>
                            </a>
                            <span class="text-xs text-muted">
                                <?= date('M d, Y', strtotime($m['assigned_at'])) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Sidebar ──────────────────────────────────────────────────────────── -->
    <div class="space-y-5">

        <!-- Members card -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
            <h3 class="text-sm font-semibold text-dark mb-4">
                Members
                <span class="text-xs text-muted font-normal ml-1">(<?= count($members) ?>)</span>
            </h3>

            <?php if (empty($members)): ?>
                <p class="text-xs text-muted">No members yet.</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($members as $m): ?>
                        <li class="flex items-center justify-between gap-2">
                            <div>
                                <p class="text-sm font-medium text-dark leading-tight">
                                    <?= htmlspecialchars($m['full_name']) ?>
                                </p>
                                <p class="text-xs text-muted">
                                    <?= ucfirst($m['team_role']) ?> &middot; <?= htmlspecialchars($m['role_name']) ?>
                                </p>
                            </div>

                            <?php if (has_any_role(['admin', 'manager'])): ?>
                                <form action="/teams/<?= $team['id'] ?>/members/remove" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= $m['user_id'] ?>">
                                    <button type="submit"
                                        class="text-xs text-danger hover:underline bg-transparent border-0 cursor-pointer p-0"
                                        onclick="return confirm('Remove <?= htmlspecialchars(addslashes($m['full_name'])) ?>?')">
                                        Remove
                                    </button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Add member form -->
        <?php if (has_any_role(['admin', 'manager']) && ! empty($availableUsers)): ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
                <h3 class="text-sm font-semibold text-dark mb-3">Add Member</h3>
                <form action="/teams/<?= $team['id'] ?>/members" method="POST" class="space-y-3">
                    <?= csrf_field() ?>
                    <select name="user_id"
                        class="form-control w-full h-10 border border-b-color rounded-md px-3 text-sm
                                   focus:border-primary outline-none duration-300">
                        <option value="">— Select user —</option>
                        <?php foreach ($availableUsers as $u): ?>
                            <option value="<?= $u['user_id'] ?>">
                                <?= htmlspecialchars($u['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="role"
                        class="form-control w-full h-10 border border-b-color rounded-md px-3 text-sm
                                   focus:border-primary outline-none duration-300">
                        <option value="member">Member</option>
                        <option value="lead">Lead</option>
                    </select>
                    <button type="submit"
                        class="w-full btn btn-primary text-sm py-2 rounded font-medium">
                        Add to Team
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>