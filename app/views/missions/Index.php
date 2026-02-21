<?php $layout = 'app'; ?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-semibold text-dark">Missions</h2>
    <?php if (can('create-missions') || has_any_role(['admin', 'manager'])): ?>
        <a href="/missions/create"
            class="btn btn-primary text-sm px-4 py-2">
            + New Mission
        </a>
    <?php endif; ?>
</div>

<?= partial('partials.flash') ?>

<?php if (empty($missions)): ?>
    <div class="text-center py-16 text-muted">
        <p class="text-lg mb-2">No missions found.</p>
        <p class="text-sm">Missions assigned to your team will appear here.</p>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-lg border border-b-color">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-dark-card text-xs uppercase tracking-wider text-muted">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Classification</th>
                    <th class="px-4 py-3">Progress</th>
                    <th class="px-4 py-3">Start</th>
                    <th class="px-4 py-3">End</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-b-color">
                <?php foreach ($missions as $mission): ?>
                    <?php
                    $total     = (int) ($mission['total_tasks'] ?? 0);
                    $completed = (int) ($mission['completed_tasks'] ?? 0);
                    $progress  = $total > 0 ? round(($completed / $total) * 100) : 0;

                    $badge = match ($mission['classification']) {
                        'SECRET'       => 'bg-danger-light text-danger',
                        'CONFIDENTIAL' => 'bg-warning-light text-warning',
                        default        => 'bg-primary-light text-primary',
                    };
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-card transition-colors">
                        <td class="px-4 py-3 font-mono text-xs">
                            <?= htmlspecialchars($mission['m_code']) ?>
                        </td>
                        <td class="px-4 py-3 font-medium">
                            <a href="/missions/<?= $mission['id'] ?>"
                                class="text-primary hover:underline">
                                <?= htmlspecialchars($mission['title']) ?>
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-1 rounded <?= $badge ?>">
                                <?= htmlspecialchars($mission['classification']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 min-w-[120px]">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-primary h-1.5 rounded-full"
                                        style="width: <?= $progress ?>%"></div>
                                </div>
                                <span class="text-xs text-muted"><?= $progress ?>%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted text-xs">
                            <?= $mission['start_time'] ? date('M d, Y', strtotime($mission['start_time'])) : '—' ?>
                        </td>
                        <td class="px-4 py-3 text-muted text-xs">
                            <?= $mission['end_time'] ? date('M d, Y', strtotime($mission['end_time'])) : '—' ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <a href="/missions/<?= $mission['id'] ?>"
                                    class="text-primary hover:underline text-xs">View</a>

                                <?php if (has_any_role(['admin', 'manager'])): ?>
                                    <a href="/missions/<?= $mission['id'] ?>/edit"
                                        class="text-muted hover:text-dark text-xs">Edit</a>
                                <?php endif; ?>

                                <?php if (has_role('admin')): ?>
                                    <form action="/missions/<?= $mission['id'] ?>/delete" method="POST"
                                        onsubmit="return confirm('Delete this mission?')">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                            class="text-danger hover:underline text-xs bg-transparent border-0 cursor-pointer p-0">
                                            Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>