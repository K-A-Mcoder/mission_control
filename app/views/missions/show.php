<?php $layout = 'app';

$total     = (int) ($mission['total_tasks'] ?? 0);
$completed = (int) ($mission['completed_tasks'] ?? 0);
$progress  = $total > 0 ? round(($completed / $total) * 100) : 0;

$badgeClass = match ($mission['classification']) {
    'SECRET'       => 'bg-danger-light text-danger',
    'CONFIDENTIAL' => 'bg-warning-light text-warning',
    default        => 'bg-primary-light text-primary',
};
?>

<!-- Header -->
<div class="flex items-start justify-between mb-6">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <a href="/missions" class="text-muted hover:text-dark text-sm">← Missions</a>
        </div>
        <h2 class="text-xl font-semibold text-dark flex items-center gap-3">
            <?= htmlspecialchars($mission['title']) ?>
            <span class="text-xs font-medium px-2 py-1 rounded <?= $badgeClass ?>">
                <?= htmlspecialchars($mission['classification']) ?>
            </span>
        </h2>
        <p class="text-xs font-mono text-muted mt-1"><?= htmlspecialchars($mission['m_code']) ?></p>
    </div>

    <?php if (has_any_role(['admin', 'manager'])): ?>
        <div class="flex gap-2">
            <a href="/missions/<?= $mission['id'] ?>/edit"
                class="btn btn-secondary text-sm px-4 py-2">Edit</a>

            <?php if (has_role('admin')): ?>
                <form action="/missions/<?= $mission['id'] ?>/delete" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this mission?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger text-sm px-4 py-2">Delete</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?= partial('partials.flash') ?>

<div class="grid grid-cols-3 gap-6">

    <!-- Left — mission details -->
    <div class="col-span-2 space-y-6">

        <!-- Overview card -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
            <h3 class="text-sm font-semibold text-dark mb-4">Overview</h3>

            <div class="grid grid-cols-2 gap-4 text-sm mb-5">
                <div>
                    <p class="text-xs text-muted uppercase tracking-wide mb-1">Start</p>
                    <p class="text-dark">
                        <?= $mission['start_time'] ? date('M d, Y H:i', strtotime($mission['start_time'])) : '—' ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-muted uppercase tracking-wide mb-1">End</p>
                    <p class="text-dark">
                        <?= $mission['end_time'] ? date('M d, Y H:i', strtotime($mission['end_time'])) : '—' ?>
                    </p>
                </div>
            </div>

            <!-- Task progress -->
            <div>
                <div class="flex justify-between text-xs text-muted mb-1">
                    <span>Task Progress</span>
                    <span><?= $completed ?> / <?= $total ?> tasks &mdash; <?= $progress ?>%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all duration-500"
                        style="width: <?= $progress ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <?php if (! empty($mission['description'])): ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
                <h3 class="text-sm font-semibold text-dark mb-3">Description</h3>
                <p class="text-sm text-body-color leading-relaxed">
                    <?= nl2br(htmlspecialchars($mission['description'])) ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right — teams sidebar -->
    <div class="space-y-4">

        <!-- Assigned teams -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
            <h3 class="text-sm font-semibold text-dark mb-4">Assigned Teams</h3>

            <?php if (empty($teams)): ?>
                <p class="text-xs text-muted">No teams assigned yet.</p>
            <?php else: ?>
                <ul class="space-y-2">
                    <?php foreach ($teams as $team): ?>
                        <li class="flex items-center gap-2 text-sm">
                            <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
                            <?= htmlspecialchars($team['name']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Assign team form -->
        <?php if (has_any_role(['admin', 'manager']) && ! empty($availableTeams)): ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
                <h3 class="text-sm font-semibold text-dark mb-3">Assign a Team</h3>
                <form action="/missions/<?= $mission['id'] ?>/assign-team" method="POST" class="space-y-3">
                    <?= csrf_field() ?>
                    <select name="team_id"
                        class="form-control w-full h-10 border border-b-color rounded-md px-3 text-sm focus:border-primary outline-none duration-300">
                        <option value="">— Select team —</option>
                        <?php foreach ($availableTeams as $team): ?>
                            <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit"
                        class="w-full btn btn-primary text-sm py-2 rounded font-medium">
                        Assign Team
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>