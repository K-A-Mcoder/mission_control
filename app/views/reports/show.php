<?php $layout = 'app';

$statusColor = match ($report['status']) {
    'approved'     => 'text-success bg-success-light border-success-light',
    'actioned'     => 'text-primary bg-primary-light border-primary-light',
    'rejected'     => 'text-danger  bg-danger-light  border-danger-light',
    'under_review' => 'text-warning bg-warning-light border-warning-light',
    'submitted'    => 'text-primary bg-primary-light border-primary-light',
    default        => 'text-muted   bg-gray-100       border-gray-200',
};

$typeLabel = $report['type'] === 'lead_report' ? 'Lead → Manager Report' : 'Member Activity Report';
?>

<!-- Header -->
<div class="flex items-start justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-2 text-sm">
            <a href="/reports" class="text-muted hover:text-dark">← Reports</a>
            <span class="text-muted">/</span>
            <span class="text-muted"><?= htmlspecialchars($typeLabel) ?></span>
        </div>
        <h2 class="text-xl font-semibold text-dark flex flex-wrap items-center gap-2">
            <?= htmlspecialchars($report['title']) ?>
            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded border <?= $statusColor ?>">
                <?= str_replace('_', ' ', $report['status']) ?>
            </span>
        </h2>
        <div class="text-xs text-muted mt-1 flex items-center gap-3">
            <span>By <strong class="text-dark"><?= htmlspecialchars($report['author_name']) ?></strong></span>
            <span>·</span>
            <span>Team: <a href="/teams/<?= $report['team_id'] ?>"
                    class="text-primary hover:underline"><?= htmlspecialchars($report['team_name']) ?></a></span>
            <?php if ($report['mission_title']): ?>
                <span>·</span>
                <span>Mission: <a href="/missions/<?= $report['mission_id'] ?>"
                        class="text-primary hover:underline"><?= htmlspecialchars($report['mission_title']) ?></a></span>
            <?php endif; ?>
            <span>·</span>
            <span><?= $report['submitted_at'] ? 'Submitted ' . date('M d, Y H:i', strtotime($report['submitted_at'])) : 'Draft' ?></span>
        </div>
    </div>

    <div class="flex gap-2 shrink-0">
        <?php if ($isAuthor && in_array($report['status'], ['draft', 'submitted'], true)): ?>
            <a href="/reports/<?= $report['id'] ?>/edit"
                class="btn btn-secondary text-sm px-4 py-2">Edit</a>
        <?php endif; ?>
        <?php if ($isAuthor && in_array($report['status'], ['draft', 'submitted'], true)): ?>
            <form action="/reports/<?= $report['id'] ?>/delete" method="POST"
                onsubmit="return confirm('Delete this report?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger text-sm px-4 py-2">Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?= partial('partials.flash') ?>

