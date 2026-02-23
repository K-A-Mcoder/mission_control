<?php $layout = 'app'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Reports Manager Inbox</h2>
        <p class="text-sm text-muted mt-0.5">Lead reports awaiting your review or action</p>
    </div>
    <div class="flex gap-2">
        <input type="text" id="inboxSearch" placeholder="Search reports…"
            class="form-control h-9 w-48 border border-b-color rounded-md px-3 text-xs
                      focus:border-primary outline-none duration-300">
        <select id="statusFilter"
            class="form-control h-9 border border-b-color rounded-md px-3 text-xs
                       focus:border-primary outline-none duration-300">
            <option value="">All statuses</option>
            <option value="submitted">Submitted</option>
            <option value="under_review">Under Review</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="actioned">Actioned</option>
        </select>
    </div>
</div>

<?= partial('partials.flash') ?>

<!-- Stats -->
<div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-6">
    <?php
    $pills = [
        ['label' => 'Total',     'key' => 'total',        'class' => 'text-dark bg-gray-100'],
        ['label' => 'Submitted', 'key' => 'submitted',    'class' => 'text-primary bg-primary-light'],
        ['label' => 'Reviewing', 'key' => 'under_review', 'class' => 'text-warning bg-warning-light'],
        ['label' => 'Approved',  'key' => 'approved',     'class' => 'text-success bg-success-light'],
        ['label' => 'Actioned',  'key' => 'actioned',     'class' => 'text-primary bg-primary-light'],
        ['label' => 'Rejected',  'key' => 'rejected',     'class' => 'text-danger  bg-danger-light'],
    ];
    foreach ($pills as $p): ?>
        <div class="rounded-lg p-3 text-center <?= $p['class'] ?>">
            <p class="text-xl font-bold"><?= (int)($stats[$p['key']] ?? 0) ?></p>
            <p class="text-[10px] uppercase tracking-wide"><?= $p['label'] ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Reports table -->
<div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
    <?php if (empty($reports)): ?>
        <div class="px-5 py-16 text-center text-sm text-muted">
            No reports in your inbox yet.
        </div>
    <?php else: ?>
        <table id="inboxTable" class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-dark text-xs uppercase text-muted">
                <tr>
                    <th class="px-4 py-3 text-left">Report</th>
                    <th class="px-4 py-3 text-left">Lead</th>
                    <th class="px-4 py-3 text-left">Team</th>
                    <th class="px-4 py-3 text-left">Mission</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Received</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-b-color">
                <?php foreach ($reports as $r):
                    $sc = match ($r['status']) {
                        'approved'     => 'text-success bg-success-light',
                        'rejected'     => 'text-danger  bg-danger-light',
                        'under_review' => 'text-warning bg-warning-light',
                        'actioned'     => 'text-primary bg-primary-light',
                        'submitted'    => 'text-primary bg-primary-light',
                        default        => 'text-muted   bg-gray-100',
                    };
                    $needsAction = $r['status'] === 'submitted';
                ?>
                    <tr data-status="<?= $r['status'] ?>"
                        class="<?= $needsAction ? 'bg-blue-50 dark:bg-dark' : '' ?>">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <?php if ($needsAction): ?>
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>
                                <?php endif; ?>
                                <a href="/reports/<?= $r['id'] ?>"
                                    class="font-medium text-dark hover:text-primary">
                                    <?= htmlspecialchars($r['title']) ?>
                                </a>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted"><?= htmlspecialchars($r['author_name']) ?></td>
                        <td class="px-4 py-3 text-muted text-xs"><?= htmlspecialchars($r['team_name']) ?></td>
                        <td class="px-4 py-3 text-muted text-xs">
                            <?= htmlspecialchars($r['mission_title'] ?? '—') ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $sc ?>">
                                <?= str_replace('_', ' ', $r['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted">
                            <?= $r['submitted_at'] ? date('M d, Y H:i', strtotime($r['submitted_at'])) : '—' ?>
                        </td>
                        <td class="px-4 py-3">
                            <a href="/reports/<?= $r['id'] ?>"
                                class="text-xs text-primary hover:underline font-medium">
                                <?= $needsAction ? 'Review →' : 'View →' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
    $(function() {
        const table = $('#inboxTable').DataTable({
            pageLength: 20,
            dom: 'rtip',
            order: [
                [5, 'desc']
            ],
        });

        $('#inboxSearch').on('input', function() {
            table.search(this.value).draw();
        });

        $.fn.dataTable.ext.search.push(function(settings, data, i) {
            const f = $('#statusFilter').val();
            if (!f) return true;
            return $(table.row(i).node()).data('status') === f;
        });
        $('#statusFilter').on('change', function() {
            table.draw();
        });
    });
</script>