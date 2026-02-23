<?php $layout = 'app'; ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">My Reports</h2>
        <p class="text-sm text-muted mt-0.5">Activity reports you have submitted</p>
    </div>
    <a href="/reports/create" class="btn btn-primary text-sm px-4 py-2">+ New Report</a>
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

<!-- Report list -->
<?php if (empty($reports)): ?>
    <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl px-6 py-16 text-center">
        <i class="fa-solid fa-file-circle-plus text-4xl text-muted mb-4"></i>
        <p class="text-base font-medium text-dark mb-1">No reports yet</p>
        <p class="text-sm text-muted mb-5">Submit your first activity report for your team lead to review.</p>
        <a href="/reports/create" class="btn btn-primary text-sm px-6 py-2">Create Report</a>
    </div>
<?php else: ?>
    <div class="space-y-3">
        <?php foreach ($reports as $r):
            $sc = match ($r['status']) {
                'approved'     => ['badge' => 'text-success bg-success-light', 'border' => 'border-l-4 border-success'],
                'rejected'     => ['badge' => 'text-danger  bg-danger-light',  'border' => 'border-l-4 border-danger'],
                'under_review' => ['badge' => 'text-warning bg-warning-light', 'border' => 'border-l-4 border-warning'],
                'submitted'    => ['badge' => 'text-primary bg-primary-light', 'border' => 'border-l-4 border-primary'],
                default        => ['badge' => 'text-muted   bg-gray-100',      'border' => ''],
            };
        ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-5 flex items-start gap-4 <?= $sc['border'] ?>">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-1">
                        <a href="/reports/<?= $r['id'] ?>"
                            class="text-base font-semibold text-dark hover:text-primary truncate">
                            <?= htmlspecialchars($r['title']) ?>
                        </a>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded shrink-0 <?= $sc['badge'] ?>">
                            <?= str_replace('_', ' ', $r['status']) ?>
                        </span>
                        <?php if ($r['type'] === 'lead_report'): ?>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-primary-light text-primary shrink-0">
                                Lead Report
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-muted">
                        <span><i class="fa-solid fa-users mr-1"></i><?= htmlspecialchars($r['team_name']) ?></span>
                        <?php if ($r['mission_title']): ?>
                            <span><i class="fa-solid fa-crosshairs mr-1"></i><?= htmlspecialchars($r['mission_title']) ?></span>
                        <?php endif; ?>
                        <span><i class="fa-solid fa-clock mr-1"></i>
                            <?= $r['submitted_at'] ? date('M d, Y H:i', strtotime($r['submitted_at'])) : 'Not submitted' ?>
                        </span>
                    </div>
                    <?php if ($r['status'] === 'submitted'): ?>
                        <p class="text-xs text-primary mt-1.5">
                            <i class="fa-solid fa-hourglass-half mr-1"></i>Awaiting team lead review
                        </p>
                    <?php elseif ($r['status'] === 'rejected'): ?>
                        <p class="text-xs text-danger mt-1.5">
                            <i class="fa-solid fa-circle-xmark mr-1"></i>Rejected — view for feedback
                        </p>
                    <?php elseif ($r['status'] === 'approved'): ?>
                        <p class="text-xs text-success mt-1.5">
                            <i class="fa-solid fa-circle-check mr-1"></i>Approved
                        </p>
                    <?php endif; ?>
                </div>
                <div class="flex gap-2 shrink-0">
                    <?php if (in_array($r['status'], ['draft', 'submitted'], true)): ?>
                        <a href="/reports/<?= $r['id'] ?>/edit"
                            class="text-xs border border-b-color rounded-lg px-3 py-1.5 text-muted hover:text-dark transition-colors">
                            Edit
                        </a>
                    <?php endif; ?>
                    <a href="/reports/<?= $r['id'] ?>"
                        class="text-xs bg-primary text-white rounded-lg px-3 py-1.5 hover:bg-hover-primary transition-colors">
                        View →
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>