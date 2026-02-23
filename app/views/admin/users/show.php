<?php $layout = 'app'; ?>

<!-- Header -->
<div class="flex items-start justify-between mb-6">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-primary-light flex items-center justify-center
                    text-primary text-xl font-bold shrink-0">
            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-dark"><?= htmlspecialchars($user['full_name']) ?></h2>
            <div class="flex items-center gap-3 mt-1 text-xs text-muted">
                <span><?= htmlspecialchars($user['email']) ?></span>
                <span>·</span>
                <span class="capitalize"><?= str_replace('_', ' ', $user['role_name']) ?></span>
                <span>·</span>
                <?php
                $sc = match ($user['status']) {
                    'active'    => 'text-success bg-success-light',
                    'pending'   => 'text-warning bg-warning-light',
                    'suspended' => 'text-danger bg-danger-light',
                    default     => 'text-muted bg-gray-100',
                };
                ?>
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $sc ?>">
                    <?= $user['status'] ?>
                </span>
            </div>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="/admin/users/<?= $user['user_id'] ?>/edit"
            class="btn btn-secondary text-sm px-4 py-2">Edit</a>
        <?php if ($user['status'] === 'active'): ?>
            <form action="/admin/users/<?= $user['user_id'] ?>/status" method="POST"
                onsubmit="return confirm('Suspend this user?')">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="suspended">
                <button type="submit" class="btn btn-danger text-sm px-4 py-2">Suspend</button>
            </form>
        <?php elseif ($user['status'] === 'suspended'): ?>
            <form action="/admin/users/<?= $user['user_id'] ?>/status" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="active">
                <button type="submit" class="btn btn-primary text-sm px-4 py-2">Reactivate</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?= partial('partials.flash') ?>

<div class="grid grid-cols-3 gap-6">

    <!-- Left: stats + teams + tasks -->
    <div class="col-span-2 space-y-5">

        <!-- Task Stats -->
        <div class="grid grid-cols-4 gap-3">
            <?php
            $ts = $stats['tasks'];
            $cards = [
                ['label' => 'Total Tasks',  'val' => $ts['total'],       'c' => 'text-dark bg-gray-100'],
                ['label' => 'Open',         'val' => $ts['open'],        'c' => 'text-muted bg-gray-100'],
                ['label' => 'In Progress',  'val' => $ts['in_progress'], 'c' => 'text-primary bg-primary-light'],
                ['label' => 'Closed',       'val' => $ts['closed'],      'c' => 'text-success bg-success-light'],
            ];
            foreach ($cards as $c): ?>
                <div class="rounded-xl p-4 text-center <?= $c['c'] ?>">
                    <p class="text-2xl font-bold"><?= $c['val'] ?></p>
                    <p class="text-[10px] uppercase tracking-wide mt-0.5"><?= $c['label'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Teams -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-b-color">
                <h3 class="text-sm font-semibold text-dark">Teams</h3>
                <span class="text-xs text-muted"><?= count($userTeams) ?> teams</span>
            </div>
            <?php if (empty($userTeams)): ?>
                <p class="px-5 py-6 text-sm text-muted text-center">Not a member of any team.</p>
            <?php else: ?>
                <div class="divide-y divide-b-color">
                    <?php foreach ($userTeams as $t): ?>
                        <div class="flex items-center justify-between px-5 py-3">
                            <a href="/teams/<?= $t['id'] ?>"
                                class="text-sm font-medium text-dark hover:text-primary">
                                <?= htmlspecialchars($t['name']) ?>
                            </a>
                            <span class="text-xs text-muted">
                                Joined <?= $t['joined_at'] ? date('M d, Y', strtotime($t['joined_at'])) : '—' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent tasks -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-b-color">
                <h3 class="text-sm font-semibold text-dark">Recent Tasks</h3>
            </div>
            <?php if (empty($recentTasks)): ?>
                <p class="px-5 py-6 text-sm text-muted text-center">No tasks assigned.</p>
            <?php else: ?>
                <div class="divide-y divide-b-color">
                    <?php foreach ($recentTasks as $tk):
                        $tsc = match ($tk['status']) {
                            'in_progress' => 'text-primary bg-primary-light',
                            'closed'      => 'text-success bg-success-light',
                            'review'      => 'text-warning bg-warning-light',
                            default       => 'text-muted bg-gray-100',
                        };
                    ?>
                        <div class="flex items-center justify-between px-5 py-3">
                            <a href="/tasks/<?= $tk['id'] ?>"
                                class="text-sm text-dark hover:text-primary">
                                <?= htmlspecialchars($tk['title']) ?>
                                <?php if ($tk['team_name']): ?>
                                    <span class="text-xs text-muted ml-1">
                                        — <?= htmlspecialchars($tk['team_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $tsc ?>">
                                <?= str_replace('_', ' ', $tk['status']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: details + activity -->
    <div class="space-y-5">

        <!-- Details -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-5 space-y-4 text-sm">
            <h3 class="text-sm font-semibold text-dark">Details</h3>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">User ID</p>
                <p class="text-dark font-mono">#<?= $user['user_id'] ?></p>
            </div>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Role</p>
                <p class="text-dark capitalize"><?= str_replace('_', ' ', $user['role_name']) ?></p>
            </div>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Reports</p>
                <p class="text-dark"><?= (int)$stats['reports']['total'] ?> submitted</p>
            </div>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Last Login</p>
                <p class="text-dark">
                    <?= $user['last_login_at']
                        ? date('M d, Y H:i', strtotime($user['last_login_at']))
                        : 'Never' ?>
                </p>
            </div>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Created</p>
                <p class="text-dark">
                    <?= $user['created_at'] ? date('M d, Y', strtotime($user['created_at'])) : '—' ?>
                </p>
            </div>
        </div>

        <!-- Activity log -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-b-color">
                <h3 class="text-sm font-semibold text-dark">Recent Activity</h3>
            </div>
            <?php if (empty($activity)): ?>
                <p class="px-5 py-6 text-sm text-muted text-center">No activity logged.</p>
            <?php else: ?>
                <div class="divide-y divide-b-color max-h-64 overflow-y-auto">
                    <?php foreach ($activity as $log): ?>
                        <div class="px-5 py-3">
                            <p class="text-xs text-dark">
                                <span class="font-medium capitalize"><?= htmlspecialchars($log['action']) ?></span>
                                <?= htmlspecialchars(basename(str_replace('\\', '/', $log['model']))) ?>
                            </p>
                            <p class="text-[10px] text-muted mt-0.5">
                                <?= date('M d, Y H:i', strtotime($log['created_at'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>