<?php $layout = 'app'; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.tailwindcss.min.css">
<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">Tasks Overview</h2>
        <p class="text-sm text-muted mt-0.5">
            <a href="/dashboard" class="hover:text-primary">← Dashboard</a>
        </p>
    </div>
    <?php if (has_any_role(['admin', 'manager', 'super_admin'])): ?>
        <a href="/teams" class="btn btn-primary text-sm px-4 py-2">+ New Task</a>
    <?php endif; ?>
</div>

<?= partial('partials.flash') ?>

<!-- ── Stat cards ────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-6">
    <?php
    $pills = [
        ['label' => 'Total',       'key' => 'total',       'class' => 'text-dark bg-gray-100'],
        ['label' => 'Open',        'key' => 'open',        'class' => 'text-muted bg-gray-100'],
        ['label' => 'In Progress', 'key' => 'in_progress', 'class' => 'text-primary bg-primary-light'],
        ['label' => 'Review',      'key' => 'review',      'class' => 'text-warning bg-warning-light'],
        ['label' => 'Closed',      'key' => 'closed',      'class' => 'text-success bg-success-light'],
        ['label' => 'Overdue',     'key' => 'overdue',     'class' => 'text-danger bg-danger-light'],
    ];
    foreach ($pills as $p): ?>
        <div class="rounded-lg p-3 text-center <?= $p['class'] ?>">
            <p class="text-xl font-bold"><?= (int)($taskStats[$p['key']] ?? 0) ?></p>
            <p class="text-[10px] uppercase tracking-wide"><?= $p['label'] ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- ── Filters ────────────────────────────────────────────────────────────── -->
