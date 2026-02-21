<?php $layout = 'app'; ?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-semibold text-dark">Teams</h2>
    <?php if (has_any_role(['admin', 'manager'])): ?>
        <a href="/teams/create" class="btn btn-primary text-sm px-4 py-2">+ New Team</a>
    <?php endif; ?>
</div>

<?= partial('partials.flash') ?>

<?php if (empty($teams)): ?>
    <div class="text-center py-16 text-muted">
        <p class="text-lg mb-1">No teams found.</p>
        <p class="text-sm">Teams you belong to will appear here.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php foreach ($teams as $team):
            $statusClass = match ($team['status']) {
                'inactive' => 'text-warning bg-warning-light',
                'archived' => 'text-muted bg-gray-100',
                default    => 'text-success bg-success-light',
            };
        ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-lg p-5
                        hover:shadow-md transition-shadow duration-200 flex flex-col gap-3">

                <!-- Header -->
                <div class="flex items-start justify-between">
                    <div>
                        <a href="/teams/<?= $team['id'] ?>"
                            class="text-base font-semibold text-dark hover:text-primary transition-colors">
                            <?= htmlspecialchars($team['name']) ?>
                        </a>
                        <span class="ml-2 text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full <?= $statusClass ?>">
                            <?= $team['status'] ?>
                        </span>
                    </div>
                </div>

                <!-- Lead -->
                <?php if ($team['lead_name']): ?>
                    <p class="text-xs text-muted flex items-center gap-1.5">
                        <i class="fa-solid fa-user-tie"></i>
                        <?= htmlspecialchars($team['lead_name']) ?>
                    </p>
                <?php endif; ?>

                <!-- Member count -->
                <p class="text-xs text-muted flex items-center gap-1.5">
                    <i class="fa-solid fa-users"></i>
                    <?= (int) $team['member_count'] ?> member<?= $team['member_count'] != 1 ? 's' : '' ?>
                </p>

                <!-- Footer actions -->
                <div class="flex items-center justify-between pt-2 border-t border-b-color mt-auto">
                    <a href="/teams/<?= $team['id'] ?>"
                        class="text-xs text-primary hover:underline">View team →</a>

                    <?php if (has_any_role(['admin', 'manager'])): ?>
                        <div class="flex gap-3">
                            <a href="/teams/<?= $team['id'] ?>/edit"
                                class="text-xs text-muted hover:text-dark">Edit</a>

                            <?php if (has_role('admin')): ?>
                                <form action="/teams/<?= $team['id'] ?>/delete" method="POST"
                                    onsubmit="return confirm('Delete team \'<?= htmlspecialchars(addslashes($team['name'])) ?>\'?')">
                                    <?= csrf_field() ?>
                                    <button type="submit"
                                        class="text-xs text-danger hover:underline bg-transparent border-0 cursor-pointer p-0">
                                        Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>