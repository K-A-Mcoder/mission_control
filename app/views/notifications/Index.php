<?php $layout = 'app'; ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Notifications</h2>
        <p class="text-sm text-muted mt-0.5">Your activity feed</p>
    </div>
    <button id="markAllReadBtn"
        class="text-sm text-primary hover:underline flex items-center gap-1.5">
        <i class="fa-solid fa-check-double text-xs"></i>
        Mark all as read
    </button>
</div>

<?= partial('partials.flash') ?>

<!-- Filter tabs -->
<div class="flex gap-1 mb-5 border-b border-b-color">
    <?php
    $tabs = ['all' => 'All', 'unread' => 'Unread', 'report' => 'Reports', 'task' => 'Tasks', 'team' => 'Teams'];
    $activeTab = $_GET['filter'] ?? 'all';
    foreach ($tabs as $k => $label):
    ?>
        <a href="?filter=<?= $k ?>"
            class="px-4 py-2 text-sm font-medium border-b-2 transition-colors -mb-px
                  <?= $activeTab === $k
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted hover:text-dark' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Notification list -->
<div id="notifFullList" class="space-y-2">
    <?php if (empty($notifications)): ?>
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl px-6 py-16 text-center">
            <i class="fa-solid fa-bell-slash text-4xl text-muted mb-4"></i>
            <p class="text-base font-medium text-dark mb-1">All caught up!</p>
            <p class="text-sm text-muted">No notifications to show.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $n):
            $isUnread   = ! $n['is_read'];
            $iconMap = [
                'report_submitted' => ['icon' => 'fa-file-circle-plus',   'color' => 'text-primary  bg-primary-light'],
                'report_reviewed'  => ['icon' => 'fa-file-circle-check',  'color' => 'text-success  bg-success-light'],
                'report_actioned'  => ['icon' => 'fa-bolt',               'color' => 'text-warning  bg-warning-light'],
                'report_escalated' => ['icon' => 'fa-arrow-up-right-dots', 'color' => 'text-danger   bg-danger-light'],
                'task_assigned'    => ['icon' => 'fa-list-check',         'color' => 'text-primary  bg-primary-light'],
                'team_added'       => ['icon' => 'fa-users',              'color' => 'text-primary  bg-primary-light'],
            ];
            $meta = $iconMap[$n['type']] ?? ['icon' => 'fa-bell', 'color' => 'text-muted bg-gray-100'];
            $timeAgo = (function ($dt) {
                $diff = time() - strtotime($dt);
                if ($diff < 60)    return 'just now';
                if ($diff < 3600)  return floor($diff / 60) . 'm ago';
                if ($diff < 86400) return floor($diff / 3600) . 'h ago';
                return date('M d, Y', strtotime($dt));
            })($n['created_at']);
        ?>
            <div class="flex gap-4 bg-white dark:bg-dark-card border border-b-color rounded-xl p-4
                        transition-all duration-150 notif-item <?= $isUnread ? 'ring-1 ring-primary ring-opacity-30' : '' ?>"
                data-id="<?= $n['id'] ?>"
                data-read="<?= $n['is_read'] ?>"
                data-type="<?= htmlspecialchars($n['type'] ?? 'info') ?>">

                <!-- Icon -->
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 <?= $meta['color'] ?>">
                    <i class="fa-solid <?= $meta['icon'] ?> text-sm"></i>
                </div>

                <!-- Body -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-dark flex items-center gap-2">
                                <?= htmlspecialchars($n['title']) ?>
                                <?php if ($isUnread): ?>
                                    <span class="w-2 h-2 rounded-full bg-primary shrink-0 inline-block"></span>
                                <?php endif; ?>
                            </p>
                            <p class="text-sm text-body-color mt-0.5">
                                <?= htmlspecialchars($n['body']) ?>
                            </p>
                            <?php if ($n['sender_name']): ?>
                                <p class="text-xs text-muted mt-1">
                                    From: <?= htmlspecialchars($n['sender_name']) ?>
                                    &middot; <?= $timeAgo ?>
                                </p>
                            <?php else: ?>
                                <p class="text-xs text-muted mt-1"><?= $timeAgo ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 shrink-0">
                            <?php if ($n['url']): ?>
                                <a href="<?= htmlspecialchars($n['url']) ?>"
                                    class="text-xs text-primary hover:underline whitespace-nowrap">
                                    View →
                                </a>
                            <?php endif; ?>
                            <?php if ($isUnread): ?>
                                <button class="mark-read-btn text-xs text-muted hover:text-dark"
                                    data-id="<?= $n['id'] ?>"
                                    title="Mark as read">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    (function() {
        'use strict';

        const CSRF = '<?= csrf_token() ?>';

        // ── Filter tabs (client-side) ─────────────────────────────────────────────
        const activeTab = new URLSearchParams(window.location.search).get('filter') || 'all';

        document.querySelectorAll('.notif-item').forEach(item => {
            const type = item.dataset.type || '';
            const isRead = item.dataset.read == '1';

            let show = true;

            if (activeTab === 'unread') show = !isRead;
            else if (activeTab === 'report') show = type.startsWith('report');
            else if (activeTab === 'task') show = type.startsWith('task');
            else if (activeTab === 'team') show = type.startsWith('team');

            if (!show) item.style.display = 'none';
        });

        // ── Mark single read ──────────────────────────────────────────────────────
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                const item = document.querySelector(`.notif-item[data-id="${id}"]`);

                try {
                    const res = await fetch(`/api/notifications/${id}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            _token: CSRF
                        }),
                    });
                    const json = await res.json();

                    if (json.success && item) {
                        item.dataset.read = '1';
                        item.classList.remove('ring-1', 'ring-primary', 'ring-opacity-30');
                        this.remove();
                        // Remove the unread dot
                        const dot = item.querySelector('.rounded-full.bg-primary.inline-block');
                        if (dot) dot.remove();
                    }
                } catch {}
            });
        });

        // ── Mark all read ─────────────────────────────────────────────────────────
        document.getElementById('markAllReadBtn')?.addEventListener('click', async function() {
            try {
                const res = await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        _token: CSRF
                    }),
                });
                const json = await res.json();

                if (json.success) {
                    document.querySelectorAll('.notif-item').forEach(item => {
                        item.dataset.read = '1';
                        item.classList.remove('ring-1', 'ring-primary', 'ring-opacity-30');
                        item.querySelector('.mark-read-btn')?.remove();
                        const dot = item.querySelector('.rounded-full.bg-primary.inline-block');
                        if (dot) dot.remove();
                    });
                    this.classList.add('opacity-50', 'pointer-events-none');
                }
            } catch {}
        });

    })();
</script>