<div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-4 mb-4" id="filterPanel">
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <input type="text" id="filterSearch" placeholder="Search tasks..."
            class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                      focus:border-primary outline-none duration-300 col-span-2">

        <select id="filterStatus"
            class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                       focus:border-primary outline-none duration-300">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>"><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="filterPriority"
            class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                       focus:border-primary outline-none duration-300">
            <option value="">All priorities</option>
            <?php foreach ($priorities as $p): ?>
                <option value="<?= $p ?>"><?= ucfirst($p) ?></option>
            <?php endforeach; ?>
        </select>

        <?php if (! empty($teams)): ?>
            <select id="filterTeam"
                class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                           focus:border-primary outline-none duration-300">
                <option value="">All teams</option>
                <?php foreach ($teams as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if (! empty($assignees)): ?>
            <select id="filterAssignee"
                class="form-control h-10 border border-b-color rounded-md px-3 text-sm
                           focus:border-primary outline-none duration-300">
                <option value="">All assignees</option>
                <?php foreach ($assignees as $u): ?>
                    <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>

    <!-- Bulk actions (hidden until rows selected) -->
    <?php if (has_any_role(['admin', 'manager', 'super_admin'])): ?>
        <div id="bulkActions" class="hidden mt-3 flex items-center gap-3 pt-3 border-t border-b-color">
            <span class="text-sm text-dark font-medium">
                <span id="selectedCount">0</span> selected
            </span>
            <select id="bulkStatus"
                class="form-control h-9 border border-b-color rounded-md px-3 text-sm
                           focus:border-primary outline-none duration-300">
                <option value="">Set status…</option>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>"><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                <?php endforeach; ?>
            </select>
            <button id="applyBulkStatus"
                class="btn btn-primary text-sm px-4 py-2 rounded">Apply</button>
            <?php if (has_any_role(['admin', 'super_admin'])): ?>
                <button id="applyBulkDelete"
                    class="btn btn-danger text-sm px-4 py-2 rounded">Delete selected</button>
            <?php endif; ?>
            <button id="clearSelection"
                class="text-sm text-muted hover:text-dark">Clear</button>
        </div>
    <?php endif; ?>
</div>

<!-- ── Task table (DataTables) ───────────────────────────────────────────── -->
<div class="bg-white dark:bg-dark-card border border-b-color rounded-lg overflow-hidden">
    <table id="tasksTable" class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-dark text-xs uppercase text-muted">
            <tr>
                <?php if (has_any_role(['admin', 'manager', 'super_admin'])): ?>
                    <th class="px-4 py-3 w-8">
                        <input type="checkbox" id="selectAll" class="form-check-input">
                    </th>
                <?php endif; ?>
                <th class="px-4 py-3 text-left">Task</th>
                <th class="px-4 py-3 text-left">Team</th>
                <th class="px-4 py-3 text-left">Assignee</th>
                <th class="px-4 py-3 text-left">Priority</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Due</th>
                <th class="px-4 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-b-color" id="tasksBody">
            <?php foreach ($tasks as $task):
                $pc = match ($task['priority']) {
                    'critical' => 'bg-danger-light text-danger',
                    'high'     => 'bg-warning-light text-warning',
                    'medium'   => 'bg-primary-light text-primary',
                    default    => 'bg-gray-100 text-muted',
                };
                $sc = match ($task['status']) {
                    'closed'      => 'bg-success-light text-success',
                    'in_progress' => 'bg-primary-light text-primary',
                    'review'      => 'bg-warning-light text-warning',
                    default       => 'bg-gray-100 text-muted',
                };
                $isOverdue = $task['status'] !== 'closed'
                    && ! empty($task['due_date'])
                    && strtotime($task['due_date']) < time();
            ?>
                <tr data-task-id="<?= $task['id'] ?>"
                    data-status="<?= htmlspecialchars($task['status']) ?>"
                    data-priority="<?= htmlspecialchars($task['priority']) ?>"
                    data-team="<?= (int)($task['team_id'] ?? 0) ?>"
                    data-assignee="<?= (int)($task['assigned_to'] ?? 0) ?>">
                    <?php if (has_any_role(['admin', 'manager', 'super_admin'])): ?>
                        <td class="px-4 py-3">
                            <input type="checkbox" class="task-checkbox form-check-input"
                                value="<?= $task['id'] ?>">
                        </td>
                    <?php endif; ?>
                    <td class="px-4 py-3">
                        <a href="/tasks/<?= $task['id'] ?>"
                            class="font-medium text-dark hover:text-primary <?= $task['status'] === 'closed' ? 'line-through text-muted' : '' ?>">
                            <?= htmlspecialchars($task['title']) ?>
                        </a>
                        <?php if ($task['mission_title']): ?>
                            <p class="text-xs text-muted"><?= htmlspecialchars($task['mission_title']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-muted text-xs">
                        <?= htmlspecialchars($task['team_name'] ?? '—') ?>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <?= htmlspecialchars($task['assignee_name'] ?? '—') ?>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $pc ?>">
                            <?= $task['priority'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $sc ?>">
                            <?= str_replace('_', ' ', $task['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs <?= $isOverdue ? 'text-danger font-semibold' : 'text-muted' ?>">
                        <?= $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : '—' ?>
                        <?= $isOverdue ? ' ⚠' : '' ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="/tasks/<?= $task['id'] ?>" class="text-xs text-primary hover:underline">View</a>
                            <?php if (has_any_role(['admin', 'manager', 'super_admin'])): ?>
                                <a href="/tasks/<?= $task['id'] ?>/edit" class="text-xs text-muted hover:text-dark">Edit</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- DataTables JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>

<script>
    (function() {
        'use strict';

        // ── DataTable init ────────────────────────────────────────────────────────
        const hasCheckbox = document.querySelector('.task-checkbox') !== null;
        const colOffset = hasCheckbox ? 1 : 0;

        const table = $('#tasksTable').DataTable({
            pageLength: 25,
            order: [
                [3 + colOffset, 'asc']
            ], // priority col
            columnDefs: hasCheckbox ? [{
                orderable: false,
                targets: 0
            }] : [],
            language: {
                search: '',
                searchPlaceholder: 'Search table…',
                lengthMenu: 'Show _MENU_ tasks',
                info: '_START_–_END_ of _TOTAL_ tasks',
            },
            dom: '<"flex items-center justify-between px-4 py-3 border-b border-b-color"lf>rtip',
        });

        // ── External filter inputs ────────────────────────────────────────────────
        const applyFilters = () => {
            const search = document.getElementById('filterSearch')?.value ?? '';
            const status = document.getElementById('filterStatus')?.value ?? '';
            const priority = document.getElementById('filterPriority')?.value ?? '';
            const teamId = document.getElementById('filterTeam')?.value ?? '';
            const assignee = document.getElementById('filterAssignee')?.value ?? '';

            // Custom filter on the table rows
            $.fn.dataTable.ext.search = [];

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const row = table.row(dataIndex).node();
                const $row = $(row);
                const rs = $row.data('status') || '';
                const rp = $row.data('priority') || '';
                const rt = String($row.data('team') || '');
                const ra = String($row.data('assignee') || '');

                if (status && rs !== status) return false;
                if (priority && rp !== priority) return false;
                if (teamId && rt !== teamId) return false;
                if (assignee && ra !== assignee) return false;

                return true;
            });

            table.search(search).draw();
        };

        ['filterSearch', 'filterStatus', 'filterPriority', 'filterTeam', 'filterAssignee'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener(el.tagName === 'SELECT' ? 'change' : 'input', applyFilters);
        });

        // ── Select all / row checkboxes ───────────────────────────────────────────
        const bulkPanel = document.getElementById('bulkActions');
        const countEl = document.getElementById('selectedCount');

        function updateBulkPanel() {
            const checked = document.querySelectorAll('.task-checkbox:checked').length;
            if (countEl) countEl.textContent = checked;
            if (bulkPanel) bulkPanel.classList.toggle('hidden', checked === 0);
        }

        document.getElementById('selectAll')?.addEventListener('change', function() {
            document.querySelectorAll('.task-checkbox').forEach(cb => cb.checked = this.checked);
            updateBulkPanel();
        });

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('task-checkbox')) updateBulkPanel();
        });

        document.getElementById('clearSelection')?.addEventListener('click', function() {
            document.querySelectorAll('.task-checkbox:checked').forEach(cb => cb.checked = false);
            const sa = document.getElementById('selectAll');
            if (sa) sa.checked = false;
            updateBulkPanel();
        });

        // ── Bulk status update ────────────────────────────────────────────────────
        document.getElementById('applyBulkStatus')?.addEventListener('click', async function() {
            const status = document.getElementById('bulkStatus')?.value;
            const ids = [...document.querySelectorAll('.task-checkbox:checked')].map(cb => parseInt(cb.value));

            if (!status) return alert('Please choose a status first.');
            if (ids.length === 0) return;

            if (!confirm(`Set ${ids.length} task(s) to "${status}"?`)) return;

            const res = await fetch('/api/tasks/bulk-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ids,
                    status,
                    _token: '<?= csrf_token() ?>'
                }),
            });
            const json = await res.json();

            if (json.success) {
                // Update rows in-place instead of full reload
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-task-id="${id}"]`);
                    if (!row) return;
                    row.dataset.status = status;
                    const chip = row.querySelector('td:nth-child(' + (5 + colOffset) + ') span');
                    if (chip) {
                        chip.textContent = status.replace('_', ' ');
                        chip.className = 'text-[10px] font-bold uppercase px-2 py-0.5 rounded ' + statusClass(status);
                    }
                });
                alert(json.message);
                updateBulkPanel();
            } else {
                alert(json.error ?? 'Something went wrong.');
            }
        });

        // ── Bulk delete ───────────────────────────────────────────────────────────
        document.getElementById('applyBulkDelete')?.addEventListener('click', async function() {
            const ids = [...document.querySelectorAll('.task-checkbox:checked')].map(cb => parseInt(cb.value));
            if (ids.length === 0) return;
            if (!confirm(`Permanently delete ${ids.length} task(s)?`)) return;

            const res = await fetch('/api/tasks/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ids,
                    _token: '<?= csrf_token() ?>'
                }),
            });
            const json = await res.json();

            if (json.success) {
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-task-id="${id}"]`);
                    if (row) table.row(row).remove().draw();
                });
                updateBulkPanel();
            } else {
                alert(json.error ?? 'Something went wrong.');
            }
        });

        function statusClass(status) {
            const map = {
                closed: 'bg-success-light text-success',
                in_progress: 'bg-primary-light text-primary',
                review: 'bg-warning-light text-warning',
            };
            return map[status] ?? 'bg-gray-100 text-muted';
        }

    })();
</script>