<div class="grid grid-cols-3 gap-6">

    <!-- ── Report body ────────────────────────────────────────────────────── -->
    <div class="col-span-2 space-y-5">

        <?php
        $sections = [
            'summary'         => ['label' => 'Activity Summary',    'icon' => 'fa-clipboard-list'],
            'challenges'      => ['label' => 'Challenges Faced',    'icon' => 'fa-triangle-exclamation'],
            'actions_taken'   => ['label' => 'Actions Taken',       'icon' => 'fa-gears'],
            'next_steps'      => ['label' => 'Next Steps',          'icon' => 'fa-forward'],
            'recommendations' => ['label' => 'Recommendations',     'icon' => 'fa-lightbulb'],
            'context_info'    => ['label' => 'Additional Context',  'icon' => 'fa-circle-info'],
            'attachments_note' => ['label' => 'Attachments / Notes', 'icon' => 'fa-paperclip'],
        ];
        foreach ($sections as $key => $meta):
            if (empty($report[$key])) continue;
        ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-5">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-dark mb-3">
                    <i class="fa-solid <?= $meta['icon'] ?> text-primary text-xs w-4"></i>
                    <?= $meta['label'] ?>
                </h3>
                <p class="text-sm text-body-color leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($report[$key]) ?></p>
            </div>
        <?php endforeach; ?>

        <!-- ── Review / comment thread ────────────────────────────────────── -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-b-color">
                <h3 class="text-sm font-semibold text-dark">
                    Review Thread
                    <span class="text-xs font-normal text-muted ml-1">(<?= count($reviews) ?>)</span>
                </h3>
            </div>

            <?php if (! empty($reviews)): ?>
                <ul class="divide-y divide-b-color">
                    <?php foreach ($reviews as $rv):
                        $actionBadge = match ($rv['action']) {
                            'approved'  => 'text-success bg-success-light',
                            'rejected'  => 'text-danger  bg-danger-light',
                            'actioned'  => 'text-primary bg-primary-light',
                            'reviewed'  => 'text-warning bg-warning-light',
                            default     => 'text-muted   bg-gray-100',
                        };
                    ?>
                        <li class="px-5 py-4">
                            <div class="flex items-start justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-dark">
                                        <?= htmlspecialchars($rv['reviewer_name']) ?>
                                    </p>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $actionBadge ?>">
                                        <?= $rv['action'] ?>
                                    </span>
                                </div>
                                <p class="text-xs text-muted">
                                    <?= date('M d, Y H:i', strtotime($rv['created_at'])) ?>
                                </p>
                            </div>
                            <?php if ($rv['comment']): ?>
                                <p class="text-sm text-body-color mt-1 leading-relaxed">
                                    <?= nl2br(htmlspecialchars($rv['comment'])) ?>
                                </p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="px-5 py-6 text-center text-sm text-muted">
                    No reviews yet.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Sidebar ────────────────────────────────────────────────────────── -->
    <div class="space-y-5">

        <!-- Status card -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-5 text-sm space-y-3">
            <h3 class="text-sm font-semibold text-dark">Report Details</h3>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Type</p>
                <p class="text-dark"><?= htmlspecialchars($typeLabel) ?></p>
            </div>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Status</p>
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded border <?= $statusColor ?>">
                    <?= str_replace('_', ' ', $report['status']) ?>
                </span>
            </div>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Author</p>
                <p class="text-dark"><?= htmlspecialchars($report['author_name']) ?></p>
                <p class="text-xs text-muted"><?= htmlspecialchars($report['author_email']) ?></p>
            </div>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Submitted</p>
                <p class="text-dark">
                    <?= $report['submitted_at'] ? date('M d, Y H:i', strtotime($report['submitted_at'])) : '—' ?>
                </p>
            </div>
            <div>
                <p class="text-xs text-muted uppercase tracking-wide mb-1">Created</p>
                <p class="text-dark"><?= date('M d, Y', strtotime($report['created_at'])) ?></p>
            </div>
        </div>

        <!-- Review action panel (for team lead OR manager) -->
        <?php if ($canReview): ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-5">
                <h3 class="text-sm font-semibold text-dark mb-4">Take Action</h3>
                <form action="/reports/<?= $report['id'] ?>/review" method="POST" class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-dark">Action</label>
                        <div class="grid grid-cols-2 gap-2">
                            <?php foreach (['reviewed', 'approved', 'rejected', 'actioned'] as $act):
                                $c = match ($act) {
                                    'approved' => 'border-success text-success hover:bg-success-light',
                                    'rejected' => 'border-danger  text-danger  hover:bg-danger-light',
                                    'actioned' => 'border-primary text-primary hover:bg-primary-light',
                                    default    => 'border-b-color text-muted   hover:bg-gray-50',
                                };
                            ?>
                                <label class="flex items-center gap-2 border rounded-lg px-3 py-2 cursor-pointer
                                              transition-colors text-sm <?= $c ?>">
                                    <input type="radio" name="action" value="<?= $act ?>"
                                        class="form-check-input" required>
                                    <?= ucfirst($act) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-dark" for="comment">
                            Comment / Feedback
                        </label>
                        <textarea id="comment" name="comment" rows="4"
                            placeholder="Leave a comment, feedback, or instructions…"
                            class="form-control w-full border border-b-color rounded-lg px-3 py-2.5
                                         text-sm focus:border-primary outline-none duration-300 resize-y"></textarea>
                        <p class="text-xs text-muted mt-1">
                            The report author will be notified of your action and comment.
                        </p>
                    </div>

                    <button type="submit"
                        class="w-full btn btn-primary py-2.5 text-sm font-semibold rounded-lg">
                        Submit Review
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Escalate panel (lead → manager) -->
        <?php if ($canEscalate): ?>
            <div class="bg-white dark:bg-dark-card border border-warning border-opacity-50 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-dark mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-arrow-up-right-dots text-warning"></i>
                    Escalate to Mission Manager
                </h3>
                <p class="text-xs text-muted mb-4">
                    Send this report upward to the mission manager for action or approval.
                    They will be notified and you will receive their response.
                </p>
                <form action="/reports/<?= $report['id'] ?>/escalate" method="POST" class="space-y-3">
                    <?= csrf_field() ?>
                    <textarea name="comment" rows="3"
                        placeholder="Optional note to the mission manager…"
                        class="form-control w-full border border-b-color rounded-lg px-3 py-2 text-sm
                                     focus:border-primary outline-none duration-300 resize-none"></textarea>
                    <button type="submit"
                        class="w-full border border-warning text-warning rounded-lg py-2 text-sm
                                   font-semibold hover:bg-warning-light transition-colors"
                        onclick="return confirm('Send this report to the mission manager?')">
                        Send to Manager
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>