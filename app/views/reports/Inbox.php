<?php $layout = 'app'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Reports Inbox</h2>
        <p class="text-sm text-muted mt-0.5">
            Team lead reports submitted for your review and action
        </p>
    </div>
</div>

<?= partial('partials.flash') ?>

<!-- Stats -->
<div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-6">
    <?php
    $pills = [
        ['label' => 'Total',        'key' => 'total',        'class' => 'text-dark bg-gray-100'],
        ['label' => 'Draft',        'key' => 'draft',        'class' => 'text-muted bg-gray-100'],
        ['label' => 'Submitted',    'key' => 'submitted',    'class' => 'text-primary bg-primary-light'],
        ['label' => 'Under Review', 'key' => 'under_review', 'class' => 'text-warning bg-warning-light'],
        ['label' => 'Approved',     'key' => 'approved',     'class' => 'text-success bg-success-light'],
        ['label' => 'Actioned',     'key' => 'actioned',     'class' => 'text-primary bg-primary-light'],
    ];
    foreach ($pills as $p): ?>
        <div class="rounded-lg p-3 text-center <?= $p['class'] ?>">
            <p class="text-xl font-bold"><?= (int)($stats[$p['key']] ?? 0) ?></p>
            <p class="text-[10px] uppercase tracking-wide"><?= $p['label'] ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Filter bar -->
<div class="flex gap-3 mb-4">
    <input type="text" id="inboxSearch" placeholder="Search reports…"
        class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                  focus:border-primary outline-none duration-300 flex-1 max-w-xs">
    <select id="inboxStatus"
        class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                   focus:border-primary outline-none duration-300">
        <option value="">All statuses</option>
        <option value="submitted">Submitted</option>
        <option value="under_review">Under Review</option>
        <option value="approved">Approved</option>
        <option value="actioned">Actioned</option>
        <option value="rejected">Rejected</option>
    </select>
</div>

<!-- Reports table -->
<div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
    <table id="inboxTable" class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-dark text-xs uppercase text-muted">
            <tr>
                <th class="px-4 py-3 text-left">Report</th>
                <th class="px-4 py-3 text-left">From</th>
                <th class="px-4 py-3 text-left">Team</th>
                <th class="px-4 py-3 text-left">Mission</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Submitted</th>
                <th class="px-4 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-b-color">
            <?php if (empty($reports)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-muted">
                        <i class="fa-solid fa-inbox text-3xl mb-3 block opacity-30"></i>
                        No reports in your inbox.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($reports as $report):
                    $sc = match ($report['status']) {
                        'approved'     => 'text-success bg-success-light',
                        'rejected'     => 'text-danger bg-danger-light',
                        'actioned'     => 'text-primary bg-primary-light',
                        'under_review' => 'text-warning bg-warning-light',
                        'submitted'    => 'text-primary bg-primary-light',
                        default        => 'text-muted bg-gray-100',
                    };
                    $needsAction = in_array($report['status'], ['submitted', 'under_review'], true);
                ?>
                    <tr data-status="<?= $report['status'] ?>">
                        <td class="px-4 py-3">
                            <a href="/reports/<?= $report['id'] ?>"
                                class="font-medium text-dark hover:text-primary flex items-center gap-2">
                                <?= htmlspecialchars($report['title']) ?>
                                <?php if ($needsAction): ?>
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <?= htmlspecialchars($report['author_name']) ?>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted">
                            <?= htmlspecialchars($report['team_name'] ?? '—') ?>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted max-w-[120px] truncate">
                            <?= $report['mission_title'] ? htmlspecialchars($report['mission_title']) : '—' ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $sc ?>">
                                <?= str_replace('_', ' ', $report['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted">
                            <?= $report['submitted_at']
                                ? date('M d, Y', strtotime($report['submitted_at']))
                                : '—' ?>
                        </td>
                        <td class="px-4 py-3">
                            <a href="/reports/<?= $report['id'] ?>"
                                class="text-xs <?= $needsAction ? 'text-primary font-semibold' : 'text-muted' ?> hover:underline">
                                <?= $needsAction ? 'Review →' : 'View' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    $(function() {
        const table = $('#inboxTable').DataTable({
            pageLength: 25,
            order: [
                [5, 'desc']
            ],
            dom: '<"flex items-center justify-between px-4 py-3 border-b border-b-color"lf>rtip',
        });

        $('#inboxSearch').on('input', function() {
            table.search(this.value).draw();
        });
        $('#inboxStatus').on('change', function() {
            const status = this.value;
            $.fn.dataTable.ext.search = [];
            if (status) {
                $.fn.dataTable.ext.search.push(function(settings, data, idx) {
                    return $(table.row(idx).node()).data('status') === status;
                });
            }
            table.draw();
        });
    });
</script>