<?php $layout = 'app'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Teams Overview</h2>
        <p class="text-sm text-muted mt-0.5"><a href="/dashboard" class="hover:text-primary">← Dashboard</a></p>
    </div>
    <?php if (has_any_role(['admin', 'manager', 'super_admin'])): ?>
        <a href="/teams/create" class="btn btn-primary text-sm px-4 py-2">+ New Team</a>
    <?php endif; ?>
</div>

<?= partial('partials.flash') ?>

<!-- Stat cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php
    $cards = [
        ['label' => 'Total Teams',    'value' => $teamStats['total'],    'color' => 'text-dark bg-gray-100'],
        ['label' => 'Active',         'value' => $teamStats['active'],   'color' => 'text-success bg-success-light'],
        ['label' => 'Inactive',       'value' => $teamStats['inactive'], 'color' => 'text-warning bg-warning-light'],
        ['label' => 'Archived',       'value' => $teamStats['archived'], 'color' => 'text-muted bg-gray-100'],
    ];
    foreach ($cards as $c): ?>
        <div class="rounded-lg p-4 text-center <?= $c['color'] ?>">
            <p class="text-2xl font-bold"><?= (int)$c['value'] ?></p>
            <p class="text-xs uppercase tracking-wide"><?= $c['label'] ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Search -->
<div class="mb-4">
    <input type="text" id="teamSearch" placeholder="Search teams…"
        class="form-control w-full md:w-80 h-10 border border-b-color rounded-md px-3 text-sm
                  focus:border-primary outline-none duration-300">
</div>

<!-- Teams table (DataTables) -->
<div class="bg-white dark:bg-dark-card border border-b-color rounded-lg overflow-hidden">
    <table id="teamsTable" class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-dark text-xs uppercase text-muted">
            <tr>
                <th class="px-4 py-3 text-left">Team</th>
                <th class="px-4 py-3 text-left">Lead</th>
                <th class="px-4 py-3 text-center">Members</th>
                <th class="px-4 py-3 text-center">Open</th>
                <th class="px-4 py-3 text-center">In Progress</th>
                <th class="px-4 py-3 text-center">Closed</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-b-color">
            <?php foreach ($teams as $team):
                $statusClass = match ($team['status']) {
                    'inactive' => 'text-warning bg-warning-light',
                    'archived' => 'text-muted bg-gray-100',
                    default    => 'text-success bg-success-light',
                };
                $ts = $team['task_stats'] ?? [];
                $total = array_sum($ts);
                $done  = (int)($ts['closed'] ?? 0);
                $pct   = $total > 0 ? round($done / $total * 100) : 0;
            ?>
                <tr>
                    <td class="px-4 py-3">
                        <a href="/teams/<?= $team['id'] ?>"
                            class="font-medium text-dark hover:text-primary">
                            <?= htmlspecialchars($team['name']) ?>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-muted text-xs">
                        <?= htmlspecialchars($team['lead_name'] ?? '—') ?>
                    </td>
                    <td class="px-4 py-3 text-center text-sm">
                        <?= (int)($team['member_count'] ?? 0) ?>
                    </td>
                    <td class="px-4 py-3 text-center text-muted text-sm">
                        <?= (int)($ts['open'] ?? 0) ?>
                    </td>
                    <td class="px-4 py-3 text-center text-primary text-sm">
                        <?= (int)($ts['in_progress'] ?? 0) ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                <div class="bg-primary h-1.5 rounded-full" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="text-xs text-muted"><?= $pct ?>%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $statusClass ?>">
                            <?= $team['status'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2 items-center">
                            <a href="/teams/<?= $team['id'] ?>" class="text-xs text-primary hover:underline">View</a>
                            <?php if (has_any_role(['admin', 'manager', 'super_admin'])): ?>
                                <a href="/teams/<?= $team['id'] ?>/edit" class="text-xs text-muted hover:text-dark">Edit</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script>
    $(function() {
        const table = $('#teamsTable').DataTable({
            pageLength: 20,
            language: {
                searchPlaceholder: 'Search…',
                search: ''
            },
            dom: '<"flex items-center justify-between px-4 py-3 border-b border-b-color"lf>rtip',
        });

        // Wire external search input
        document.getElementById('teamSearch').addEventListener('input', function() {
            table.search(this.value).draw();
        });
    });
</script>