<?php $layout = 'app';

// SortableJS is loaded via CDN — no composer install needed for pure-JS drag-and-drop.
// For a PHP-rendered kanban with server push, install via npm (npm i sortablejs) or CDN.

$userId = (int) ($_SESSION['user_id'] ?? 0);
$role   = $_SESSION['role'] ?? '';
?>

<!-- SortableJS (drag-and-drop) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Kanban Board</h2>
        <p class="text-sm text-muted mt-0.5">
            <a href="/dashboard/tasks" class="hover:text-primary">← Tasks</a>
        </p>
    </div>
    <div class="flex items-center gap-3">
        <!-- Filter by team (optional) -->
        <?php if (! empty($teams)): ?>
            <select id="kanbanTeamFilter"
                class="form-control h-9 border border-b-color rounded-md px-3 text-sm
                           focus:border-primary outline-none duration-300">
                <option value="">All teams</option>
                <?php foreach ($teams as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <button id="refreshKanban"
            class="btn btn-secondary text-sm px-3 py-2 rounded flex items-center gap-1.5">
            <i class="fa-solid fa-rotate-right text-xs"></i> Refresh
        </button>
        <?php if (has_any_role(['admin', 'manager', 'super_admin'])): ?>
            <a href="/teams" class="btn btn-primary text-sm px-3 py-2">+ New Task</a>
        <?php endif; ?>
    </div>
</div>

<?= partial('partials.flash') ?>

<!-- Status legend -->
<div class="flex items-center gap-4 mb-4 text-xs text-muted">
    <span class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-gray-400"></span> Open
    </span>
    <span class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-blue-500"></span> In Progress
    </span>
    <span class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-yellow-500"></span> Review
    </span>
    <span class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-green-500"></span> Closed
    </span>
    <span class="ml-auto text-xs" id="saveIndicator"></span>
</div>

<!-- ── Board columns ─────────────────────────────────────────────────────── -->
<div id="kanbanBoard" class="grid grid-cols-4 gap-4 min-h-[60vh]">

    <?php
    $columnConfig = [
        'open'        => ['label' => 'Open',        'dot' => 'bg-gray-400',   'header' => 'bg-gray-100 dark:bg-dark'],
        'in_progress' => ['label' => 'In Progress',  'dot' => 'bg-blue-500',   'header' => 'bg-blue-50 dark:bg-dark'],
        'review'      => ['label' => 'Review',       'dot' => 'bg-yellow-500', 'header' => 'bg-yellow-50 dark:bg-dark'],
        'closed'      => ['label' => 'Closed',       'dot' => 'bg-green-500',  'header' => 'bg-green-50 dark:bg-dark'],
    ];

    foreach ($columnConfig as $status => $col): ?>
        <div class="kanban-column flex flex-col" data-status="<?= $status ?>">

            <!-- Column header -->
            <div class="flex items-center justify-between px-3 py-2 rounded-t-lg <?= $col['header'] ?> border border-b-color border-b-0">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full <?= $col['dot'] ?>"></span>
                    <span class="text-sm font-semibold text-dark"><?= $col['label'] ?></span>
                </div>
                <span class="text-xs text-muted font-medium column-count" data-status="<?= $status ?>">0</span>
            </div>

            <!-- Drop zone -->
            <div class="task-drop-zone flex-1 border border-b-color rounded-b-lg p-2 space-y-2
                        bg-white dark:bg-dark-card min-h-[200px] transition-colors duration-150"
                data-status="<?= $status ?>"
                id="col-<?= $status ?>">
                <!-- Cards inserted here -->
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Card template (hidden, cloned by JS) -->
<template id="taskCardTemplate">
    <div class="task-card bg-white dark:bg-dark border border-b-color rounded-lg p-3 cursor-grab
                active:cursor-grabbing hover:shadow-md transition-shadow duration-150 group"
        data-task-id=""
        data-priority="">
        <div class="flex items-start justify-between gap-2 mb-2">
            <a class="task-title text-sm font-medium text-dark hover:text-primary leading-snug
                      group-[.is-closed]:line-through group-[.is-closed]:text-muted" href="#"></a>
            <span class="task-priority text-[10px] font-bold uppercase px-1.5 py-0.5 rounded shrink-0"></span>
        </div>
        <div class="flex items-center justify-between text-xs text-muted">
            <span class="task-team truncate max-w-[100px]"></span>
            <span class="task-due flex items-center gap-1"></span>
        </div>
        <p class="task-assignee text-xs text-muted mt-1"></p>
    </div>
</template>

<script>
    (function() {
        'use strict';

        const CSRF_TOKEN = '<?= csrf_token() ?>';
        const IS_MANAGER = <?= json_encode(has_any_role(['admin', 'manager', 'super_admin'])) ?>;
        const indicator = document.getElementById('saveIndicator');

        let allTasks = [];
        let sortables = [];

        // ── Load tasks from API ───────────────────────────────────────────────────
        async function loadKanban(teamId = '') {
            const url = '/api/tasks/kanban' + (teamId ? `?team_id=${teamId}` : '');

            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();

                if (!json.success) return;

                allTasks = [];
                Object.values(json.columns).forEach(col => allTasks.push(...col));

                renderColumns(json.columns);
            } catch (e) {
                console.error('Kanban load error', e);
            }
        }

        // ── Render columns ────────────────────────────────────────────────────────
        function renderColumns(columns) {
            const template = document.getElementById('taskCardTemplate');

            Object.entries(columns).forEach(([status, tasks]) => {
                const zone = document.getElementById(`col-${status}`);
                const countEl = document.querySelector(`.column-count[data-status="${status}"]`);

                zone.innerHTML = '';
                if (countEl) countEl.textContent = tasks.length;

                tasks.forEach(task => {
                    const card = template.content.cloneNode(true).querySelector('.task-card');
                    card.dataset.taskId = task.id;
                    card.dataset.priority = task.priority;

                    if (status === 'closed') card.classList.add('is-closed');

                    // Title
                    const titleEl = card.querySelector('.task-title');
                    titleEl.textContent = task.title;
                    titleEl.href = `/tasks/${task.id}`;

                    // Priority badge
                    const pEl = card.querySelector('.task-priority');
                    pEl.textContent = task.priority;
                    pEl.className += ' ' + priorityClass(task.priority);

                    // Team
                    card.querySelector('.task-team').textContent = task.team_name ?? '';

                    // Due date
                    const dueEl = card.querySelector('.task-due');
                    if (task.due_date) {
                        const d = new Date(task.due_date);
                        const overdue = status !== 'closed' && d < new Date();
                        dueEl.textContent = d.toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric'
                        });
                        if (overdue) dueEl.classList.add('text-danger', 'font-semibold');
                    }

                    // Assignee
                    card.querySelector('.task-assignee').textContent =
                        task.assignee_name ? `→ ${task.assignee_name}` : '';

                    // Disable drag for non-managers
                    if (!IS_MANAGER) card.style.cursor = 'default';

                    zone.appendChild(card);
                });
            });

            initSortable();
        }

        // ── Sortable drag-and-drop ────────────────────────────────────────────────
        function initSortable() {
            sortables.forEach(s => s.destroy());
            sortables = [];

            document.querySelectorAll('.task-drop-zone').forEach(zone => {
                sortables.push(Sortable.create(zone, {
                    group: 'kanban',
                    animation: 150,
                    disabled: !IS_MANAGER,
                    ghostClass: 'opacity-40',
                    dragClass: 'shadow-xl',

                    onEnd: async function(evt) {
                        const newStatus = evt.to.dataset.status;
                        const taskId = parseInt(evt.item.dataset.taskId);

                        if (!taskId || !newStatus) return;

                        // Optimistic UI — update count badges immediately
                        updateColumnCounts();

                        // Toggle closed styling
                        evt.item.classList.toggle('is-closed', newStatus === 'closed');

                        // Persist via API
                        setIndicator('Saving…', 'text-muted');

                        try {
                            const res = await fetch(`/api/tasks/${taskId}/status`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    status: newStatus,
                                    _token: CSRF_TOKEN
                                }),
                            });
                            const json = await res.json();

                            if (json.success) {
                                setIndicator('Saved ✓', 'text-success');
                                setTimeout(() => setIndicator(''), 2000);
                            } else {
                                // Revert card back to original column
                                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                                updateColumnCounts();
                                setIndicator('Error — reverted', 'text-danger');
                                setTimeout(() => setIndicator(''), 3000);
                            }
                        } catch {
                            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                            updateColumnCounts();
                            setIndicator('Network error', 'text-danger');
                        }
                    },
                }));
            });
        }

        function updateColumnCounts() {
            document.querySelectorAll('.task-drop-zone').forEach(zone => {
                const count = zone.querySelectorAll('.task-card').length;
                const status = zone.dataset.status;
                const badge = document.querySelector(`.column-count[data-status="${status}"]`);
                if (badge) badge.textContent = count;
            });
        }

        function setIndicator(msg, cls = '') {
            indicator.textContent = msg;
            indicator.className = `text-xs font-medium ${cls}`;
        }

        function priorityClass(priority) {
            const map = {
                critical: 'bg-danger-light text-danger',
                high: 'bg-warning-light text-warning',
                medium: 'bg-primary-light text-primary',
            };
            return map[priority] ?? 'bg-gray-100 text-muted';
        }

        // ── Team filter ───────────────────────────────────────────────────────────
        document.getElementById('kanbanTeamFilter')?.addEventListener('change', function() {
            loadKanban(this.value);
        });

        // ── Refresh ───────────────────────────────────────────────────────────────
        document.getElementById('refreshKanban')?.addEventListener('click', function() {
            const teamId = document.getElementById('kanbanTeamFilter')?.value ?? '';
            loadKanban(teamId);
        });

        // ── Initial load ──────────────────────────────────────────────────────────
        loadKanban();

    })();
</script>