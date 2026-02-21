<?php $layout = 'app'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Missions Overview</h2>
        <p class="text-sm text-muted mt-0.5"><a href="/dashboard" class="hover:text-primary">← Dashboard</a></p>
    </div>
    <?php if (has_any_role(['admin', 'manager', 'super_admin'])): ?>
        <a href="/missions/create" class="btn btn-primary text-sm px-4 py-2">+ New Mission</a>
    <?php endif; ?>
</div>

<?= partial('partials.flash') ?>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-4 text-center">
        <p class="text-3xl font-bold text-dark"><?= (int)$missionStats['total'] ?></p>
        <p class="text-xs uppercase text-muted mt-1">Total Missions</p>
    </div>
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-4 text-center">
        <p class="text-3xl font-bold text-primary"><?= (int)$missionStats['with_tasks'] ?></p>
        <p class="text-xs uppercase text-muted mt-1">With Active Tasks</p>
    </div>
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-4 text-center">
        <p class="text-3xl font-bold text-success"><?= (int)$missionStats['completed'] ?></p>
        <p class="text-xs uppercase text-muted mt-1">Fully Completed</p>
    </div>
</div>

<!-- Filters -->
<div class="flex gap-3 mb-4">
    <input type="text" id="missionSearch" placeholder="Search missions…"
        class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                  focus:border-primary outline-none duration-300 flex-1 max-w-xs">
    <select id="classFilter"
        class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                   focus:border-primary outline-none duration-300">
        <option value="">All classifications</option>
        <option value="PUBLIC">Public</option>
        <option value="CONFIDENTIAL">Confidential</option>
        <option value="SECRET">Secret</option>
    </select>
</div>

<!-- Table -->
<div class="bg-white dark:bg-dark-card border border-b-color rounded-lg overflow-hidden">
    <table id="missionsTable" class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-dark text-xs uppercase text-muted">
            <tr>
                <th class="px-4 py-3 text-left">Code</th>
                <th class="px-4 py-3 text-left">Title</th>
                <th class="px-4 py-3 text-left">Classification</th>
                <th class="px-4 py-3 text-center">Tasks</th>
                <th class="px-4 py-3 text-left">Progress</th>
                <th class="px-4 py-3 text-left">Start</th>
                <th class="px-4 py-3 text-left">End</th>
                <th class="px-4 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-b-color">
            <?php foreach ($missions as $m):
                $total = (int)($m['total_tasks'] ?? 0);
                $done  = (int)($m['completed_tasks'] ?? 0);
                $pct   = $total > 0 ? round($done / $total * 100) : 0;
                $badge = match ($m['classification']) {
                    'SECRET'       => 'text-danger bg-danger-light',
                    'CONFIDENTIAL' => 'text-warning bg-warning-light',
                    default        => 'text-primary bg-primary-light',
                };
            ?>
                <tr data-class="<?= $m['classification'] ?>">
                    <td class="px-4 py-3 font-mono text-xs text-muted">
                        <?= htmlspecialchars($m['m_code']) ?>
                    </td>
                    <td class="px-4 py-3">
                        <a href="/missions/<?= $m['id'] ?>"
                            class="font-medium text-dark hover:text-primary">
                            <?= htmlspecialchars($m['title']) ?>
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $badge ?>">
                            <?= $m['classification'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm"><?= $total ?></td>
                    <td class="px-4 py-3 min-w-[130px]">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                <div class="bg-primary h-1.5 rounded-full" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="text-xs text-muted w-8 text-right"><?= $pct ?>%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-muted">
                        <?= $m['start_time'] ? date('M d, Y', strtotime($m['start_time'])) : '—' ?>
                    </td>
                    <td class="px-4 py-3 text-xs text-muted">
                        <?= $m['end_time'] ? date('M d, Y', strtotime($m['end_time'])) : '—' ?>
                    </td>
                    <td class="px-4 py-3">
                        <a href="/missions/<?= $m['id'] ?>" class="text-xs text-primary hover:underline">View</a>
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
        const table = $('#missionsTable').DataTable({
            pageLength: 20,
            dom: '<"flex items-center justify-between px-4 py-3 border-b border-b-color"lf>rtip',
        });

        $('#missionSearch').on('input', function() {
            table.search(this.value).draw();
        });

        // Classification filter
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const cls = document.getElementById('classFilter').value;
            if (!cls) return true;
            const row = table.row(dataIndex).node();
            return $(row).data('class') === cls;
        });

        $('#classFilter').on('change', function() {
            table.draw();
        });
    });
</script>