<?php $layout = 'app'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Reports Inbox</h2>
        <p class="text-sm text-muted mt-0.5">Member reports submitted to your team</p>
    </div>
    <a href="/reports/create?type=lead_report"
        class="btn btn-primary text-sm px-4 py-2">+ Report to Manager</a>
</div>

<?= partial('partials.flash') ?>

<!-- Stats row -->
<div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-6">
    <?php
    $pills = [
        ['label' => 'Total',        'key' => 'total',        'class' => 'text-dark bg-gray-100'],
        ['label' => 'Draft',        'key' => 'draft',        'class' => 'text-muted bg-gray-100'],
        ['label' => 'Submitted',    'key' => 'submitted',    'class' => 'text-primary bg-primary-light'],
        ['label' => 'Under Review', 'key' => 'under_review', 'class' => 'text-warning bg-warning-light'],
        ['label' => 'Approved',     'key' => 'approved',     'class' => 'text-success bg-success-light'],
        ['label' => 'Rejected',     'key' => 'rejected',     'class' => 'text-danger  bg-danger-light'],
    ];
    foreach ($pills as $p): ?>
        <div class="rounded-lg p-3 text-center <?= $p['class'] ?>">
            <p class="text-xl font-bold"><?= (int)($stats[$p['key']] ?? 0) ?></p>
            <p class="text-[10px] uppercase tracking-wide"><?= $p['label'] ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- ── Member reports inbox ─────────────────────────────────────────────── -->
<div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden mb-6">
    <div class="flex items-center justify-between px-5 py-3 border-b border-b-color">
        <h3 class="text-sm font-semibold text-dark">Team Member Reports</h3>
        <input type="text" id="memberSearch" placeholder="Search…"
            class="form-control h-9 w-48 border border-b-color rounded-md px-3 text-xs
                      focus:border-primary outline-none duration-300">
    </div>
    <?php if (empty($reports)): ?>
        <div class="px-5 py-10 text-center text-sm text-muted">
            No reports submitted by team members yet.
        </div>
    <?php else: ?>
        <table id="memberTable" class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-dark text-xs uppercase text-muted">
                <tr>
                    <th class="px-4 py-3 text-left">Report</th>
                    <th class="px-4 py-3 text-left">Member</th>
                    <th class="px-4 py-3 text-left">Mission</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Submitted</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-b-color">
                <?php foreach ($reports as $r):
                    $sc = match ($r['status']) {
                        'approved'     => 'text-success bg-success-light',
                        'rejected'     => 'text-danger  bg-danger-light',
                        'under_review' => 'text-warning bg-warning-light',
                        'submitted'    => 'text-primary bg-primary-light',
                        default        => 'text-muted   bg-gray-100',
                    };
                    $isNew = $r['status'] === 'submitted';
                ?>
                    <tr class="<?= $isNew ? 'bg-blue-50 dark:bg-dark' : '' ?>">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <?php if ($isNew): ?>
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>
                                <?php endif; ?>
                                <a href="/reports/<?= $r['id'] ?>"
                                    class="font-medium text-dark hover:text-primary">
                                    <?= htmlspecialchars($r['title']) ?>
                                </a>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted"><?= htmlspecialchars($r['author_name']) ?></td>
                        <td class="px-4 py-3 text-muted text-xs">
                            <?= htmlspecialchars($r['mission_title'] ?? '—') ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $sc ?>">
                                <?= str_replace('_', ' ', $r['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted">
                            <?= $r['submitted_at'] ? date('M d, H:i', strtotime($r['submitted_at'])) : '—' ?>
                        </td>
                        <td class="px-4 py-3">
                            <a href="/reports/<?= $r['id'] ?>" class="text-xs text-primary hover:underline">
                                <?= $r['status'] === 'submitted' ? 'Review →' : 'View →' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- ── My sent lead reports ─────────────────────────────────────────────── -->
<?php if (! empty($myReports)): ?>
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-b-color">
            <h3 class="text-sm font-semibold text-dark">My Reports to Manager</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-dark text-xs uppercase text-muted">
                <tr>
                    <th class="px-4 py-3 text-left">Report</th>
                    <th class="px-4 py-3 text-left">Mission</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Submitted</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-b-color">
                <?php foreach ($myReports as $r):
                    if ($r['type'] !== 'lead_report') continue;
                    $sc = match ($r['status']) {
                        'approved' => 'text-success bg-success-light',
                        'rejected' => 'text-danger  bg-danger-light',
                        'actioned' => 'text-primary bg-primary-light',
                        default    => 'text-muted   bg-gray-100',
                    };
                ?>
                    <tr>
                        <td class="px-4 py-3">
                            <a href="/reports/<?= $r['id'] ?>"
                                class="font-medium text-dark hover:text-primary">
                                <?= htmlspecialchars($r['title']) ?>
                            </a>
                        </td>
                        <td class="px-4 py-3 text-muted text-xs">
                            <?= htmlspecialchars($r['mission_title'] ?? '—') ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $sc ?>">
                                <?= str_replace('_', ' ', $r['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted">
                            <?= $r['submitted_at'] ? date('M d, Y', strtotime($r['submitted_at'])) : '—' ?>
                        </td>
                        <td class="px-4 py-3">
                            <a href="/reports/<?= $r['id'] ?>" class="text-xs text-primary hover:underline">View →</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
    $(function() {
        const table = $('#memberTable').DataTable({
            pageLength: 20,
            dom: 'rtip',
            order: [
                [4, 'desc']
            ],
        });
        $('#memberSearch').on('input', function() {
            table.search(this.value).draw();
        });
    });
</script>