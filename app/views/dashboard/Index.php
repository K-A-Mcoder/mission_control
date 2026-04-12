<?php $layout = 'app'; ?>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">
            <?= $isSuperAdmin ? 'Super Admin Dashboard' : 'Dashboard' ?>
        </h2>
        <p class="text-sm text-muted mt-0.5">
            Welcome back, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></strong>
            &mdash; <?= date('l, F j Y') ?>
        </p>
    </div>

    <div class="flex gap-3">
        <a href="/dashboard/tasks" class="text-sm text-primary hover:underline">Tasks ↗</a>
        <a href="/dashboard/teams" class="text-sm text-primary hover:underline">Teams ↗</a>
        <a href="/dashboard/missions" class="text-sm text-primary hover:underline">Missions ↗</a>
    </div>
</div>

<?= partial('partials.flash') ?>

<!-- ── Super admin user filter ──────────────────────────────────────────── -->
<?php if ($isSuperAdmin): ?>
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-4 mb-6">
        <form method="GET" action="/dashboard" class="flex items-center gap-4">
            <label class="text-sm font-medium text-dark whitespace-nowrap">View as user:</label>
            <select name="user_id"
                class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                           focus:border-primary outline-none duration-300 flex-1 max-w-xs"
                onchange="this.form.submit()">
                <option value="">— All users (global view) —</option>
                <?php foreach ($allUsers as $u): ?>
                    <option value="<?= $u['user_id'] ?>"
                        <?= $filterUserId === (int) $u['user_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['full_name']) ?>
                        (<?= htmlspecialchars($u['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($filterUserId): ?>
                <a href="/dashboard" class="text-xs text-muted hover:text-danger">✕ Clear filter</a>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<!-- ── Stat cards ────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php
    $cards = [
        ['label' => 'Total Tasks',   'value' => $taskStats['total'],       'icon' => 'fa-list-check',       'color' => 'text-primary',  'bg' => 'bg-primary-light'],
        ['label' => 'Open',          'value' => $taskStats['open'],         'icon' => 'fa-circle',           'color' => 'text-muted',    'bg' => 'bg-gray-100'],
        ['label' => 'In Progress',   'value' => $taskStats['in_progress'],  'icon' => 'fa-spinner',          'color' => 'text-primary',  'bg' => 'bg-primary-light'],
        ['label' => 'Overdue',       'value' => $taskStats['overdue'],      'icon' => 'fa-triangle-exclamation', 'color' => 'text-danger', 'bg' => 'bg-danger-light'],
        ['label' => 'Closed Tasks',  'value' => $taskStats['closed'],       'icon' => 'fa-circle-check',     'color' => 'text-success',  'bg' => 'bg-success-light'],
        ['label' => 'Active Teams',  'value' => $teamStats['active'],       'icon' => 'fa-users',            'color' => 'text-primary',  'bg' => 'bg-primary-light'],
        ['label' => 'Missions',      'value' => $missionStats['total'],     'icon' => 'fa-crosshairs',       'color' => 'text-warning',  'bg' => 'bg-warning-light'],
        ['label' => 'In Review',     'value' => $taskStats['review'],       'icon' => 'fa-magnifying-glass', 'color' => 'text-warning',  'bg' => 'bg-warning-light'],
    ];
    foreach ($cards as $card): ?>
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-4
                    flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 <?= $card['bg'] ?>">
                <i class="fa-solid <?= $card['icon'] ?> <?= $card['color'] ?>"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-dark leading-tight"><?= number_format((int)$card['value']) ?></p>
                <p class="text-xs text-muted"><?= $card['label'] ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ── Charts + recent tasks ─────────────────────────────────────────────── -->
<div class="grid grid-cols-3 gap-6 mb-6">

    <!-- Task status donut -->
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
        <h3 class="text-sm font-semibold text-dark mb-4">Task Status</h3>
        <div class="relative" style="height:200px">
            <canvas id="taskStatusChart"></canvas>
            <?= $_SESSION['just_logged_out']??'bad' ?>
        </div>
    </div>


    <!-- Recent tasks table -->
    <div class="col-span-2 bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-dark">Recent Tasks</h3>
            <a href="/dashboard/tasks" class="text-xs text-primary hover:underline">View all →</a>
        </div>

        <?php if (empty($recentTasks)): ?>
            <p class="text-sm text-muted">No tasks to show.</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($recentTasks as $t):
                    $pc = match ($t['priority']) {
                        'critical' => 'bg-danger-light text-danger',
                        'high'     => 'bg-warning-light text-warning',
                        'medium'   => 'bg-primary-light text-primary',
                        default    => 'bg-gray-100 text-muted',
                    };
                    $sc = match ($t['status']) {
                        'closed'      => 'text-muted line-through',
                        'in_progress' => 'text-primary',
                        'review'      => 'text-warning',
                        default       => 'text-dark',
                    };
                ?>
                    <div class="flex items-center gap-3 py-2 border-b border-b-color last:border-0">
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $pc ?>">
                            <?= $t['priority'] ?>
                        </span>
                        <a href="/tasks/<?= $t['id'] ?>" class="flex-1 text-sm <?= $sc ?> hover:text-primary truncate">
                            <?= htmlspecialchars($t['title']) ?>
                        </a>
                        <span class="text-xs text-muted shrink-0">
                            <?= htmlspecialchars($t['assignee_name'] ?? '—') ?>
                        </span>
                        <?php if ($t['due_date']): ?>
                            <?php $overdue = $t['status'] !== 'closed' && strtotime($t['due_date']) < time(); ?>
                            <span class="text-xs shrink-0 <?= $overdue ? 'text-danger font-semibold' : 'text-muted' ?>">
                                <?= date('M d', strtotime($t['due_date'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Missions summary ───────────────────────────────────────────────────── -->
<?php if (! empty($missions)): ?>
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-dark">Active Missions</h3>
            <a href="/dashboard/missions" class="text-xs text-primary hover:underline">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-muted border-b border-b-color">
                    <tr>
                        <th class="pb-2 text-left">Code</th>
                        <th class="pb-2 text-left">Title</th>
                        <th class="pb-2 text-left">Class.</th>
                        <th class="pb-2 text-left">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-b-color">
                    <?php foreach ($missions as $m):
                        $total    = (int)($m['total_tasks'] ?? 0);
                        $done     = (int)($m['completed_tasks'] ?? 0);
                        $pct      = $total > 0 ? round($done / $total * 100) : 0;
                        $badge = match ($m['classification']) {
                            'SECRET'       => 'text-danger bg-danger-light',
                            'CONFIDENTIAL' => 'text-warning bg-warning-light',
                            default        => 'text-primary bg-primary-light',
                        };
                    ?>
                        <tr>
                            <td class="py-2 font-mono text-xs text-muted"><?= htmlspecialchars($m['m_code']) ?></td>
                            <td class="py-2">
                                <a href="/missions/<?= $m['id'] ?>" class="text-dark hover:text-primary">
                                    <?= htmlspecialchars($m['title']) ?>
                                </a>
                            </td>
                            <td class="py-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $badge ?>">
                                    <?= $m['classification'] ?>
                                </span>
                            </td>
                            <td class="py-2 min-w-[120px]">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-primary h-1.5 rounded-full" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <span class="text-xs text-muted"><?= $pct ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- ── Super admin: per-user table ───────────────────────────────────────── -->
<?php if ($isSuperAdmin && ! empty($userTaskStats)): ?>
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5">
        <h3 class="text-sm font-semibold text-dark mb-4">All Users — Task Summary</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="usersTable">
                <thead class="text-xs uppercase text-muted border-b border-b-color">
                    <tr>
                        <th class="pb-2 text-left">User</th>
                        <th class="pb-2 text-left">Role</th>
                        <th class="pb-2 text-center">Total</th>
                        <th class="pb-2 text-center">Open</th>
                        <th class="pb-2 text-center">In Progress</th>
                        <th class="pb-2 text-center">Review</th>
                        <th class="pb-2 text-center">Closed</th>
                        <th class="pb-2 text-center">Overdue</th>
                        <th class="pb-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-b-color">
                    <?php foreach ($allUsers as $u):
                        $us = $userTaskStats[$u['user_id']] ?? [];
                    ?>
                        <tr>
                            <td class="py-2">
                                <p class="font-medium text-dark"><?= htmlspecialchars($u['full_name']) ?></p>
                                <p class="text-xs text-muted"><?= htmlspecialchars($u['email']) ?></p>
                            </td>
                            <td class="py-2 text-xs text-muted"><?= htmlspecialchars($u['role_name'] ?? '') ?></td>
                            <td class="py-2 text-center font-semibold"><?= $us['total'] ?? 0 ?></td>
                            <td class="py-2 text-center text-muted"><?= $us['open'] ?? 0 ?></td>
                            <td class="py-2 text-center text-primary"><?= $us['in_progress'] ?? 0 ?></td>
                            <td class="py-2 text-center text-warning"><?= $us['review'] ?? 0 ?></td>
                            <td class="py-2 text-center text-success"><?= $us['closed'] ?? 0 ?></td>
                            <td class="py-2 text-center <?= ($us['overdue'] ?? 0) > 0 ? 'text-danger font-semibold' : 'text-muted' ?>">
                                <?= $us['overdue'] ?? 0 ?>
                            </td>
                            <td class="py-2">
                                <a href="/dashboard?user_id=<?= $u['user_id'] ?>"
                                    class="text-xs text-primary hover:underline">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- ── Activity feed ──────────────────────────────────────────────────────── -->
<?php if (! empty($recentActivity)): ?>
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5 mt-6">
        <h3 class="text-sm font-semibold text-dark mb-4">Recent Activity</h3>
        <ul class="space-y-3 text-sm">
            <?php foreach ($recentActivity as $log): ?>
                <li class="flex items-start gap-3 text-body-color">
                    <span class="w-2 h-2 rounded-full bg-primary mt-1.5 shrink-0"></span>
                    <div>
                        <span class="font-medium text-dark">
                            <?= htmlspecialchars($log['actor_name'] ?? 'System') ?>
                        </span>
                        <span class="ml-1"><?= htmlspecialchars(str_replace('_', ' ', $log['action'])) ?></span>
                        <span class="text-xs text-muted ml-2">
                            <?= date('M d, H:i', strtotime($log['created_at'])) ?>
                        </span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<script>
    // Task status donut chart
    (function() {
        const ctx = document.getElementById('taskStatusChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'In Progress', 'Review', 'Closed'],
                datasets: [{
                    data: [
                        <?= (int)($taskStats['open']        ?? 0) ?>,
                        <?= (int)($taskStats['in_progress']  ?? 0) ?>,
                        <?= (int)($taskStats['review']       ?? 0) ?>,
                        <?= (int)($taskStats['closed']       ?? 0) ?>,
                    ],
                    backgroundColor: ['#e5e7eb', '#3b82f6', '#f59e0b', '#22c55e'],
                    borderWidth: 0,
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11
                            }
                        }
                    },
                },
            },
        });
    })();
</script>